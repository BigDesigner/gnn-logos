# Verified Worklog

- **Project**: GNN Logos
- **Sprint**: v1.1.1

## Completed Work
- **2026-10-01** (v1.1.1):
  - **Badges Vertical Alignment (`badges_valign`)**: Added `badges_valign` parameter with options `bottom` (fixed to bottom of card), `top` (starts directly 14px beneath the logo, eliminating giant gaps in cards with fewer badges), and `center` (centered vertically with logo).
  - **Admin Generator Dikey Hizalama**: Integrated "Dikey Hizalama" dropdown into the Shortcode Builder form with real-time shortcode generation.
  - **CSS Card Flexbox Alignment**: Implemented `.gnn-valign-top`, `.gnn-valign-center`, and `.gnn-valign-bottom` rules in `gnn-logos-frontend.css`.
  - **Version Bump**: Bumped version to `1.1.1` across all manifests and assets.
- **2026-10-01** (v1.1.0):
  - **Button Icon Vertical Centering**: Added universal CSS flexbox centering for all admin buttons and Dashicons (`.gnn-admin-wrap .button`, `.gnn-meta-box-wrap .button`, `.gnn-preset-buttons .button`, `#gnn-copy-shortcode-btn`). Completely eliminated bottom-heavy icon misalignment, aligning icons and button text strictly on the mathematical vertical center.
  - **Explicit Wizard Attributes**: Modified `updateShortcode` in `gnn-logos-admin.js` to unconditionally output `badges_align="center"` and `badges_layout="wrap"` when selected, eliminating confusing omissions.
  - **Updater Cache Desync Fix**: Fixed updater issue where WordPress `update_plugins` transient retained stale update notifications. Added `site_transient_update_plugins` filter on read, ensured `unset($transient->response[$this->plugin_slug])` occurs when `version_compare` is equal or newer, and flushed transient cache on install/core-update.
  - **Dynamic Plugin Slug Resolution**: Dynamically resolved `$this->plugin_slug` using `plugin_basename(GNN_LOGOS_FILE)` to ensure 100% key matching across custom directory names.
  - **Version Bump**: Bumped version to `1.1.0` across all manifests and assets.
- **2026-10-01** (v1.0.2):
  - **Badges Layout Controls**: Added `badges_layout` parameter (`wrap` for flex-wrap side-by-side, `stacked` for vertical column stacked).
  - **Badges Alignment Controls**: Added `badges_align` parameter (`center` for centered, `left` for left-aligned, `right` for right-aligned).
  - **Admin UI Generator**: Integrated "Rozet Dizilimi" and "Rozet Hizalama" dropdowns into the Shortcode Generator UI with dynamic slide toggling based on `show_code`.
  - **CSS Enhancements**: Added responsive layout & alignment classes (`.gnn-badges-layout-wrap`, `.gnn-badges-layout-stacked`, `.gnn-badges-align-center`, `.gnn-badges-align-left`, `.gnn-badges-align-right`) and overflow protection for badges.
  - **Version Bump**: Bumped plugin version, constant, stable tag, and assets to `1.0.2`.
- **2026-10-01** (v1.0.1):
  - **Multi-Standard Badges**: Replaced single-block standard code rendering with `.gnn-cert-badges-wrap` and `.gnn-cert-badge` chips. Allows 3-5 standards per certificate (TS EN 12201-2, TS EN ISO 1452-2, etc.) to wrap gracefully into separate, beautiful monospace pills rather than clumping into one overflowing box.
  - **Dynamic Admin Tag Manager**: Added interactive tag chips builder in `class-gnn-logos-cpt.php` with Enter key and bulk comma/newline paste support (`gnn_cert_codes[]`).
  - **Admin Table Columns**: Upgraded `gnn_code` column in post list to display clean flex-wrap badges.
  - **Backward Compatibility**: Automatically detects and splits legacy comma-separated or newline-separated strings in `_gnn_cert_code`.
  - **Version Bump**: Bumped plugin version and asset tags to `1.0.1`.
- **2026-09-30**: Initialized Git repository tracking.
- **2026-09-30**: Bootstrapped Sentinel Memory Bank governance structure (`.memory-bank/`, `.specs/`, `.agents/`, `.tasks/`).
- **2026-09-30**: Recorded ADR-0001 (Tech Stack), ADR-0002 (Lightweight Frontend Engine), ADR-0003 (Certificate Card Layout), and ADR-0004 (GNN Ecosystem Standards & Auto-Updater).
- **2026-10-01**: **TASK-001**: Created `gnn-logos/gnn-logos.php` with singleton architecture, rewrite flush hooks, and action links (`Donate`, `Shortcode Oluşturucu`, `Check Updates`).
- **2026-10-01**: **TASK-002**: Implemented native GitHub Releases updater in `gnn-logos/inc/updater.php` with transient caching, host SSRF validation, and manual check nonces.
- **2026-10-01**: **TASK-003**: Implemented CPT `gnn_logo`, Taxonomy `gnn_logo_group`, custom meta boxes (`_gnn_logo_id`, `_gnn_cert_code`, `_gnn_description`, `_gnn_link_url`, `_gnn_aspect_ratio`), and custom admin table columns in `gnn-logos/includes/class-gnn-logos-cpt.php`.
- **2026-10-01**: **TASK-004**: Developed zero-dependency frontend assets (`gnn-logos/assets/css/gnn-logos-frontend.css` [8.7KB] and `gnn-logos/assets/js/gnn-logos-frontend.js` [4.6KB]) supporting CSS Scroll-Snap, GPU marquee transforms, certificate cards, and grayscale hover transitions.
- **2026-10-01**: **TASK-005**: Developed shortcode engine `[gnn_logos]` with conditional asset enqueueing, responsive `srcset`, `loading="lazy"`, and contextual escaping in `gnn-logos/includes/class-gnn-logos-shortcode.php`.
- **2026-10-01**: **TASK-006**: Implemented admin menu at exact position `'79.109'`, interactive Shortcode Generator UI with presets, and media uploader in `gnn-logos/includes/class-gnn-logos-admin.php`, `gnn-logos/assets/css/gnn-logos-admin.css`, and `gnn-logos/assets/js/gnn-logos-admin.js`.
- **2026-10-01**: **TASK-007**: Created `gnn-logos/readme.txt`, `gnn-logos/uninstall.php`, `README.md`, and validated syntax with `php -l` on all PHP files (0 errors).

## Validation Status
- Static analysis: `php -l` passed on all 6 PHP files with 0 syntax errors.
- Security checks: Verified `ABSPATH` guards, nonces (`gnn_logos_save_meta`, `gnn_logos_manual_update`), capability checks (`edit_post`, `manage_options`, `update_plugins`), and contextual escaping (`esc_html`, `esc_attr`, `esc_url`).
- Performance check: Frontend CSS (8.7KB) and JS (4.6KB) strictly inside the <10KB / <5KB payload budget. Zero external CDN/npm dependencies.
