# Boundary Conditions & Security Invariants

- **Project**: GNN Logos
- **Status**: Enforced
- **Confidence**: Verified

## 1. Security Invariants (WordPress Ecosystem)

### 1.1 Direct File Access Guard
Every single PHP file within the plugin MUST begin with a direct file execution abort check:
```php
defined('ABSPATH') || exit;
```
*(For `uninstall.php`: `if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }`)*

### 1.2 Capability & Authorization Verification (BOLA / IDOR Defense)
Any administrative action, meta box save, AJAX handler, or settings update MUST check user capabilities:
- Meta box save: `current_user_can('edit_post', $post_id)`
- Generator page & Live Preview AJAX (`gnn_logos_preview`): `current_user_can('manage_options')`
- Drag-and-drop reorder AJAX (`gnn_logos_reorder`): `current_user_can('edit_others_posts') || current_user_can('manage_options')`
- Bulk logo creation AJAX (`gnn_logos_bulk_create`): `current_user_can('publish_posts')`
- Manual update check: `current_user_can('update_plugins')`

### 1.3 CSRF & Nonce Protection
All form submissions, meta box updates, and AJAX endpoints MUST use cryptographic nonces:
- Meta box: `wp_nonce_field('gnn_logos_save_meta', 'gnn_logos_meta_nonce')` / `wp_verify_nonce(..., 'gnn_logos_save_meta')`
- Admin AJAX (`gnn_logos_reorder`, `gnn_logos_bulk_create`, `gnn_logos_preview`): `check_ajax_referer('gnn_logos_admin_nonce', 'nonce')`
- Manual update check: `wp_nonce_url(..., 'gnn_logos_manual_update')`

### 1.4 Strict Input Sanitization
No unsanitized `$_POST` or `$_GET` variable may ever touch the database or CSS output:
- Text inputs (titles, subtitles, certificate codes): `sanitize_text_field(wp_unslash($input))`
- Multiline legacy cert codes: `sanitize_textarea_field(wp_unslash($input))`
- URLs (company links, certificate PDF links): `esc_url_raw(wp_unslash($input))`
- Integers / IDs (attachment ID, order, columns): `absint($input)`
- Choice enums (`layout`, `style`, `theme`, `logo_filter`, `orderby`, `order`, `direction`, `badges_*`): strict `in_array(..., true)` allowlists.
- CSS custom property values (`gap`, `aspect_ratio`, `logo_height`, `item_width`, `speed`): strict regex validation to prevent CSS injection.

### 1.5 Strict Output Escaping & DOM Safety (XSS Prevention)
No variable may be output into HTML attributes or tags without context-specific escaping:
- Inside HTML text: `esc_html($text)`
- Inside HTML attributes: `esc_attr($attr)`
- Inside href/src URLs: `esc_url($url)`
- Internationalized strings: `esc_html__('Text', 'gnn-logos')`
- Frontend JS (`gnn-logos-frontend.js`): Zero `innerHTML` usage; Lightbox DOM is constructed strictly via `document.createElement` and `textContent`.

## 2. Architectural & Performance Boundaries

### 2.1 Zero External Frontend Dependency Invariant
- **Rule**: Absolutely NO inclusion of jQuery slider plugins (Slick, Owl Carousel, bxSlider), external lightbox packages, npm runtime bundles, or CDN scripts on the frontend.
- **Enforcement**: Frontend carousel uses native CSS Scroll-Snap + Vanilla JS (`gnn-logos-frontend.js`); marquee uses multi-group GPU keyframe transforms (`transform: translate3d(-100%, 0, 0)`) with `ResizeObserver` viewport fill; lightbox uses zero-dependency Vanilla JS DOM dialog.

### 2.2 Asset Size Budgets (Updated per ADR-0010)
- Frontend CSS payload: Must remain `< 28 KB` unminified (~23.6 KB raw, ~5 KB gzip).
- Frontend JS payload: Must remain `< 20 KB` unminified (~17.7 KB raw, ~4.5 KB gzip).
- Conditional enqueueing: Frontend assets register on `init` priority `5` and only enqueue when `[gnn_logos]` or `gnn-logos/showcase` renders (plus inside the Admin Live Preview screen and Block Editor iframe).
- Admin assets: Only enqueue on `gnn_logo` CPT screens or the Shortcode Generator screen.

### 2.3 Responsive Image Standards
All logo images rendered on the frontend MUST:
- Use WordPress native `wp_get_attachment_image()` to automatically output responsive `srcset` and `sizes`.
- Include `decoding="async"` and `draggable="false"` (with `loading="eager"` for marquee to prevent blank cloned frames and `loading="lazy"` for carousel/grid).
- Apply `object-fit: contain` within an aspect-ratio / max-height container to prevent distortion.

### 2.4 Admin Menu Registry & Position Invariant
- **Slot**: Registered at position `'79.109'` adjacent to WordPress Settings, bound directly to `edit.php?post_type=gnn_logo`.
- **Submenus**: Order remains `All Items` (`Tüm Logolar & Sertifikalar`), `Add New` (`Yeni Ekle`), `Groups` (`Logo Grupları`), `Shortcode Generator` (`Shortcode Oluşturucu`). Manual update checks are exposed via `plugins.php` action links (`Check Updates`) and GNN Dashboard.

### 2.5 Multi-Standard Badges & Layout Invariant
- **Layouts**: `badges_layout` supports `wrap` and `stacked`.
- **Horizontal Alignment**: `badges_align` supports `center`, `left`, `right`.
- **Vertical Alignment**: `badges_valign` supports `bottom`, `top`, `center`.
- **Container Structure**: Monospace badge pills (`.gnn-cert-badge`) inside `.gnn-cert-badges-wrap`.

### 2.6 Unified GNN GitHub Updater Invariant (ADR-0009)
- **Slug Resolution**: Dynamically resolved via `plugin_basename(GNN_LOGOS_FILE)`.
- **Hook Architecture**: `pre_set_site_transient_update_plugins`, `plugins_api`, `upgrader_pre_install` (`capture_active_state`), `upgrader_post_install` (`after_install` with `wp_normalize_path(untrailingslashit(...))` + `wp_clean_plugins_cache(true)` + active state restoration), `admin_init`, and `load-update-core.php`.
- **Resilience & Security**: Primary GitHub Releases API with `wp_remote_head` + `/releases/expanded_assets/{tag}` fallback, strict SSRF host allowlist (`github.com`, `*.githubusercontent.com`), 12h positive cache, 5m negative cache.

## 3. End-to-End Integration Contract (Anti-Illusion Rule)
Every feature must verify all 5 links of the feature chain:
1. DB Persistence (`wp_posts`, `wp_postmeta`, `wp_term_relationships`)
2. Backend Handler (CPT save hook, AJAX endpoints, Block `render_callback`, Shortcode `WP_Query` with `no_found_rows => true`)
3. Frontend Renderer (HTML DOM generation with validated CSS custom properties and ARIA attributes)
4. UI Trigger Element (Carousel arrows/dots/drag, Lightbox buttons, Admin sortable rows, Bulk uploader, Live Preview switcher)
5. UI Feedback (Smooth scroll/snap, Lightbox modal + focus restore, Saved order toast, Instant live preview render)
