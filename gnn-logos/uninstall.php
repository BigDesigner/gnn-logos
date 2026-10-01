<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package GNN_Logos
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 1. Delete all gnn_logo posts and their postmeta.
$posts = get_posts(array(
    'post_type'      => 'gnn_logo',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
));

if (!empty($posts)) {
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }
}

// 2. Delete all gnn_logo_group taxonomy terms.
$terms = get_terms(array(
    'taxonomy'   => 'gnn_logo_group',
    'hide_empty' => false,
    'fields'     => 'ids',
));

if (!is_wp_error($terms) && !empty($terms)) {
    foreach ($terms as $term_id) {
        wp_delete_term($term_id, 'gnn_logo_group');
    }
}

// 3. Clean updater transients.
delete_transient('gnn_logos_github_update_check');
delete_site_transient('gnn_logos_github_update_check');
delete_site_transient('update_plugins');
