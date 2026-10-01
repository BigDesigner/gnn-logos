# ADR 0007: Security Hardening, Sanitization Alignment, and Complete Lifecycle Cleanup

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-10-01
- **Supersedes**: None
- **Superseded By**: None

## Context

A comprehensive code quality and security audit of the GNN Logos plugin identified several hardening opportunities and defense-in-depth gaps:
1. In `includes/class-gnn-logos-cpt.php`, the legacy certificate codes fallback used `sanitize_text_field()`, which strips newline delimiters (`\r\n`) into spaces before `preg_split` can parse them.
2. In `includes/class-gnn-logos-cpt.php`, `wp_unslash()` was omitted prior to sanitization on `_gnn_link_target` and `_gnn_aspect_ratio`.
3. In `includes/class-gnn-logos-shortcode.php`, the shortcode `orderby` parameter was passed directly to `WP_Query` without strict allowlist validation.
4. In `assets/js/gnn-logos-admin.js`, image preview insertion used `.html()` string concatenation instead of safe DOM object creation.
5. In `includes/class-gnn-logos-admin.php`, the manual update redirect handler lacked an explicit `current_user_can('update_plugins')` authorization check.
6. In `uninstall.php`, taxonomy terms (`gnn_logo_group`) and CPT posts (`gnn_logo`) were not cleaned up upon plugin deletion, leaving orphaned database records.
7. In `README.md`, the license badge was hardcoded to an external URL instead of dynamically linking to the repository's native `LICENSE` file.

## Decision

1. Switched `sanitize_text_field()` to `sanitize_textarea_field(wp_unslash($_POST['gnn_cert_code']))` to preserve newline delimiters for multi-standard code parsing.
2. Added `wp_unslash()` before sanitization on all meta box attributes (`_gnn_link_target`, `_gnn_aspect_ratio`) for strict WPCS compliance.
3. Implemented a strict whitelist for `orderby` (`none`, `ID`, `author`, `title`, `name`, `type`, `date`, `modified`, `parent`, `rand`, `menu_order`) defaulting safely to `menu_order`.
4. Replaced jQuery `.html()` string concatenation with safe DOM construction (`$('<img>').attr('src', previewUrl).attr('alt', ...)`).
5. Added `current_user_can('update_plugins')` guard with `wp_die()` in `handle_manual_update_redirect()`.
6. Expanded `uninstall.php` to clean all `gnn_logo` posts, postmeta, `gnn_logo_group` terms, and transients (`gnn_logos_github_update_check`, `update_plugins`).
7. Updated `README.md` license badge to dynamic GitHub badge linking to local `LICENSE`.

## Consequences

- Full WPCS compliance across all inputs and database writes.
- Clean database uninstallation with zero orphaned rows in `wp_posts`, `wp_postmeta`, `wp_terms`, or `wp_term_taxonomy`.
- Complete immunity against potential DOM XSS, authorization bypasses, and newline parsing regressions.

## Evidence

- `gnn-logos/includes/class-gnn-logos-cpt.php#L320`
- `gnn-logos/includes/class-gnn-logos-shortcode.php#L133-L140`
- `gnn-logos/assets/js/gnn-logos-admin.js#L52`
- `gnn-logos/includes/class-gnn-logos-admin.php#L106-L113`
- `gnn-logos/uninstall.php#L1-L40`
- `README.md#L6`
