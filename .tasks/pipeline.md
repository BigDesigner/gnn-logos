# Task Pipeline & Implementation Roadmap

- **Project**: GNN Logos
- **Author**: BigDesigner
- **Admin Menu Slot**: `'79.109'`
- **Active Sprint**: v2.0.0
- **Status**: Sprint Completed & Verified

## Sprint Backlog: v1.0.0-mvp

### [x] TASK-001: Plugin Core Architecture & Action Links (`gnn-logos/gnn-logos.php`)
- Standard GNN plugin header (Author: BigDesigner, URI: https://github.com/BigDesigner/gnn-logos).
- Plugin constants (`GNN_LOGOS_VERSION`, `GNN_LOGOS_DIR`, `GNN_LOGOS_URL`, `GNN_LOGOS_FILE`).
- Activation/deactivation hooks with rewrite rule flushing.
- Action links filter (`Donate` -> BuyMeACoffee, `Shortcode Oluşturucu`, `Check Updates`).
- Loaded components: `inc/updater.php`, `class-gnn-logos-cpt.php`, `class-gnn-logos-shortcode.php`, `class-gnn-logos-admin.php`.

### [x] TASK-002: Native GitHub Releases Auto-Updater (`gnn-logos/inc/updater.php`)
- Implemented `GNN_Logos_Updater` based on verified GNN family updater pattern.
- Transient cache key `gnn_logos_github_update_check` (12h TTL).
- WordPress filter integrations (`pre_set_site_transient_update_plugins`, `plugins_api`, `upgrader_post_install`).
- Manual check handler with nonce security (`plugins.php?gnn_logos_check_update=1`).
- Post-install directory name normalization (`gnn-logos`).
- SSRF validation on download URL host (`github.com`, `codeload.github.com`).

### [x] TASK-003: CPT, Taxonomy, and Meta Box Engine (`gnn-logos/includes/class-gnn-logos-cpt.php`)
- Registered `gnn_logo` custom post type.
- Registered `gnn_logo_group` hierarchical taxonomy (References, Partners, Certificates, etc.).
- Added Custom Meta Boxes:
  - WP Media Uploader ("Gözat / Logo Seç") button, preview thumbnail, remove button.
  - Certificate / Standard Code (`_gnn_cert_code` e.g., `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2`).
  - Subtitle / Description (`_gnn_description`).
  - Target URL & Link Target (`_self` / `_blank`).
  - Aspect Ratio override (`auto`, `16:9`, `4:3`, `1:1`, `3:2`, `2:1`).
- Nonce and capability verification on save.
- Custom admin columns (Thumbnail preview, Standard Code badge, Group, Target URL, Menu Order).

### [x] TASK-004: Ultra-Lightweight Frontend Assets (`gnn-logos/assets/`)
- `assets/css/gnn-logos-frontend.css` (8.7 KB):
  - CSS Grid with dynamic columns and gap custom properties.
  - CSS Scroll-Snap horizontal carousel with smooth navigation.
  - Pure CSS GPU-accelerated Keyframe Marquee (`transform: translate3d`).
  - Modern Certificate Card (`.gnn-logo-card`) and Standard Code Badge (`.gnn-cert-code`).
  - Grayscale hover filter effects (`grayscale(100%)` to `grayscale(0%)`).
  - Responsive breakpoints (desktop, tablet, mobile).
- `assets/js/gnn-logos-frontend.js` (4.6 KB):
  - Micro Vanilla JS controller (<3KB unminified, zero dependencies) for touch swipe, arrow buttons, autoplay loop with pause-on-hover.

### [x] TASK-005: Shortcode Engine & HTML Renderer (`gnn-logos/includes/class-gnn-logos-shortcode.php`)
- Registered `[gnn_logos]` shortcode with conditional asset enqueueing.
- Parsed attributes: `group`, `layout`, `style`, `columns`, `columns_tablet`, `columns_mobile`, `aspect_ratio`, `gap`, `autoplay`, `autoplay_speed`, `speed`, `grayscale`, `show_code`, `show_title`, `show_desc`, `arrows`, `dots`, `pause_on_hover`, `limit`, `orderby`, `order`.
- Safe `WP_Query` execution with group filtering.
- Seamless infinite marquee item duplication.
- Responsive images using `wp_get_attachment_image`, `srcset`, `loading="lazy"`, `decoding="async"`, and contextual escaping.

### [x] TASK-006: Admin Navigation & Shortcode Generator UI (`gnn-logos/includes/class-gnn-logos-admin.php`)
- Registered top-level menu `GNN Logos` at exact position `'79.109'`.
- Submenus:
  - `Tüm Logolar & Sertifikalar` (CPT edit screen)
  - `Yeni Ekle` (CPT new post screen)
  - `Logo Grupları` (Taxonomy screen)
  - `Shortcode Oluşturucu` (Interactive generator wizard)
  - `Güncellemeleri Kontrol Et` (Manual update check redirect)
- Interactive Shortcode Generator UI with presets (Certificates, Marquee, Partners, Grid), live code update, and one-click copy.
- Enqueue admin media scripts and CSS.

### [x] TASK-007: Readme Documentation & Syntax Validation
- Created `gnn-logos/readme.txt` and root `README.md`.
- Created `gnn-logos/uninstall.php`.
- Ran `php -l` on all PHP files (0 errors).

### [x] TASK-008: Multi-Standard Tag Chips & Badges System (v1.0.1)
- Dynamic tag manager in admin CPT edit screen (`_gnn_cert_codes[]`).
- CSS flex-wrap monospace badge pill styling (`.gnn-cert-badges-wrap`, `.gnn-cert-badge`).
- Backward compatible legacy string parsing.

### [x] TASK-009: Badges Layout & Alignment Controls (v1.0.2)
- Added `badges_layout` ('wrap' | 'stacked') and `badges_align` ('center' | 'left' | 'right') to `[gnn_logos]`.
- Frontend flexbox styles for stacked and wrapped layouts with left/center/right alignment.
- Admin Shortcode Generator UI dropdowns with dynamic slide toggle based on `show_code`.
- Version bump to 1.0.2 across all files.

### [x] TASK-010: Updater Cache Desync, Explicit Wizard Attributes, and Button Icon Alignment (v1.1.0)
- Fixed updater transient desync with `site_transient_update_plugins` filter and stale response purging.
- Forced explicit inclusion of `badges_align="center"` and `badges_layout="wrap"` in shortcode generator.
- Fixed admin button Dashicon vertical centering with unified flexbox alignment rules.
- Bumped version to 1.1.0 across all files.

### [x] TASK-011: Badges Vertical Alignment Controls (v1.1.1)
- Added `badges_valign` ('bottom' | 'top' | 'center') to `[gnn_logos]` shortcode.
- Created flexbox rules `.gnn-valign-top`, `.gnn-valign-center`, and `.gnn-valign-bottom` in `gnn-logos-frontend.css`.
- Added "Dikey Hizalama" dropdown in Shortcode Builder admin UI.
- Bumped version to 1.1.1 across all files.

### [x] TASK-012: Security Hardening & Uninstall Cleanup (v1.2.0)
- Fixed logic bug: `sanitize_text_field` → `sanitize_textarea_field` for legacy cert code fallback to preserve newlines for `preg_split`.
- Added `wp_unslash()` to `_gnn_link_target` and `_gnn_aspect_ratio` save paths (WPCS compliance).
- Added strict `orderby` whitelist in shortcode `WP_Query` (defense-in-depth).
- Replaced `.html()` string concatenation with safe jQuery DOM creation in admin JS (DOM XSS prevention).
- Added `current_user_can('update_plugins')` capability check in `handle_manual_update_redirect()`.
- Complete uninstall cleanup: `gnn_logo` posts + postmeta, `gnn_logo_group` taxonomy terms, and all transients.
- Bumped version to 1.2.0 across all files.

### [x] TASK-013: WP 7.1 Compatibility, Updater Cache Fix & WCAG Accessibility (v1.2.1)
- Moved `load_plugin_textdomain()` to `init` priority `1` and added plugin header requirements (`Requires at least: 5.8`, `Requires PHP: 8.0`).
- Fixed updater `'error'` transient sentinel, enforced `https` scheme in SSRF check, and added `requires`/`tested`/`requires_php` to `plugin_info()`.
- Added WCAG accessibility: `tabindex="-1"` on marquee duplicates, `role="tab"`/`aria-selected` on dots, keyboard navigation, `:focus-visible`, and `prefers-reduced-motion`.
- Added `php -l` validation step to `.github/workflows/release.yml` and bumped version to `1.2.1`.

### [x] TASK-014: Marquee Width Constraint & Flex/Grid Containment (v1.2.2)
- Constrained `.gnn-marquee-item` with `width: clamp(140px, 16vw, 200px)` and `max-height: 90px` for `auto` aspect ratio in `gnn-logos-frontend.css`.
- Added `max-width: 100%; min-width: 0;` to `.gnn-logos-wrapper` and `.gnn-logos-layout-marquee`.
- Bumped version to `1.2.2`.

### [x] TASK-015: Shortcode CSS Injection Defense, Full POT & Drag Guard (v1.2.3)
- Enforced strict regex validation on `gap`, `aspect_ratio`, and `speed` CSS variables in `class-gnn-logos-shortcode.php`.
- Added `requires`/`tested`/`requires_php` to `check_for_update()` and `$translate = false` to `get_plugin_data()`.
- Added `role="region"` / `aria-roledescription="carousel"` and `hasDragged` click suppression in `gnn-logos-frontend.js`.
- Created root `VERSION` file and populated `gnn-logos/languages/gnn-logos.pot` (120 strings); bumped version to `1.2.3`.

### [x] TASK-016: Direct CPT Admin Menu & Sidebar Cleanup (v1.2.4)
- Bound top-level `'79.109'` menu directly to `edit.php?post_type=gnn_logo` to eliminate duplicate empty submenu.
- Removed redundant `Güncellemeleri Kontrol Et` submenu from admin sidebar (updates handled via GNN Dashboard & `plugins.php` action link).
- Updated `plugin_action_links` generator URL and bumped version to `1.2.4`.

### [x] TASK-017: Updater `after_install()` Path Normalization Fix (v1.2.5)
- Normalized `$result['destination']` and `$plugin_dir` via `wp_normalize_path(untrailingslashit(...))` in `inc/updater.php` to prevent self-deletion on upgrade.
- Bumped version to `1.2.5`.

### [x] TASK-018: Unified GNN GitHub Updater Standard (v1.2.6 - ADR-0009)
- Upgraded `inc/updater.php` with `upgrader_pre_install` active-state capture, `wp_remote_head` + `expanded_assets` fallback, `wp_clean_plugins_cache(true)`, and post-install reactivation.
- Bumped version to `1.2.6`.

### [x] TASK-019: AAA v2.0.0 Engine Overhaul, Theme System, Lightbox, Admin UX & Gutenberg Block (v2.0.0 - ADR-0010)
- Rebuilt frontend CSS (`gnn-logos-frontend.css`) with CSS custom property themes (`light`, `dark`, `auto`), `glass` style, `logo_filter` (`grayscale`, `monochrome-white`, `monochrome-dark`), multi-group zero-jump marquee, `.gnn-carousel-static`/`.is-dragging` states, and `.gnn-lightbox` modal.
- Rebuilt frontend JS (`gnn-logos-frontend.js`) with `window.GNNLogosInit`, `ResizeObserver` marquee viewport fill, fixed carousel off-by-one & dynamic dots, `IntersectionObserver` + `visibilitychange` + `focusin` autoplay pausing, and delegated zero-`innerHTML` Lightbox.
- Upgraded `class-gnn-logos-shortcode.php` with shared `get_defaults()`, new attributes (`theme`, `style="glass"`, `logo_filter`, `logo_height`, `item_width`, `fade_edges`, `lightbox`), regex CSS validation, `no_found_rows => true`, and `init` priority `5` asset registration.
- Upgraded `class-gnn-logos-cpt.php` with drag-and-drop `menu_order` reorder (`gnn_logos_reorder`), Bulk Logo Uploader (`gnn_logos_bulk_create`), `gnn_logo_group` filter dropdown, and sortable `Sıra` column.
- Rebuilt `class-gnn-logos-admin.php`, `gnn-logos-admin.js`, and `gnn-logos-admin.css` with Live Visual Preview (`gnn_logos_preview`), Light/Dark/Checker canvas switcher, 5 presets, layout-aware panels, and fixed `show_code="false"` emission.
- Added zero-build Native Gutenberg Block `gnn-logos/showcase` (`blocks/showcase/block.json`, `editor.js`, `includes/class-gnn-logos-block.php`).
- Regenerated `languages/gnn-logos.pot` (177 strings), validated PHP/JS/JSON, bumped version to `2.0.0`, and published `v2.0.0` GitHub Release.
