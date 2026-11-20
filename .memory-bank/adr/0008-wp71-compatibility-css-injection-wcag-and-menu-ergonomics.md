# ADR 0008: WP 7.1 Compatibility, CSS Injection Defense, WCAG Accessibility, and Menu Ergonomics

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-11-20
- **Supersedes**: None
- **Superseded By**: None

## Context

Following v1.2.0, comprehensive ecosystem audits and live production testing surfaced several operational, security, and UX issues across v1.2.1–v1.2.4:
1. **WP 6.7+ i18n Timing & WP 7.1 Metadata:** `load_plugin_textdomain()` shared priority `10` on `init` with CPT registration, risking `_load_textdomain_just_in_time` notices. Plugin headers lacked `Requires at least` / `Requires PHP`, `readme.txt` listed `Tested up to: 6.7`, and `languages/gnn-logos.pot` + root `VERSION` were absent.
2. **Updater Edge Cases:** `get_remote_release()` cached HTTP failures as `false`, which WordPress `get_transient()` treats identically to a cache miss, bypassing rate-limit protection. `check_for_update()` lacked `requires`/`tested`/`requires_php`, `get_plugin_data()` lacked `$translate = false`, and SSRF validation lacked an explicit `https` scheme check.
3. **Shortcode CSS Injection Seam:** `gap`, `aspect_ratio`, and `speed` were sanitized with `sanitize_text_field()` only, allowing arbitrary CSS declarations inside inline `style="--gnn-..."` attributes.
4. **Marquee Oversized Logos in Narrow/Footer Containers:** `.gnn-marquee-item` inside `width: max-content` lacked an explicit `width` constraint, causing high-resolution logos to render at their full intrinsic pixel size (e.g., 2000×1000px).
5. **WCAG Accessibility Gaps:** Missing `tabindex="-1"` on `aria-hidden="true"` marquee duplicates, missing `role="tab"` / `aria-selected` on dots, missing `role="region"` / `aria-roledescription="carousel"`, missing `:focus-visible`, `prefers-reduced-motion`, and drag-vs-click protection.
6. **Admin Sidebar Menu Duplication:** `add_menu_page` with slug `'gnn-logos'` generated a duplicate empty submenu item above "Tüm Logolar & Sertifikalar", and "Güncellemeleri Kontrol Et" was redundant alongside GNN Dashboard.

## Decision

1. **WP 7.1 & i18n Alignment (v1.2.1 / v1.2.3):** Moved `load_plugin_textdomain()` to `init` priority `1`, updated `Tested up to: 7.1`, added `Requires at least: 5.8` and `Requires PHP: 8.0` to `gnn-logos.php`, generated complete `languages/gnn-logos.pot` (120 strings), and created root `VERSION`.
2. **Updater Hardening (v1.2.1 / v1.2.3):** Used `'error'` sentinel in `set_transient()`, enforced `'https' === $parsed['scheme']`, added `requires`/`tested`/`requires_php` to both `check_for_update()` and `plugin_info()`, passed `false, false` to `get_plugin_data()`, and added `is_object($wp_filesystem)` check in `after_install()`.
3. **Shortcode CSS Regex Allowlists (v1.2.3):** Enforced strict regex validation on `gap`, `aspect_ratio`, and `speed`.
4. **Marquee Size & Flex Containment (v1.2.2):** Constrained `.gnn-marquee-item` to `width: clamp(140px, 16vw, 200px)`, added `max-height: 90px` for `auto` aspect ratio, and added `max-width: 100%; min-width: 0;` to `.gnn-logos-wrapper` and `.gnn-logos-layout-marquee`.
5. **WCAG & Drag UX (v1.2.1 / v1.2.3):** Added full ARIA carousel/tablist semantics, `tabindex="-1"` on duplicate marquee links, keyboard `ArrowLeft`/`ArrowRight`, `:focus-visible`, `prefers-reduced-motion`, and `hasDragged` (>5px) click suppression.
6. **Direct CPT Top-Level Menu (v1.2.4):** Registered `'79.109'` top-level menu directly with `'edit.php?post_type=gnn_logo'` and removed the redundant sidebar update submenu (preserving `Check Updates` on `plugins.php`).

## Consequences

- Zero `_load_textdomain_just_in_time` warnings on WP 6.7–7.1.5+.
- Full immunity against CSS variable injection via shortcode attributes and GitHub API retry storms.
- Compact, responsive marquee rendering in footer and narrow sidebar/column containers.
- Clean 4-item admin submenu (`Tüm Logolar & Sertifikalar`, `Yeni Ekle`, `Logo Grupları`, `Shortcode Oluşturucu`) with zero empty redirect screens.

## Evidence

- `gnn-logos/gnn-logos.php#L6-L114`
- `gnn-logos/inc/updater.php#L81-L285`
- `gnn-logos/includes/class-gnn-logos-shortcode.php#L117-L331`
- `gnn-logos/includes/class-gnn-logos-admin.php#L31-L82`
- `gnn-logos/assets/css/gnn-logos-frontend.css#L12-L505`
- `gnn-logos/assets/js/gnn-logos-frontend.js#L45-L120`
