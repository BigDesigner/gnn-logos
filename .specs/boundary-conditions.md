# Boundary Conditions & Security Invariants

- **Project**: GNN Logos
- **Status**: Enforced
- **Confidence**: Verified

## 1. Security Invariants (WordPress Ecosystem)

### 1.1 Direct File Access Guard
Every single PHP file within the plugin MUST begin with a direct file execution abort check:
```php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
```

### 1.2 Capability & Authorization Verification (BOLA / IDOR Defense)
Any administrative action, meta box save, or settings update MUST check user capabilities:
```php
if (!current_user_can('edit_post', $post_id)) {
    return;
}
```
For general admin pages or generator settings:
```php
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'gnn-logos'));
}
```

### 1.3 CSRF & Nonce Protection
All form submissions and meta box updates MUST use cryptographic nonces:
- Creation: `wp_nonce_field('gnn_logos_meta_box_nonce_action', 'gnn_logos_meta_nonce');`
- Verification: `if (!isset($_POST['gnn_logos_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gnn_logos_meta_nonce'])), 'gnn_logos_meta_box_nonce_action')) { return; }`

### 1.4 Strict Input Sanitization
No unsanitized `$_POST` or `$_GET` variable may ever touch the database:
- Text inputs (titles, subtitles, certificate codes): `sanitize_text_field(wp_unslash($input))`
- URLs (company links, certificate PDF links): `esc_url_raw(wp_unslash($input))`
- Integers / IDs (attachment ID, order, columns): `absint($input)`
- Booleans / Choice enums: strict in_array whitelist checks.

### 1.5 Strict Output Escaping (XSS Prevention)
No variable may be output into HTML attributes or tags without context-specific escaping:
- Inside HTML text: `esc_html($text)`
- Inside HTML attributes: `esc_attr($attr)`
- Inside href/src URLs: `esc_url($url)`
- Internationalized strings: `esc_html__('Text', 'gnn-logos')`

## 2. Architectural & Performance Boundaries

### 2.1 Zero External JavaScript Dependency Invariant
- **Rule**: Absolutely NO inclusion of jQuery slider plugins (Slick, Owl Carousel, bxSlider) or heavy bundles (Swiper 150KB+).
- **Enforcement**: Frontend carousel must use native CSS Scroll-Snap + lightweight Vanilla JS (< 3KB). Marquee/ticker must use 100% pure CSS keyframe transforms with GPU acceleration (`transform: translate3d`).

### 2.2 Asset Size Budgets
- Frontend CSS payload: Must remain strictly `< 10 KB`.
- Frontend JS payload: Must remain strictly `< 5 KB`.
- Admin assets: Must only enqueue on `gnn_logo` CPT editing screens or the plugin settings/generator screen.

### 2.3 Responsive Image Standards
All logo images rendered on the frontend MUST:
- Use WordPress native `wp_get_attachment_image()` to automatically output responsive `srcset` and `sizes`.
- Include `loading="lazy"` and `decoding="async"` attributes to preserve Core Web Vitals.
- Apply `object-fit: contain` within an aspect-ratio container to prevent distortion.

## 3. End-to-End Integration Contract (Anti-Illusion Rule)
Every feature must verify all 5 links of the feature chain:
1. DB Persistence (`wp_posts`, `wp_postmeta`)
2. Backend Handler (CPT save hook, shortcode query builder)
3. Frontend Renderer (HTML DOM generation with proper data attributes)
4. UI Trigger Element (Nav buttons, touch drag, auto-play timer)
5. UI Feedback (Visual slide transition, pause-on-hover state)
