<?php
/**
 * GNN GitHub Plugin Updater for GNN Logos
 *
 * Checks GitHub Releases API for new versions and integrates
 * natively with the WordPress core plugin update system.
 *
 * @package GNN_Logos
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class GNN_Logos_Updater
 */
class GNN_Logos_Updater
{
    /**
     * GitHub repository owner/name.
     *
     * @var string
     */
    private $repo = 'BigDesigner/gnn-logos';

    /**
     * Plugin slug (directory/main file).
     *
     * @var string
     */
    private $plugin_slug = 'gnn-logos/gnn-logos.php';

    /**
     * Transient key for caching GitHub API release response.
     *
     * @var string
     */
    private $transient_key = 'gnn_logos_github_update_check';

    /**
     * Cache duration (12 hours).
     *
     * @var int
     */
    private $cache_duration = 43200;

    /**
     * Initialize updater hooks.
     */
    public function __construct()
    {
        // Pipeline update filters
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);

        // Admin manual check and cache-clearing hooks
        add_action('admin_init', array($this, 'handle_manual_check'));
        add_action('load-update-core.php', array($this, 'clear_cache'));
    }

    /**
     * Get local installed version from plugin headers.
     *
     * @return string
     */
    private function get_local_version()
    {
        if (defined('GNN_LOGOS_VERSION')) {
            return GNN_LOGOS_VERSION;
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_slug);
        return isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
    }

    /**
     * Fetch latest release data from GitHub API.
     *
     * @return object|false
     */
    private function get_remote_release()
    {
        $cached = get_transient($this->transient_key);
        if (false !== $cached) {
            return $cached;
        }

        $url = sprintf('https://api.github.com/repos/%s/releases/latest', $this->repo);
        $args = array(
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
            ),
            'timeout' => 10,
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            set_transient($this->transient_key, false, 300); // Cache failure for 5 mins
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        if (empty($body) || !isset($body->tag_name)) {
            return false;
        }

        $remote_version = ltrim($body->tag_name, 'v');

        $download_url = '';

        if (!empty($body->assets) && is_array($body->assets)) {
            foreach ($body->assets as $asset) {
                if (isset($asset->name) && strpos($asset->name, '.zip') !== false && !empty($asset->browser_download_url)) {
                    $download_url = $asset->browser_download_url;
                    break;
                }
            }
        }

        if (empty($download_url) && !empty($body->zipball_url)) {
            $download_url = $body->zipball_url;
        }

        // Validate version string (semver: X.Y.Z)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $remote_version)) {
            return false;
        }

        // SSRF defense: Validate download URL host is strictly GitHub
        $parsed = wp_parse_url($download_url);
        if (empty($parsed['host']) || !in_array($parsed['host'], array('github.com', 'codeload.github.com', 'objects.githubusercontent.com'), true)) {
            return false;
        }
        $download_url = esc_url_raw($download_url);

        $release_data = (object) array(
            'version'      => $remote_version,
            'download_url' => $download_url,
            'changelog'    => isset($body->body) ? $body->body : '',
            'published_at' => isset($body->published_at) ? $body->published_at : '',
            'html_url'     => isset($body->html_url) ? $body->html_url : '',
        );

        set_transient($this->transient_key, $release_data, $this->cache_duration);

        return $release_data;
    }

    /**
     * Filter into core update plugin transient.
     *
     * @param object $transient Update plugins transient.
     * @return object
     */
    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_remote_release();
        if (false === $release) {
            return $transient;
        }

        $local_version = $this->get_local_version();

        if (version_compare($release->version, $local_version, '>')) {
            $obj = new stdClass();
            $obj->slug        = 'gnn-logos';
            $obj->plugin      = $this->plugin_slug;
            $obj->new_version = $release->version;
            $obj->url         = $release->html_url;
            $obj->package     = $release->download_url;

            $transient->response[$this->plugin_slug] = $obj;
        }

        return $transient;
    }

    /**
     * Provide plugin information for the update details modal.
     *
     * @param false|object $result
     * @param string       $action
     * @param object       $args
     * @return object
     */
    public function plugin_info($result, $action, $args)
    {
        if ('plugin_information' !== $action || 'gnn-logos' !== $args->slug) {
            return $result;
        }

        $release = $this->get_remote_release();
        if (false === $release) {
            return $result;
        }

        return (object) array(
            'name'          => 'GNN Logos',
            'slug'          => 'gnn-logos',
            'version'       => $release->version,
            'author'        => 'BigDesigner',
            'homepage'      => 'https://github.com/BigDesigner/gnn-logos',
            'download_link' => $release->download_url,
            'sections'      => array(
                'description' => 'WordPress için ultra hafif logo ve sertifika vitrini. CSS Scroll-Snap karusel, GPU destekli sonsuz marquee ve estetik sertifika kartları (TS EN 12201-2 vb.) sunar.',
                'changelog'   => nl2br(esc_html($release->changelog)),
            ),
        );
    }

    /**
     * Ensure plugin directory name remains normalized to 'gnn-logos' after upgrade.
     *
     * @param bool  $response
     * @param array $hook_extra
     * @param array $result
     * @return array
     */
    public function after_install($response, $hook_extra, $result)
    {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_slug) {
            return $result;
        }

        global $wp_filesystem;
        $plugin_dir = WP_PLUGIN_DIR . '/gnn-logos';

        if ($result['destination'] !== $plugin_dir) {
            $wp_filesystem->move($result['destination'], $plugin_dir);
            $result['destination'] = $plugin_dir;
        }

        delete_transient($this->transient_key);
        return $result;
    }

    /**
     * Handle manual update check request from admin action links.
     */
    public function handle_manual_check()
    {
        if (isset($_GET['gnn_logos_check_update']) && '1' === $_GET['gnn_logos_check_update']) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'gnn_logos_manual_update')) {
                wp_die(esc_html__('Güvenlik doğrulaması başarısız oldu.', 'gnn-logos'));
            }

            if (current_user_can('update_plugins')) {
                delete_transient($this->transient_key);
                delete_site_transient('update_plugins');

                wp_safe_redirect(admin_url('update-core.php?force-check=1'));
                exit;
            }
        }
    }

    /**
     * Clear update cache when visiting core update page.
     */
    public function clear_cache()
    {
        delete_transient($this->transient_key);
    }
}

// Initialize updater.
new GNN_Logos_Updater();
