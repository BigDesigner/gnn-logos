# ADR 0010: AAA v2.0.0 Frontend Engine, Theme Token System, Lightbox, Admin UX & Native Gutenberg Block

- **Status**: Accepted
- **Date**: 2026-11-25
- **Supersedes**: [ADR-0002: Lightweight Frontend Engine](file://.memory-bank/adr/0002-lightweight-frontend-engine.md)
- **Superseded By**: None

## Context
Real-world production usage of GNN Logos (`v1.0.x`–`v1.2.6`) exposed structural weaknesses across rendering, theming, administration, and modern WordPress block editing:
1. **Marquee Loop Seam & Short-List Gaps**: Placing `2N` items in a single flex container and translating `-50%` introduced a `0.5 * gap` jump at the loop seam and left blank whitespace on ultrawide/4K displays when only 1–4 logos were present.
2. **Carousel Navigation & Autoplay Defects**: `goNext()` wrapped early (`scrollLeft + step >= max - 5`), permanently skipping the final slide; desktop viewports rendered unreachable overflow dots; arrows/dots remained visible even when all logos fit; drag-to-scroll fought `scroll-snap-type`; autoplay kept ticking in background tabs and off-screen sections.
3. **Dark Theme Incompatibility**: Hardcoded light-mode hex values rendered titles, arrows, dots, and dark logos unreadable on dark site themes or footers.
4. **Missing Certificate Inspection UX**: Users displaying quality certificates (TS EN, ISO) had no built-in way to zoom and inspect documents/standards without external modal plugins.
5. **Admin Ergonomics**: Reordering logos required editing each post's `menu_order` manually; uploading 20+ logos required 20 separate post creations; the CPT table lacked a `gnn_logo_group` filter; the Shortcode Generator had no live visual preview and omitted `show_code="false"` when unchecked.
6. **Gutenberg / FSE Integration**: Modern Block Themes and Site Editor headers/footers lacked a native Gutenberg block.

## Decision
Re-architect GNN Logos into `v2.0.0` while strictly preserving the zero-external-frontend-dependency invariant (no Swiper, Slick, jQuery sliders, npm bundles, or CDNs):
1. **Multi-Group Seamless Marquee Engine**: Render items inside `.gnn-marquee-group` siblings with `padding-right: var(--gnn-gap)` animating `translate3d(-100%, 0, 0)` (zero seam drift) paired with a `ResizeObserver` viewport-fill loop in `gnn-logos-frontend.js` that clones groups dynamically until ultrawide screens are filled. Set `loading="eager"` on marquee images to eliminate blank-clone flash.
2. **Bug-Free Scroll-Snap Carousel**: Fix `goNext()` off-by-one check (`scrollLeft >= max - 5`), dynamically hide unreachable dots and apply `.gnn-carousel-static` when `maxScroll <= 4`, suspend scroll-snap via `.is-dragging` during `clientX` pointer drags, reset autoplay interval on manual navigation, and pause autoplay via `IntersectionObserver`, `document.visibilitychange`, `focusin`, and `touchstart`.
3. **CSS Custom Property Theme & Filter System**: Introduce `theme="light|dark|auto"` (`prefers-color-scheme: dark`), `style="glass"` (Glassmorphism), `logo_filter="none|grayscale|monochrome-white|monochrome-dark"`, `logo_height`, `item_width`, and `fade_edges`. All CSS values pass strict regex allowlists.
4. **Accessible Zero-Dependency Lightbox**: Optional `lightbox="true"` renders semantic `<button data-gnn-lightbox="1">` triggers opened by a single delegated document click handler in `gnn-logos-frontend.js` using pure DOM node creation (zero `innerHTML`), `Escape`/backdrop close, and focus restoration.
5. **Admin UX & Authenticated AJAX**:
   - Drag-and-drop `menu_order` sorting (`jquery-ui-sortable` + `wp_ajax_gnn_logos_reorder`, nonce `gnn_logos_admin_nonce`, capability `edit_others_posts`/`manage_options`).
   - Bulk Logo Uploader (`wp.media({ multiple: true })` + `wp_ajax_gnn_logos_bulk_create`, capability `publish_posts`).
   - `gnn_logo_group` filter dropdown + sortable `Sıra` (`menu_order`) column with default `menu_order ASC` admin query order.
   - Live Visual Preview Shortcode Generator (`wp_ajax_gnn_logos_preview`, capability `manage_options`) with Light/Dark/Checker canvas switcher, 5 presets, layout-aware panels, and fixed `show_code="false"` emission.
6. **Zero-Build Native Gutenberg Block (`gnn-logos/showcase`)**:
   - `gnn-logos/blocks/showcase/block.json` (`apiVersion: 3`, 30 typed attributes, `align: ["wide", "full"]`), vanilla `editor.js` (`InspectorControls` + `ServerSideRender`), and `GNN_Logos_Block` (`includes/class-gnn-logos-block.php`) delegating directly to `GNN_Logos_Shortcode::render_shortcode()`.
   - Register frontend assets on `init` priority `5` so `block.json`'s `"style": "gnn-logos-frontend"` loads reliably in Block Editor and FSE iframes.
7. **Revised Asset Budget**: Keep zero external runtime dependencies while updating raw unminified budgets to `< 28 KB` CSS (~23.6 KB raw, ~5 KB gzip) and `< 20 KB` JS (~17.7 KB raw, ~4.5 KB gzip) to accommodate theme tokens, glassmorphism, marquee fill, smart carousel, and lightbox in one self-contained bundle.

## Consequences
- Eliminates visual seam jumps, skipped slides, dark-theme contrast breakage, and admin workflow bottlenecks.
- Enables seamless native editing in Gutenberg and FSE Site Editor without introducing a Node build pipeline.
- Increases uncompressed frontend asset sizes from ~13.3 KB total to ~41.3 KB total (~9.5 KB gzipped combined), still orders of magnitude smaller than third-party slider + lightbox stacks and loaded conditionally only on pages containing the shortcode or block.
