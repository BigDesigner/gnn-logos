# Data Dictionary & Persistence Specification

<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php#L20-L80 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php#L300-L355 -->
<!-- Verified from: gnn-logos/inc/updater.php#L35-L45 -->
<!-- Verified from: gnn-logos/uninstall.php#L1-L44 -->

## 1. Entity Relationship Overview

GNN Logos utilizes WordPress core relational tables to persist entities, taxonomies, and custom post metadata without creating proprietary SQL tables:

```mermaid
erDiagram
    WP_POSTS {
        bigint ID PK
        string post_title
        string post_name
        string post_type "gnn_logo"
        string post_status "publish"
        int menu_order
    }

    WP_POSTMETA {
        bigint meta_id PK
        bigint post_id FK
        string meta_key
        longtext meta_value
    }

    WP_TERMS {
        bigint term_id PK
        string name
        string slug
    }

    WP_TERM_TAXONOMY {
        bigint term_taxonomy_id PK
        bigint term_id FK
        string taxonomy "gnn_logo_group"
        longtext description
        bigint count
    }

    WP_TERM_RELATIONSHIPS {
        bigint object_id PK,FK "Maps to wp_posts.ID"
        bigint term_taxonomy_id PK,FK
    }

    WP_OPTIONS {
        bigint option_id PK
        string option_name
        longtext option_value
        string autoload
    }

    WP_POSTS ||--o{ WP_POSTMETA : "has metadata"
    WP_POSTS ||--o{ WP_TERM_RELATIONSHIPS : "classified by"
    WP_TERM_TAXONOMY ||--o{ WP_TERM_RELATIONSHIPS : "groups"
    WP_TERMS ||--|| WP_TERM_TAXONOMY : "defines"
```

---

## 2. Core Entities Specification

### 2.1 Custom Post Type: `gnn_logo`
- **Registration File:** `gnn-logos/includes/class-gnn-logos-cpt.php#L20-L50`
- **Storage Table:** `wp_posts`
- **Filtered Columns:**
  - `post_type`: strictly `'gnn_logo'`
  - `post_status`: `'publish'`, `'draft'`, `'trash'`
  - `post_title`: Used as Logo Name / Company Title and fallback image `alt` attribute.
  - `menu_order`: Used as primary sorting field when `orderby="menu_order"` (default).

### 2.2 Taxonomy: `gnn_logo_group`
- **Registration File:** `gnn-logos/includes/class-gnn-logos-cpt.php#L55-L85`
- **Storage Tables:** `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`
- **Type:** Hierarchical (category-like)
- **Purpose:** Groups items into logical showcases (e.g., `sertifikalar`, `referanslar`, `cozum-ortaklari`).

---

## 3. Secondary & Postmeta Metadata Keys

<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php#L300-L355 -->

All metadata attributes are attached to `wp_postmeta` via standard WordPress `get_post_meta()` and `update_post_meta()` APIs:

| Meta Key (`meta_key`) | Data Type | Serialization | Sanitization Function | Purpose & Default |
|---|---|---|---|---|
| `_gnn_logo_id` | Integer (`bigint`) | Raw Scalar | `absint($_POST['gnn_logo_id'])` | WordPress attachment ID pointing to `wp_posts` (`post_type = 'attachment'`). Required for rendering logo graphic. |
| `_gnn_cert_codes` | Array of Strings | PHP Serialized Array | `sanitize_text_field(wp_unslash($raw_code))` | Multi-standard tags array (e.g. `['TS EN 12201-2', 'TS EN ISO 1452-2']`). Rendered as individual badge pills. |
| `_gnn_cert_code` | String (`varchar`) | Comma-separated string | `sanitize_textarea_field(wp_unslash($_POST['gnn_cert_code']))` | Legacy single-string standard code. Kept synchronized with `_gnn_cert_codes` for backward compatibility. |
| `_gnn_description` | String (`text`) | Raw String | `sanitize_text_field(wp_unslash($_POST['gnn_description']))` | Subtitle, company description, or accreditation body text displayed underneath title. |
| `_gnn_link_url` | String (`url`) | Raw String | `esc_url_raw(wp_unslash($_POST['gnn_link_url']))` | Target destination hyperlink when user clicks on logo item. |
| `_gnn_link_target` | String (`enum`) | Raw String | `in_array(..., ['_self', '_blank'])` | Hyperlink browser window target (`_blank` for external tab, `_self` for same window). Default: `'_blank'`. |
| `_gnn_aspect_ratio` | String (`enum`) | Raw String | `in_array(..., ['auto', '16:9', '4:3', '1:1', '3:2', '2:1'])` | Item-specific aspect ratio override. Default: `'auto'`. |

---

## 4. Caching & Transient Dictionary

<!-- Verified from: gnn-logos/inc/updater.php#L35-L45 -->
<!-- Verified from: gnn-logos/inc/updater.php#L156 -->
<!-- Verified from: gnn-logos/uninstall.php#L37-L43 -->

The plugin caches external API communication and core update statuses using the WordPress Transients API (stored in `wp_options`):

| Transient Key | Storage Location | Time-to-Live (TTL) | Eviction Trigger | Payload Structure |
|---|---|---|---|---|
| `gnn_logos_github_update_check` | `wp_options` (`_transient_...`) | `43200` seconds (12 hours) | Visiting `update-core.php`, triggering manual check, or running `uninstall.php` | `(object) ['version' => '1.2.0', 'download_url' => '...', 'changelog' => '...', 'published_at' => '...']` |
| `_site_transient_update_plugins` | `wp_options` (WordPress Core) | Managed by WP Core (12h) | Post-install upgrade, core update check, or manual check | Injects standard plugin update object into `$transient->response['gnn-logos/gnn-logos.php']` |

---

## 5. Physical Storage Assets

<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php#L190-L240 -->

| Asset Type | Storage Location | Access Protocol | Management Interface |
|---|---|---|---|
| **Logo Images** | `/wp-content/uploads/YYYY/MM/` | Native WordPress Attachment URLs | WordPress Media Library modal (`wp.media`) via "Gözat / Logo Seç" button |
| **Plugin Package** | `/wp-content/plugins/gnn-logos/` | Local Filesystem | WordPress Plugins Dashboard / Git |
| **Release Archive** | `gnn-logos-1.2.0.zip` | GitHub Releases CDN | GitHub Actions Release Pipeline (`.github/workflows/release.yml`) |
