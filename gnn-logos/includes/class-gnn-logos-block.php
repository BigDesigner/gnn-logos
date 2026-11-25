<?php
/**
 * Native Gutenberg Block Engine for GNN Logos (API Version 3).
 *
 * Registers the dynamic `gnn-logos/showcase` block using block.json
 * and delegates rendering directly to GNN_Logos_Shortcode.
 *
 * @package GNN_Logos
 * @since   2.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class GNN_Logos_Block
 */
class GNN_Logos_Block
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_block'));
    }

    /**
     * Register block editor script and block.json metadata.
     */
    public function register_block()
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        wp_register_script(
            'gnn-logos-block-editor',
            GNN_LOGOS_URL . 'blocks/showcase/editor.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render'),
            GNN_LOGOS_VERSION,
            true
        );

        $group_options = array();
        $terms = get_terms(array(
            'taxonomy'   => GNN_Logos_CPT::TAXONOMY,
            'hide_empty' => false,
        ));
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $group_options[] = array(
                    'label' => $term->name . ' (' . $term->count . ')',
                    'value' => $term->slug,
                );
            }
        }

        wp_localize_script('gnn-logos-block-editor', 'gnnLogosBlockData', array(
            'groups' => $group_options,
        ));

        register_block_type(
            GNN_LOGOS_DIR . 'blocks/showcase',
            array(
                'render_callback' => array($this, 'render_block'),
            )
        );
    }

    /**
     * Dynamic render callback for `gnn-logos/showcase`.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Saved block content (unused).
     * @param WP_Block $block      Block instance.
     * @return string
     */
    public function render_block($attributes, $content = '', $block = null)
    {
        $shortcode_atts = array();
        foreach ((array) $attributes as $key => $val) {
            if (is_bool($val)) {
                $shortcode_atts[$key] = $val ? 'true' : 'false';
            } else {
                $shortcode_atts[$key] = $val;
            }
        }

        $html = GNN_Logos::instance()->shortcode->render_shortcode($shortcode_atts);

        if ('' === trim($html)) {
            if (defined('REST_REQUEST') && REST_REQUEST) {
                return '<div style="padding:20px; border:1px dashed #94a3b8; border-radius:8px; text-align:center; color:#475569; font-family:sans-serif; font-size:13px;">'
                    . esc_html__('GNN Logos: Seçili grupta yayımlanmış logo bulunamadı.', 'gnn-logos')
                    . '</div>';
            }
            return '';
        }

        $wrapper_attrs = function_exists('get_block_wrapper_attributes')
            ? get_block_wrapper_attributes(array('class' => 'gnn-logos-block-container'))
            : 'class="gnn-logos-block-container"';

        return '<div ' . $wrapper_attrs . '>' . $html . '</div>';
    }
}
