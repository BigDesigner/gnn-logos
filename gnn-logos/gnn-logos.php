<?php
/**
 * Plugin Name: GNN Logos
 * Plugin URI:  https://github.com/BigDesigner/gnn-logos
 * Description: WordPress için ultra hafif logo ve sertifika vitrini. CSS Scroll-Snap karusel, GPU destekli sonsuz marquee ve estetik sertifika kartları (TS EN 12201-2 vb.) sunar.
 * Version:     1.1.1
 * Author:      BigDesigner
 * Author URI:  https://github.com/BigDesigner
 * Text Domain: gnn-logos
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

// Define plugin constants.
define('GNN_LOGOS_VERSION', '1.1.1');
define('GNN_LOGOS_FILE', __FILE__);
define('GNN_LOGOS_DIR', plugin_dir_path(__FILE__));
define('GNN_LOGOS_URL', plugin_dir_url(__FILE__));

// Include GitHub updater.
if (file_exists(GNN_LOGOS_DIR . 'inc/updater.php')) {
    require_once GNN_LOGOS_DIR . 'inc/updater.php';
}

// Include core classes.
require_once GNN_LOGOS_DIR . 'includes/class-gnn-logos-cpt.php';
require_once GNN_LOGOS_DIR . 'includes/class-gnn-logos-shortcode.php';
require_once GNN_LOGOS_DIR . 'includes/class-gnn-logos-admin.php';

/**
 * Main GNN Logos Plugin Class.
 */
final class GNN_Logos
{
    /**
     * Singleton instance.
     *
     * @var GNN_Logos|null
     */
    private static $instance = null;

    /**
     * Custom Post Type Handler.
     *
     * @var GNN_Logos_CPT
     */
    public $cpt;

    /**
     * Shortcode Handler.
     *
     * @var GNN_Logos_Shortcode
     */
    public $shortcode;

    /**
     * Admin Handler.
     *
     * @var GNN_Logos_Admin
     */
    public $admin;

    /**
     * Main plugin instance getter.
     *
     * @return GNN_Logos
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Initialize plugin components.
     */
    private function init_components()
    {
        $this->cpt       = new GNN_Logos_CPT();
        $this->shortcode = new GNN_Logos_Shortcode();
        $this->admin     = new GNN_Logos_Admin();
    }

    /**
     * Register core WordPress hooks.
     */
    private function init_hooks()
    {
        add_action('init', array($this, 'load_textdomain'));
        add_filter('plugin_action_links_' . plugin_basename(GNN_LOGOS_FILE), array($this, 'plugin_action_links'));
    }

    /**
     * Load localization textdomain.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('gnn-logos', false, dirname(plugin_basename(GNN_LOGOS_FILE)) . '/languages');
    }

    /**
     * Add action links to the plugin list screen.
     *
     * @param array $links Existing plugin links.
     * @return array
     */
    public function plugin_action_links($links)
    {
        $donate_link    = '<a href="' . esc_url('https://buymeacoffee.com/bigdesigner') . '" target="_blank" rel="noopener noreferrer" style="font-weight:bold; color:#d63638;">' . esc_html__('Donate', 'gnn-logos') . '</a>';
        $generator_link = '<a href="' . esc_url(admin_url('admin.php?page=gnn-logos-generator')) . '">' . esc_html__('Shortcode Oluşturucu', 'gnn-logos') . '</a>';
        $update_url     = wp_nonce_url(admin_url('plugins.php?gnn_logos_check_update=1'), 'gnn_logos_manual_update');
        $update_link    = '<a href="' . esc_url($update_url) . '">' . esc_html__('Check Updates', 'gnn-logos') . '</a>';

        array_unshift($links, $donate_link, $generator_link, $update_link);
        return $links;
    }

    /**
     * Plugin activation hook callback.
     */
    public static function activate()
    {
        GNN_Logos_CPT::register_post_type_and_taxonomies();
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook callback.
     */
    public static function deactivate()
    {
        flush_rewrite_rules();
    }
}

// Register activation and deactivation hooks.
register_activation_hook(GNN_LOGOS_FILE, array('GNN_Logos', 'activate'));
register_deactivation_hook(GNN_LOGOS_FILE, array('GNN_Logos', 'deactivate'));

// Boot the plugin after other plugins have loaded.
add_action('plugins_loaded', array('GNN_Logos', 'instance'));
