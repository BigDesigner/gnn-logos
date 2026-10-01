<?php
/**
 * Shortcode Engine and HTML Renderer for GNN Logos.
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
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_assets'));
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
     * Render [gnn_logos] shortcode output.
     *
     * @param array  $atts    User shortcode attributes.
     * @param string $content Enclosed content (unused).
     * @return string HTML output.
     */
    public function render_shortcode($atts, $content = '')
    {
        // Enqueue styles and scripts only when shortcode is executed
        wp_enqueue_style('gnn-logos-frontend');
        wp_enqueue_script('gnn-logos-frontend');

        $atts = shortcode_atts(
            array(
                'group'          => '',
                'layout'         => 'carousel', // 'grid', 'carousel', 'marquee'
                'style'          => 'minimal',  // 'minimal', 'card', 'bordered'
                'columns'        => 4,
                'columns_tablet' => 3,
                'columns_mobile' => 2,
                'aspect_ratio'   => 'auto',     // 'auto', '16/9', '4/3', '1/1', '3/2', '2/1'
                'gap'            => '20px',
                'autoplay'       => 'true',
                'autoplay_speed' => 3000,
                'speed'          => '25s',
                'direction'      => 'left',     // 'left', 'right'
                'grayscale'      => 'false',
                'arrows'         => 'true',
                'dots'           => 'false',
                'pause_on_hover' => 'true',
                'show_code'      => 'true',
                'badges_layout'  => 'wrap',     // 'wrap' (yan yana), 'stacked' (alt alta)
                'badges_align'   => 'center',   // 'center' (ortalı), 'left' (sola), 'right' (sağa)
                'badges_valign'  => 'bottom',   // 'bottom' (altta), 'top' (üstte), 'center' (ortada)
                'show_title'     => 'false',
                'show_desc'      => 'false',
                'limit'          => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ),
            $atts,
            self::TAG
        );

        // Sanitize attributes
        $layout         = in_array($atts['layout'], array('grid', 'carousel', 'marquee'), true) ? $atts['layout'] : 'carousel';
        $style          = in_array($atts['style'], array('minimal', 'card', 'bordered'), true) ? $atts['style'] : 'minimal';
        $columns        = absint($atts['columns']) ?: 4;
        $columns_tablet = absint($atts['columns_tablet']) ?: 3;
        $columns_mobile = absint($atts['columns_mobile']) ?: 2;
        $autoplay       = filter_var($atts['autoplay'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        $autoplay_speed = absint($atts['autoplay_speed']) ?: 3000;
        $grayscale      = filter_var($atts['grayscale'], FILTER_VALIDATE_BOOLEAN);
        $arrows         = filter_var($atts['arrows'], FILTER_VALIDATE_BOOLEAN);
        $dots           = filter_var($atts['dots'], FILTER_VALIDATE_BOOLEAN);
        $pause_on_hover = filter_var($atts['pause_on_hover'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        $show_code      = filter_var($atts['show_code'], FILTER_VALIDATE_BOOLEAN);
        $badges_layout  = in_array($atts['badges_layout'], array('wrap', 'stacked'), true) ? $atts['badges_layout'] : 'wrap';
        $badges_align   = in_array($atts['badges_align'], array('center', 'left', 'right'), true) ? $atts['badges_align'] : 'center';
        $badges_valign  = in_array($atts['badges_valign'], array('top', 'center', 'bottom'), true) ? $atts['badges_valign'] : 'bottom';
        $show_title     = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $show_desc      = filter_var($atts['show_desc'], FILTER_VALIDATE_BOOLEAN);
        $direction      = in_array($atts['direction'], array('left', 'right'), true) ? $atts['direction'] : 'left';
        $limit          = intval($atts['limit']);

        // Normalize gap
        $gap = sanitize_text_field($atts['gap']);
        if (is_numeric($gap)) {
            $gap .= 'px';
        }

        // Normalize aspect ratio
        $aspect_ratio = str_replace(':', '/', sanitize_text_field($atts['aspect_ratio']));
        $has_aspect_ratio = ('auto' !== $aspect_ratio);

        // Normalize speed
        $speed = sanitize_text_field($atts['speed']);
        if (is_numeric($speed)) {
            $speed .= 's';
        }

        // Strict whitelist for orderby parameter (defense-in-depth)
        $allowed_orderby = array('none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'menu_order');
        $safe_orderby = in_array($atts['orderby'], $allowed_orderby, true) ? $atts['orderby'] : 'menu_order';

        // Build query args
        $query_args = array(
            'post_type'      => GNN_Logos_CPT::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => $safe_orderby,
            'order'          => in_array(strtoupper($atts['order']), array('ASC', 'DESC'), true) ? strtoupper($atts['order']) : 'ASC',
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

        // Prepare post items
        $items = array();
        while ($query->have_posts()) {
            $query->the_post();
            $post_id      = get_the_ID();
            $logo_id      = absint(get_post_meta($post_id, '_gnn_logo_id', true));
            $description  = get_post_meta($post_id, '_gnn_description', true);
            $link_url     = get_post_meta($post_id, '_gnn_link_url', true);
            $link_target  = get_post_meta($post_id, '_gnn_link_target', true) ?: '_blank';
            $item_ratio   = get_post_meta($post_id, '_gnn_aspect_ratio', true) ?: 'auto';

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

            if ('auto' !== $item_ratio) {
                $item_ratio = str_replace(':', '/', $item_ratio);
            }

            $items[] = array(
                'id'           => $post_id,
                'title'        => get_the_title(),
                'logo_id'      => $logo_id,
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

        // Classes for wrapper
        $wrapper_classes = array(
            'gnn-logos-wrapper',
            'gnn-logos-layout-' . esc_attr($layout),
            'gnn-style-' . esc_attr($style),
        );

        if ($has_aspect_ratio) {
            $wrapper_classes[] = 'gnn-has-aspect-ratio';
        }
        if ($grayscale) {
            $wrapper_classes[] = 'gnn-grayscale-enabled';
        }
        if ('carousel' === $layout && !$arrows) {
            $wrapper_classes[] = 'gnn-no-arrows';
        }
        if ('marquee' === $layout) {
            $wrapper_classes[] = 'gnn-marquee-direction-' . esc_attr($direction);
        }

        // Inline CSS variables
        $css_vars = array(
            '--gnn-columns: ' . intval($columns),
            '--gnn-columns-tablet: ' . intval($columns_tablet),
            '--gnn-columns-mobile: ' . intval($columns_mobile),
            '--gnn-gap: ' . esc_attr($gap),
            '--gnn-aspect-ratio: ' . esc_attr($aspect_ratio),
            '--gnn-marquee-speed: ' . esc_attr($speed),
        );
        $style_attr = 'style="' . esc_attr(implode('; ', $css_vars)) . ';"';

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" <?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php if ('carousel' === $layout) : ?>
                data-autoplay="<?php echo esc_attr($autoplay); ?>"
                data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
                data-pause-on-hover="<?php echo esc_attr($pause_on_hover); ?>"
            <?php endif; ?>
        >
            <?php if ('grid' === $layout) : ?>
                <!-- GRID LAYOUT -->
                <div class="gnn-logos-grid-container">
                    <?php foreach ($items as $item) : ?>
                        <?php echo $this->render_item_html($item, $show_code, $show_title, $show_desc, $badges_layout, $badges_align, $badges_valign); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endforeach; ?>
                </div>

            <?php elseif ('carousel' === $layout) : ?>
                <!-- CAROUSEL LAYOUT -->
                <?php if ($arrows) : ?>
                    <button type="button" class="gnn-carousel-arrow prev" aria-label="<?php esc_attr_e('Önceki', 'gnn-logos'); ?>">
                        <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    </button>
                <?php endif; ?>

                <div class="gnn-carousel-viewport">
                    <?php foreach ($items as $item) : ?>
                        <div class="gnn-carousel-slide">
                            <?php echo $this->render_item_html($item, $show_code, $show_title, $show_desc, $badges_layout, $badges_align, $badges_valign); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($arrows) : ?>
                    <button type="button" class="gnn-carousel-arrow next" aria-label="<?php esc_attr_e('Sonraki', 'gnn-logos'); ?>">
                        <svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                    </button>
                <?php endif; ?>

                <?php if ($dots) : ?>
                    <div class="gnn-carousel-dots" role="tablist">
                        <?php foreach ($items as $idx => $item) : ?>
                            <button type="button" class="gnn-carousel-dot <?php echo 0 === $idx ? 'active' : ''; ?>" aria-label="<?php echo esc_attr(sprintf(__('Slayt %d', 'gnn-logos'), $idx + 1)); ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ('marquee' === $layout) : ?>
                <!-- CONTINUOUS TICKER / MARQUEE LAYOUT -->
                <div class="gnn-marquee-track <?php echo 'true' === $pause_on_hover ? 'pause-on-hover' : ''; ?>">
                    <!-- Primary set -->
                    <?php foreach ($items as $item) : ?>
                        <div class="gnn-marquee-item">
                            <?php echo $this->render_item_html($item, $show_code, $show_title, $show_desc, $badges_layout, $badges_align, $badges_valign); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endforeach; ?>
                    <!-- Duplicate set for seamless continuous loop -->
                    <?php foreach ($items as $item) : ?>
                        <div class="gnn-marquee-item" aria-hidden="true">
                            <?php echo $this->render_item_html($item, $show_code, $show_title, $show_desc, $badges_layout, $badges_align, $badges_valign); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper to render single logo item card HTML.
     *
     * @param array  $item          Logo item data.
     * @param bool   $show_code     Show certificate standard code.
     * @param bool   $show_title    Show title.
     * @param bool   $show_desc     Show description.
     * @param string $badges_layout Badges layout ('wrap' or 'stacked').
     * @param string $badges_align  Badges alignment ('center', 'left', 'right').
     * @param string $badges_valign Badges vertical alignment ('bottom', 'top', 'center').
     * @return string HTML output.
     */
    private function render_item_html($item, $show_code, $show_title, $show_desc, $badges_layout = 'wrap', $badges_align = 'center', $badges_valign = 'bottom')
    {
        $has_link = !empty($item['link_url']);
        $tag = $has_link ? 'a' : 'div';
        $link_attrs = '';

        if ($has_link) {
            $link_attrs = sprintf(
                'href="%s" target="%s" rel="noopener noreferrer"',
                esc_url($item['link_url']),
                esc_attr($item['link_target'])
            );
        }

        $box_style = '';
        if (!empty($item['aspect_ratio']) && 'auto' !== $item['aspect_ratio']) {
            $box_style = 'style="aspect-ratio:' . esc_attr($item['aspect_ratio']) . ';"';
        }

        $image_html = '';
        if (!empty($item['logo_id'])) {
            $image_html = wp_get_attachment_image(
                $item['logo_id'],
                'full',
                false,
                array(
                    'class'    => 'gnn-logo-img',
                    'alt'      => esc_attr($item['title']),
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                )
            );
        }

        ob_start();
        ?>
        <<?php echo esc_attr($tag); ?> class="gnn-logo-item" <?php echo $link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <div class="gnn-logo-item-inner gnn-valign-<?php echo esc_attr($badges_valign); ?>">
                <div class="gnn-logo-box" <?php echo $box_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <?php if ($show_code && !empty($item['cert_codes'])) : ?>
                    <div class="gnn-cert-badges-wrap gnn-badges-layout-<?php echo esc_attr($badges_layout); ?> gnn-badges-align-<?php echo esc_attr($badges_align); ?> gnn-badges-valign-<?php echo esc_attr($badges_valign); ?>">
                        <?php foreach ($item['cert_codes'] as $code) : ?>
                            <span class="gnn-cert-badge"><?php echo esc_html($code); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_title && !empty($item['title'])) : ?>
                    <div class="gnn-logo-title"><?php echo esc_html($item['title']); ?></div>
                <?php endif; ?>

                <?php if ($show_desc && !empty($item['description'])) : ?>
                    <div class="gnn-logo-desc"><?php echo esc_html($item['description']); ?></div>
                <?php endif; ?>
            </div>
        </<?php echo esc_attr($tag); ?>>
        <?php
        return ob_get_clean();
    }
}
