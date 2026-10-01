# API & Interface Reference Specification

<!-- Verified from: gnn-logos/gnn-logos.php#L120-L130 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-admin.php#L35-L115 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php#L60-L125 -->
<!-- Verified from: gnn-logos/inc/updater.php#L280-L297 -->

## 1. Authentication & Authorization Guardrails

| Interface Type | Authorization Mechanism | Capability Required | Security Controls Applied |
|---|---|---|---|
| **CPT Meta Box Save** | Cryptographic Nonce & Capabilities | `edit_post` (`$post_id`) | `wp_verify_nonce($_POST['gnn_logos_meta_nonce'], 'gnn_logos_meta_box_nonce_action')` |
| **Admin Settings & Generator** | WordPress User Session | `manage_options` | Enforced at `add_menu_page()` / `add_submenu_page()` |
| **Manual Update Check Handler** | Nonce & Core Update Capability | `update_plugins` | `wp_verify_nonce($_GET['_wpnonce'], 'gnn_logos_manual_update')` & `current_user_can('update_plugins')` |
| **Frontend Shortcode** | Public Access | None (Public) | Read-only SQL via `WP_Query`, contextual escaping on all output tags |

---

## 2. Public Shortcode Interface (`[gnn_logos]`)

The primary frontend interface is the `[gnn_logos]` shortcode registered via `add_shortcode('gnn_logos', ...)`.

### 2.1 Parameters Specification Table

<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php#L70-L140 -->

| Parameter | Type | Default | Accepted Values | Description & Validation Rules |
|---|---|---|---|---|
| `group` | string | `""` | Any valid taxonomy slug | Filters by `gnn_logo_group` slug. Sanitized via `sanitize_title()`. If empty, queries all groups. |
| `layout` | string | `'carousel'` | `'carousel'`, `'marquee'`, `'grid'` | Presentation mode. Validated via `in_array()`. |
| `style` | string | `'minimal'` | `'minimal'`, `'card'`, `'bordered'` | Card appearance. `'card'` applies borders, rounded corners, and shadow. |
| `aspect_ratio` | string | `'auto'` | `'16/9'`, `'4/3'`, `'1/1'`, `'3/2'`, `'2/1'`, `'auto'` | Forces container aspect ratio on image boxes. Normalizes `:` to `/`. |
| `columns` | integer | `4` | `1` to `10` | Grid and carousel desktop visible column count. Sanitized with `absint()`. |
| `columns_tablet` | integer | `3` | `1` to `8` | Tablet viewport visible column count. Sanitized with `absint()`. |
| `columns_mobile` | integer | `2` | `1` to `4` | Mobile viewport visible column count. Sanitized with `absint()`. |
| `gap` | string | `'20px'` | Any CSS length unit (px, rem) | Space between items. Sanitized via `sanitize_text_field()`. Appends `px` if numeric. |
| `show_code` | boolean | `true` | `'true'`, `'false'` | Controls visibility of certification standard badges. Validated via `filter_var(..., FILTER_VALIDATE_BOOLEAN)`. |
| `badges_layout` | string | `'wrap'` | `'wrap'`, `'stacked'` | Badge distribution. `'wrap'` allows side-by-side flex wrap; `'stacked'` displays single-column vertical list. |
| `badges_align` | string | `'center'` | `'center'`, `'left'`, `'right'` | Horizontal badge alignment within card. |
| `badges_valign` | string | `'bottom'` | `'bottom'`, `'top'`, `'center'` | Vertical badge alignment: `'bottom'` (pinned to bottom), `'top'` (pinned directly beneath logo), `'center'` (vertically centered). |
| `show_title` | boolean | `false` | `'true'`, `'false'` | Controls display of logo post title. |
| `show_desc` | boolean | `false` | `'true'`, `'false'` | Controls display of post subtitle/description. |
| `grayscale` | boolean | `false` | `'true'`, `'false'` | If true, applies 100% grayscale filter until hovered (`:hover` returns to full color). |
| `autoplay` | boolean | `true` | `'true'`, `'false'` | Enables automatic slider slide advancement in carousel layout. |
| `autoplay_speed` | integer | `3000` | Milliseconds | Slide interval timer in milliseconds (e.g. `3000` = 3s). Sanitized with `absint()`. |
| `speed` | string | `'25s'` | CSS time string | Complete loop duration for marquee ticker animation (e.g. `20s`, `30s`). |
| `direction` | string | `'left'` | `'left'`, `'right'` | Marquee scrolling direction. |
| `arrows` | boolean | `true` | `'true'`, `'false'` | Enables navigation arrow buttons on carousel layout. |
| `dots` | boolean | `false` | `'true'`, `'false'` | Enables pagination dot controls on carousel layout. |
| `pause_on_hover`| boolean | `true` | `'true'`, `'false'` | Pauses marquee animation or carousel autoplay on mouse hover. |
| `limit` | integer | `-1` | Number of items | Maximum items to query. `-1` denotes no limit. Sanitized with `intval()`. |
| `orderby` | string | `'menu_order'` | `'none'`, `'ID'`, `'author'`, `'title'`, `'name'`, `'type'`, `'date'`, `'modified'`, `'parent'`, `'rand'`, `'menu_order'` | Post ordering criteria. Validated against strict allowlist. |
| `order` | string | `'ASC'` | `'ASC'`, `'DESC'` | Sort direction. Validated via `in_array()`. |

