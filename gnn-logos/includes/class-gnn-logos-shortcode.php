<?php
/**
 * Shortcode Engine and HTML Renderer for GNN Logos.
 *
 * Shared rendering engine consumed by [gnn_logos] shortcode,
 * Admin Live Preview AJAX, and Native Gutenberg Block.
 *
 * @package GNN_Logos
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class GNN_Logos_Shortcode
 */
class GNN_Logos_Shortcode
{
    /**
     * Shortcode tag.
     */
    const TAG = 'gnn_logos';

    /**
     * Default shortcode / block attributes.
     *
     * @return array
     */
    public static function get_defaults()
    {
        return array(
            'group'          => '',
            'layout'         => 'carousel', // 'grid', 'carousel', 'marquee'
            'style'          => 'minimal',  // 'minimal', 'card', 'bordered', 'glass'
            'theme'          => 'light',    // 'light', 'dark', 'auto'
            'logo_filter'    => 'none',     // 'none', 'grayscale', 'monochrome-white', 'monochrome-dark'
            'columns'        => 4,
            'columns_tablet' => 3,
            'columns_mobile' => 2,
            'aspect_ratio'   => 'auto',     // 'auto', '16/9', '4/3', '1/1', '3/2', '2/1'
            'logo_height'    => '',         // e.g. '60px', '80px'
            'item_width'     => '',         // Marquee item width e.g. '180px'
            'gap'            => '20px',
            'autoplay'       => 'true',
            'autoplay_speed' => 3000,
            'speed'          => '25s',
            'direction'      => 'left',     // 'left', 'right'
            'fade_edges'     => 'true',     // Marquee edge mask
            'grayscale'      => 'false',    // Legacy alias for logo_filter="grayscale"
            'arrows'         => 'true',
            'dots'           => 'false',
            'pause_on_hover' => 'true',
            'lightbox'       => 'false',    // Certificate / logo zoom modal
            'show_code'      => 'true',
            'badges_layout'  => 'wrap',     // 'wrap', 'stacked'
            'badges_align'   => 'center',   // 'center', 'left', 'right'
            'badges_valign'  => 'bottom',   // 'bottom', 'top', 'center'
            'show_title'     => 'false',
            'show_desc'      => 'false',
            'limit'          => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_assets'), 5);
        add_shortcode(self::TAG, array($this, 'render_shortcode'));
    }

    /**
     * Register frontend assets for conditional enqueueing.
     */
    public function register_assets()
    {
        wp_register_style(
            'gnn-logos-frontend',
            GNN_LOGOS_URL . 'assets/css/gnn-logos-frontend.css',
            array(),
            GNN_LOGOS_VERSION
        );

        wp_register_script(
            'gnn-logos-frontend',
            GNN_LOGOS_URL . 'assets/js/gnn-logos-frontend.js',
            array(),
            GNN_LOGOS_VERSION,
            true
        );
    }

    /**
     * Render [gnn_logos] shortcode / block output.
     *
     * @param array  $atts    User shortcode attributes.
     * @param string $content Enclosed content (unused).
     * @return string HTML output.
     */
    public function render_shortcode($atts, $content = '')
    {
        wp_enqueue_style('gnn-logos-frontend');
        wp_enqueue_script('gnn-logos-frontend');

        $atts = shortcode_atts(self::get_defaults(), (array) $atts, self::TAG);

        // Sanitize enums and booleans
        $layout         = in_array($atts['layout'], array('grid', 'carousel', 'marquee'), true) ? $atts['layout'] : 'carousel';
        $style          = in_array($atts['style'], array('minimal', 'card', 'bordered', 'glass'), true) ? $atts['style'] : 'minimal';
        $theme          = in_array($atts['theme'], array('light', 'dark', 'auto'), true) ? $atts['theme'] : 'light';
        $logo_filter    = in_array($atts['logo_filter'], array('none', 'grayscale', 'monochrome-white', 'monochrome-dark'), true) ? $atts['logo_filter'] : 'none';
        $grayscale      = filter_var($atts['grayscale'], FILTER_VALIDATE_BOOLEAN);
        if ('none' === $logo_filter && $grayscale) {
            $logo_filter = 'grayscale';
        }

        $columns        = max(1, min(12, absint($atts['columns']) ?: 4));
        $columns_tablet = max(1, min(8, absint($atts['columns_tablet']) ?: 3));
        $columns_mobile = max(1, min(6, absint($atts['columns_mobile']) ?: 2));
        $autoplay       = filter_var($atts['autoplay'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        $autoplay_speed = max(500, absint($atts['autoplay_speed']) ?: 3000);
        $arrows         = filter_var($atts['arrows'], FILTER_VALIDATE_BOOLEAN);
        $dots           = filter_var($atts['dots'], FILTER_VALIDATE_BOOLEAN);
        $pause_on_hover = filter_var($atts['pause_on_hover'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        $fade_edges     = filter_var($atts['fade_edges'], FILTER_VALIDATE_BOOLEAN);
        $lightbox       = filter_var($atts['lightbox'], FILTER_VALIDATE_BOOLEAN);
        $show_code      = filter_var($atts['show_code'], FILTER_VALIDATE_BOOLEAN);
        $badges_layout  = in_array($atts['badges_layout'], array('wrap', 'stacked'), true) ? $atts['badges_layout'] : 'wrap';
        $badges_align   = in_array($atts['badges_align'], array('center', 'left', 'right'), true) ? $atts['badges_align'] : 'center';
        $badges_valign  = in_array($atts['badges_valign'], array('top', 'center', 'bottom'), true) ? $atts['badges_valign'] : 'bottom';
        $show_title     = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $show_desc      = filter_var($atts['show_desc'], FILTER_VALIDATE_BOOLEAN);
        $direction      = in_array($atts['direction'], array('left', 'right'), true) ? $atts['direction'] : 'left';
        $limit          = intval($atts['limit']);
        if (0 === $limit) {
            $limit = -1;
        }

        // Strict CSS value validation (prevents CSS injection)
        $gap = sanitize_text_field($atts['gap']);
        if (is_numeric($gap)) {
            $gap .= 'px';
        }
        if (!preg_match('/^\d+(\.\d+)?(px|rem|em|%|vw)$/', $gap)) {
            $gap = '20px';
        }

        $aspect_ratio = str_replace(':', '/', sanitize_text_field($atts['aspect_ratio']));
        if ('auto' !== $aspect_ratio && !preg_match('/^\d+\/\d+$/', $aspect_ratio)) {
            $aspect_ratio = 'auto';
        }
        $has_aspect_ratio = ('auto' !== $aspect_ratio);

        $logo_height = sanitize_text_field($atts['logo_height']);
        if ('' !== $logo_height) {
            if (is_numeric($logo_height)) {
                $logo_height .= 'px';
            }
            if (!preg_match('/^\d+(\.\d+)?(px|rem|em)$/', $logo_height)) {
                $logo_height = '';
            }
        }
        $has_logo_height = ('' !== $logo_height);

        $item_width = sanitize_text_field($atts['item_width']);
        if ('' !== $item_width) {
            if (is_numeric($item_width)) {
                $item_width .= 'px';
            }
            if (!preg_match('/^\d+(\.\d+)?(px|rem|em|vw)$/', $item_width)) {
                $item_width = '';
            }
        }

        $speed = sanitize_text_field($atts['speed']);
        if (is_numeric($speed)) {
            $speed .= 's';
        }
        if (!preg_match('/^\d+(\.\d+)?(s|ms)$/', $speed)) {
            $speed = '25s';
        }

        // Strict whitelist for orderby parameter
        $allowed_orderby = array('none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'menu_order');
        $safe_orderby = in_array($atts['orderby'], $allowed_orderby, true) ? $atts['orderby'] : 'menu_order';

        // Optimized WP_Query (skips SQL_CALC_FOUND_ROWS)
        $query_args = array(
            'post_type'      => GNN_Logos_CPT::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => $safe_orderby,
            'order'          => in_array(strtoupper($atts['order']), array('ASC', 'DESC'), true) ? strtoupper($atts['order']) : 'ASC',
            'no_found_rows'  => true,
        );

        if (!empty($atts['group'])) {
            $group_slug = sanitize_title($atts['group']);
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => GNN_Logos_CPT::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $group_slug,
                ),
            );
        }

        $query = new WP_Query($query_args);

        if (!$query->have_posts()) {
            return '';
        }

        // Prepare items
        $items = array();
        while ($query->have_posts()) {
            $query->the_post();
            $post_id     = get_the_ID();
            $logo_id     = absint(get_post_meta($post_id, '_gnn_logo_id', true));
            $description = get_post_meta($post_id, '_gnn_description', true);
            $link_url    = get_post_meta($post_id, '_gnn_link_url', true);
            $link_target = get_post_meta($post_id, '_gnn_link_target', true) ?: '_blank';
            $item_ratio  = get_post_meta($post_id, '_gnn_aspect_ratio', true) ?: 'auto';

            // Support multi-standard codes and split legacy comma/newline strings
            $cert_codes = get_post_meta($post_id, '_gnn_cert_codes', true);
            if (!is_array($cert_codes) || empty($cert_codes)) {
                $legacy_code = get_post_meta($post_id, '_gnn_cert_code', true);
                if (!empty($legacy_code)) {
                    $parts = preg_split('/[\r\n,]+/', $legacy_code);
                    $cert_codes = array_filter(array_map('trim', $parts));
                } else {
                    $cert_codes = array();
                }
            }

            // Skip completely empty entries (no logo image, no badges, and title hidden)
            if (!$logo_id && empty($cert_codes) && !$show_title) {
                continue;
            }

            if ('auto' !== $item_ratio) {
                $item_ratio = str_replace(':', '/', $item_ratio);
                if (!preg_match('/^\d+\/\d+$/', $item_ratio)) {
                    $item_ratio = 'auto';
                }
            }

            $full_img_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';

            $items[] = array(
                'id'           => $post_id,
                'title'        => get_the_title(),
                'logo_id'      => $logo_id,
                'full_url'     => $full_img_url ? $full_img_url : '',
                'cert_codes'   => array_values($cert_codes),
                'description'  => $description,
                'link_url'     => $link_url,
                'link_target'  => $link_target,
                'aspect_ratio' => $item_ratio,
            );
        }
        wp_reset_postdata();

        if (empty($items)) {
            return '';
        }

        // Wrapper classes
        $wrapper_classes = array(
            'gnn-logos-wrapper',
            'gnn-logos-layout-' . $layout,
            'gnn-style-' . $style,
            'gnn-theme-' . $theme,
        );

        if ($has_aspect_ratio) {
            $wrapper_classes[] = 'gnn-has-aspect-ratio';
        }
        if ($has_logo_height) {
            $wrapper_classes[] = 'gnn-has-logo-height';
        }
        if ('none' !== $logo_filter) {
            $wrapper_classes[] = 'gnn-filter-' . $logo_filter;
        }
        if ('carousel' === $layout && !$arrows) {
            $wrapper_classes[] = 'gnn-no-arrows';
        }
        if ('marquee' === $layout) {
            $wrapper_classes[] = 'gnn-marquee-direction-' . $direction;
            if ($fade_edges) {
                $wrapper_classes[] = 'gnn-marquee-fade';
            }
        }

        // Inline CSS variables
        $css_vars = array(
            '--gnn-columns: ' . $columns,
            '--gnn-columns-tablet: ' . $columns_tablet,
            '--gnn-columns-mobile: ' . $columns_mobile,
            '--gnn-gap: ' . $gap,
            '--gnn-aspect-ratio: ' . $aspect_ratio,
            '--gnn-marquee-speed: ' . $speed,
        );
        if ($has_logo_height) {
            $css_vars[] = '--gnn-logo-height: ' . $logo_height;
        }
        if ('' !== $item_width) {
            $css_vars[] = '--gnn-marquee-item-width: ' . $item_width;
        }
        $style_attr = 'style="' . esc_attr(implode('; ', $css_vars)) . ';"';

        $render_opts = array(
            'layout'        => $layout,
            'show_code'     => $show_code,
            'show_title'    => $show_title,
            'show_desc'     => $show_desc,
            'badges_layout' => $badges_layout,
            'badges_align'  => $badges_align,
            'badges_valign' => $badges_valign,
            'lightbox'      => $lightbox,
        );

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" <?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ('carousel' === $layout) : ?>
                role="region"
                aria-roledescription="carousel"
                aria-label="<?php esc_attr_e('Logo ve Sertifika Karuseli', 'gnn-logos'); ?>"
                data-autoplay="<?php echo esc_attr($autoplay); ?>"
                data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
                data-pause-on-hover="<?php echo esc_attr($pause_on_hover); ?>"
            <?php endif; ?>
        >
            <?php if ('grid' === $layout) : ?>
                <div class="gnn-logos-grid-container">
                    <?php foreach ($items as $item) : ?>
                        <?php echo $this->render_item_html($item, $render_opts, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endforeach; ?>
                </div>

            <?php elseif ('carousel' === $layout) : ?>
                <?php if ($arrows) : ?>
                    <button type="button" class="gnn-carousel-arrow prev" aria-label="<?php esc_attr_e('Önceki', 'gnn-logos'); ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    </button>
                <?php endif; ?>

                <div class="gnn-carousel-viewport">
                    <?php foreach ($items as $item) : ?>
                        <div class="gnn-carousel-slide">
                            <?php echo $this->render_item_html($item, $render_opts, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($arrows) : ?>
                    <button type="button" class="gnn-carousel-arrow next" aria-label="<?php esc_attr_e('Sonraki', 'gnn-logos'); ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                    </button>
                <?php endif; ?>

                <?php if ($dots) : ?>
                    <div class="gnn-carousel-dots" role="tablist">
                        <?php foreach ($items as $idx => $item) : ?>
                            <button type="button" role="tab" aria-selected="<?php echo 0 === $idx ? 'true' : 'false'; ?>" class="gnn-carousel-dot <?php echo 0 === $idx ? 'active' : ''; ?>" aria-label="<?php echo esc_attr(sprintf(__('Slayt %d', 'gnn-logos'), $idx + 1)); ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ('marquee' === $layout) : ?>
                <div class="gnn-marquee-track <?php echo 'true' === $pause_on_hover ? 'pause-on-hover' : ''; ?>">
                    <div class="gnn-marquee-group">
                        <?php foreach ($items as $item) : ?>
                            <div class="gnn-marquee-item">
                                <?php echo $this->render_item_html($item, $render_opts, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="gnn-marquee-group" aria-hidden="true" data-gnn-clone="1">
                        <?php foreach ($items as $item) : ?>
                            <div class="gnn-marquee-item">
                                <?php echo $this->render_item_html($item, $render_opts, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper to render single logo item card HTML.
     *
     * @param array $item         Logo item data.
     * @param array $opts         Render options.
     * @param bool  $is_duplicate Whether rendered inside an aria-hidden duplicate marquee group.
     * @return string HTML output.
     */
    private function render_item_html($item, $opts, $is_duplicate = false)
    {
        $has_link     = !empty($item['link_url']);
        $use_lightbox = !empty($opts['lightbox']) && (!empty($item['full_url']) || !empty($item['title']));
        $tag          = 'div';
        $extra_attrs  = '';

        if ($use_lightbox) {
            $tag = 'button';
            $extra_attrs = sprintf(
                'type="button" data-gnn-lightbox="1" data-lb-img="%s" data-lb-title="%s" data-lb-desc="%s" data-lb-codes="%s" data-lb-url="%s" data-lb-target="%s" data-lb-link-label="%s"%s',
                esc_url($item['full_url']),
                esc_attr($item['title']),
                esc_attr($item['description']),
                esc_attr(implode('||', $item['cert_codes'])),
                esc_url($item['link_url']),
                esc_attr($item['link_target']),
                esc_attr__('Belgeyi / Sayfayı Aç', 'gnn-logos'),
                $is_duplicate ? ' tabindex="-1"' : ''
            );
        } elseif ($has_link) {
            $tag = 'a';
            $extra_attrs = sprintf(
                'href="%s" target="%s" rel="noopener noreferrer"%s',
                esc_url($item['link_url']),
                esc_attr($item['link_target']),
                $is_duplicate ? ' tabindex="-1"' : ''
            );
        }

        $box_style = '';
        if (!empty($item['aspect_ratio']) && 'auto' !== $item['aspect_ratio']) {
            $box_style = 'style="aspect-ratio:' . esc_attr($item['aspect_ratio']) . ';"';
        }

        $image_html = '';
        if (!empty($item['logo_id'])) {
            $img_args = array(
                'class'     => 'gnn-logo-img',
                'alt'       => $item['title'],
                'loading'   => ('marquee' === $opts['layout']) ? 'eager' : 'lazy',
                'decoding'  => 'async',
                'draggable' => 'false',
            );
            $image_html = wp_get_attachment_image($item['logo_id'], 'full', false, $img_args);
        }

        ob_start();
        ?>
        <<?php echo esc_attr($tag); ?> class="gnn-logo-item" <?php echo $extra_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <div class="gnn-logo-item-inner gnn-valign-<?php echo esc_attr($opts['badges_valign']); ?>">
                <?php if ($use_lightbox) : ?>
                    <span class="gnn-zoom-hint" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14zm.5-7H9v2H7v1h2v2h1v-2h2V9h-2V7z"/></svg>
                    </span>
                <?php endif; ?>

                <?php if (!empty($image_html)) : ?>
                    <div class="gnn-logo-box" <?php echo $box_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                <?php endif; ?>

                <?php if ($opts['show_code'] && !empty($item['cert_codes'])) : ?>
                    <div class="gnn-cert-badges-wrap gnn-badges-layout-<?php echo esc_attr($opts['badges_layout']); ?> gnn-badges-align-<?php echo esc_attr($opts['badges_align']); ?> gnn-badges-valign-<?php echo esc_attr($opts['badges_valign']); ?>">
                        <?php foreach ($item['cert_codes'] as $code) : ?>
                            <span class="gnn-cert-badge"><?php echo esc_html($code); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($opts['show_title'] && !empty($item['title'])) : ?>
                    <div class="gnn-logo-title"><?php echo esc_html($item['title']); ?></div>
                <?php endif; ?>

                <?php if ($opts['show_desc'] && !empty($item['description'])) : ?>
                    <div class="gnn-logo-desc"><?php echo esc_html($item['description']); ?></div>
                <?php endif; ?>
            </div>
        </<?php echo esc_attr($tag); ?>>
        <?php
        return ob_get_clean();
    }
}
