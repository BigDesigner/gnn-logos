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

// Clean updater transients
delete_transient('gnn_logos_github_update_check');
delete_site_transient('gnn_logos_github_update_check');