---

### 2.2 Shortcode Usage Examples

<details>
<summary><b>Payload Specification: Shortcode Usage Scenarios</b></summary>

#### Example 1: Quality Certificates Grid Showcase
```html
[gnn_logos group="kalite-belgeleri" layout="grid" style="card" aspect_ratio="4/3" columns="4" show_code="true" badges_layout="wrap" badges_align="center" badges_valign="bottom"]
```

#### Example 2: Continuous Infinite Logo Marquee (Left Direction)
```html
[gnn_logos group="referanslar" layout="marquee" speed="20s" direction="left" grayscale="true" pause_on_hover="true"]
```

#### Example 3: Solution Partners Touch Carousel
```html
[gnn_logos group="cozum-ortaklari" layout="carousel" columns="5" aspect_ratio="16/9" autoplay="true" autoplay_speed="3000" arrows="true" dots="true"]
```
</details>

---

## 3. Administrative URI & Hook Reference

<!-- Verified from: gnn-logos/includes/class-gnn-logos-admin.php#L35-L95 -->
<!-- Verified from: gnn-logos/inc/updater.php#L55-L65 -->

| Route / Hook Identifier | HTTP Verb | Capability Guard | Action / Purpose | File Citation |
|---|---|---|---|---|
| `admin.php?page=gnn-logos` | `GET` | `manage_options` | Top-level menu entry (`'79.109'`); automatically redirects to CPT post list table | `includes/class-gnn-logos-admin.php#L38` |
| `edit.php?post_type=gnn_logo` | `GET` | `edit_posts` | Core CPT list table displaying thumbnail, standard code badges, group, and order | `includes/class-gnn-logos-cpt.php#L360` |
| `post-new.php?post_type=gnn_logo` | `GET` / `POST` | `edit_posts` | Create new logo/certificate item; renders media selector and tag chips manager | `includes/class-gnn-logos-cpt.php#L90` |
| `edit-tags.php?taxonomy=gnn_logo_group` | `GET` / `POST` | `manage_categories` | Taxonomy management screen for logo categories (e.g., Partners, Certificates) | `includes/class-gnn-logos-cpt.php#L65` |
| `admin.php?page=gnn-logos-generator` | `GET` | `manage_options` | Interactive Shortcode Generator wizard with preset buttons and live preview | `includes/class-gnn-logos-admin.php#L70` |
| `plugins.php?gnn_logos_check_update=1` | `GET` | `update_plugins` | Manual update trigger with `_wpnonce`; flushes transient cache and redirects to core updater | `inc/updater.php#L280` |
| `site_transient_update_plugins` | Filter Hook | N/A (Internal) | Injects new release data or purges stale notices during core update checks | `inc/updater.php#L58` |
| `upgrader_post_install` | Filter Hook | `update_plugins` | Normalizes directory name to `gnn-logos` following GitHub zip unzipping | `inc/updater.php#L260` |
