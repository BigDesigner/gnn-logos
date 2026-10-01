# System Architecture & Topology Specification

<!-- Verified from: gnn-logos/gnn-logos.php#L1-L155 -->
<!-- Verified from: .specs/boundary-conditions.md#L1-L85 -->

## 1. Directory Topology

```text
gnn-logos/
├── gnn-logos.php                     # [Core] Main entry point, singleton bootstrapper, action links
├── readme.txt                         # [Metadata] WordPress.org repository specifications
├── uninstall.php                      # [Lifecycle] Safe uninstall cleanup for posts, terms, and transients
├── LICENSE                            # [License] GNU General Public License v2
├── inc/
│   └── updater.php                    # [Updater] GitHub Releases API client with transient synchronization
├── includes/
│   ├── class-gnn-logos-cpt.php        # [Model/Admin] CPT 'gnn_logo', Taxonomy 'gnn_logo_group', Meta Boxes
│   ├── class-gnn-logos-shortcode.php  # [Controller/View] Shortcode [gnn_logos] handler, query builder, renderer
│   └── class-gnn-logos-admin.php      # [Admin] Menu '79.109', submenus, interactive Shortcode Generator UI
└── assets/
    ├── css/
    │   ├── gnn-logos-frontend.css     # [View] CSS Grid, CSS Scroll-Snap, GPU marquee keyframes (<10 KB)
    │   └── gnn-logos-admin.css        # [Admin View] Meta box layout, tag chips styling, button centering
    └── js/
        ├── gnn-logos-frontend.js      # [View] Micro Vanilla JS controller for carousel touch/autoplay (<5 KB)
        └── gnn-logos-admin.js         # [Admin] WP Media Library uploader, tag chip manager, shortcode wizard
```

---

## 2. Component Architecture Diagram

The diagram below maps request ingress from browser visits, admin form submissions, shortcode evaluation, database queries, and external GitHub update egress:

```mermaid
flowchart TD
    subgraph Browser["Client Browser"]
        UserPage["Public Webpage (Shortcode Execution)"]
        AdminPage["WP Admin Dashboard (Menu '79.109')"]
    end

    subgraph WordPressCore["WordPress Core Runtime"]
        HookLoader["plugins_loaded / init / admin_init Hooks"]
        WpQuery["WP_Query & DB Engine"]
        MediaLib["wp.media (Media Library Modal)"]
        CoreTransient["Transient API (_site_transient_update_plugins)"]
    end

    subgraph PluginArchitecture["GNN Logos Plugin Layer"]
        Singleton["GNN_Logos (Singleton Coordinator)"]
        CPT["GNN_Logos_CPT (CPT & Meta Box Engine)"]
        Shortcode["GNN_Logos_Shortcode (Shortcode & HTML Engine)"]
        Admin["GNN_Logos_Admin (Admin & Shortcode Wizard)"]
        Updater["GNN_Logos_Updater (GitHub Releases Engine)"]
    end

    subgraph Database["WordPress Database (MySQL/MariaDB)"]
        TablePosts["wp_posts (post_type = 'gnn_logo')"]
        TablePostmeta["wp_postmeta (_gnn_logo_id, _gnn_cert_codes, etc.)"]
        TableTerms["wp_terms & wp_term_taxonomy ('gnn_logo_group')"]
        TableOptions["wp_options (Transients: update_check, update_plugins)"]
    end

    subgraph External["External Network Services"]
        GitHubAPI["GitHub Releases API (api.github.com)"]
        GitHubCDN["GitHub CDN Zipball (codeload.github.com)"]
    end

    %% Ingress Connections
    UserPage -->|Renders [gnn_logos]| Shortcode
    AdminPage -->|CPT Form Submission| CPT
    AdminPage -->|Shortcode Builder| Admin
    AdminPage -->|Selects Logo Image| MediaLib

    %% Core Dispatching
    HookLoader --> Singleton
    Singleton --> CPT
    Singleton --> Shortcode
    Singleton --> Admin
    Singleton --> Updater

    %% Data Interactions
    CPT -->|Saves Post & Meta| TablePosts
    CPT -->|Saves Meta Box Values| TablePostmeta
    CPT -->|Assigns Taxonomies| TableTerms
    Shortcode -->|Executes Group & Order Query| WpQuery
    WpQuery --> TablePosts
    WpQuery --> TablePostmeta
    WpQuery --> TableTerms

    %% Admin & Media Interactions
    MediaLib -->|Passes attachment_id| Admin
    Admin -->|Renders Settings & Previews| AdminPage

    %% Updater Flow
    CoreTransient -->|Filter: site_transient_update_plugins| Updater
    Updater -->|HTTP GET Request (12h TTL)| GitHubAPI
    Updater -->|Fetches Release Zipball| GitHubCDN
    Updater -->|Persists Cache State| TableOptions
```

---

## 3. Component Registry

<!-- Verified from: gnn-logos/gnn-logos.php#L35-L95 -->
<!-- Verified from: gnn-logos/includes/ -->

| Component | File Path | Primary Responsibility | Inbound Dependencies | Outbound Dependencies |
|---|---|---|---|---|
| `GNN_Logos` | `gnn-logos/gnn-logos.php` | Singleton root coordinator; binds activation, deactivation, and textdomain hooks | WordPress Core (`plugins_loaded`) | `GNN_Logos_CPT`, `GNN_Logos_Shortcode`, `GNN_Logos_Admin`, `GNN_Logos_Updater` |
| `GNN_Logos_CPT` | `gnn-logos/includes/class-gnn-logos-cpt.php` | Registers `gnn_logo` CPT and `gnn_logo_group` taxonomy; handles meta box save routines and admin table columns | `GNN_Logos`, Admin UI (`save_post`) | WordPress DB (`wp_posts`, `wp_postmeta`, `wp_terms`) |
| `GNN_Logos_Shortcode` | `gnn-logos/includes/class-gnn-logos-shortcode.php` | Parses `[gnn_logos]` shortcode attributes; validates `orderby` allowlists; constructs HTML output; enqueues frontend assets conditionally | WordPress Core (`init`, `wp_enqueue_scripts`) | `wp_get_attachment_image`, `gnn-logos-frontend.css`, `gnn-logos-frontend.js` |
| `GNN_Logos_Admin` | `gnn-logos/includes/class-gnn-logos-admin.php` | Registers top-level admin menu at position `'79.109'`; renders Interactive Shortcode Generator UI with 4 preset configurations | `GNN_Logos`, Admin screen hooks | `gnn-logos-admin.css`, `gnn-logos-admin.js`, Media Library (`wp.media`) |
| `GNN_Logos_Updater` | `gnn-logos/inc/updater.php` | Connects to GitHub Releases API; checks for newer versions; handles transient synchronization and post-upgrade slug directory normalization | WordPress Core (`site_transient_update_plugins`, `plugins_api`) | GitHub API (`api.github.com`), WordPress Transients API |

---

## 4. Data Flow & Execution Lifecycle

### 4.1 Frontend Shortcode Rendering Pipeline
1. **Detection:** When a page or post is rendered, WordPress encounters `[gnn_logos]`.
2. **Conditional Asset Enqueueing:** `GNN_Logos_Shortcode::render_shortcode()` fires, registering and enqueueing `gnn-logos-frontend.css` (8.7 KB) and `gnn-logos-frontend.js` (4.6 KB).
3. **Attribute Sanitization:** Attributes are merged against defaults via `shortcode_atts()`, and validated through strict allowlists:
   - `layout`: `in_array($layout, ['grid', 'carousel', 'marquee'])`
   - `style`: `in_array($style, ['minimal', 'card', 'bordered'])`
   - `orderby`: `in_array($orderby, ['menu_order', 'date', 'title', 'rand', 'ID', 'author', 'name', 'modified', 'parent', 'none'])`
   - `badges_valign`: `in_array($badges_valign, ['top', 'center', 'bottom'])`
4. **Query Execution:** A scoped `WP_Query` executes against `post_type = 'gnn_logo'`, filtering by `tax_query` if the `group` attribute is provided.
5. **DOM Generation:** Iterates through results, invoking `render_item_html()`:
   - Generates responsive `<picture>` / `<img>` elements via `wp_get_attachment_image()` with native `srcset`, `sizes`, `loading="lazy"`, and `decoding="async"`.
   - Iterates through multi-standard array `_gnn_cert_codes` (with legacy fallback to `_gnn_cert_code`) and generates individual `.gnn-cert-badge` pills.
   - Wraps marquee elements into duplicates for seamless continuous GPU keyframe translation.
6. **Client-side Mount:** `gnn-logos-frontend.js` mounts on `.gnn-logos-layout-carousel` containers, binding touch swipe listeners, arrow controls, and pause-on-hover timers.

### 4.2 Auto-Updater Lifecycle & Transient Synchronization
1. **Polling Check:** During core update checks or viewing `plugins.php`, WordPress fires the `site_transient_update_plugins` filter.
2. **Transient Lookup:** `GNN_Logos_Updater::get_remote_release()` queries `get_transient('gnn_logos_github_update_check')`.
3. **Remote Egress:** If cache is expired, it queries `https://api.github.com/repos/BigDesigner/gnn-logos/releases/latest` with a 10s timeout.
4. **Host Validation (SSRF Defense):** Validates download URL host strictly against `github.com`, `codeload.github.com`, or `objects.githubusercontent.com`.
5. **Transient Synchronization (ADR-0006):**
   - If `remote_version > local_version`, constructs upgrade package object in `$transient->response[$slug]`.
   - If `local_version >= remote_version`, explicitly purges `$transient->response[$slug]` and populates `$transient->no_update[$slug]` to prevent false-positive update notifications.
6. **Post-Installation Normalization:** `upgrader_post_install` hook renames unzipped folder from `BigDesigner-gnn-logos-<hash>` to the canonical directory `gnn-logos`.

---

## 5. Concurrency & State Management

<!-- Verified from: gnn-logos/inc/updater.php#L35-L65 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php#L260-L290 -->

| Dimension | Pattern Applied | Enforcement / Implementation |
|---|---|---|
| **Session Locking** | `.memory-bank/.session.lock` | 10-minute timeout protocol for concurrent agent orchestration |
| **Atomic Writes** | `.tmp.json` -> `.json` rename | Used in `active-session.json` to prevent partial state corruption |
| **Race Condition Defense** | WordPress Nonce & Read-Through Transients | Nonces (`wp_nonce_field`) prevent CSRF replay during form saves; transients (`gnn_logos_github_update_check`) cache GitHub API responses for 12 hours (`43200s`) to prevent rate-limit exhaustion |
| **State Reset & Eviction** | Direct Transient Invalidation | Visiting `update-core.php` or executing manual check (`handle_manual_check`) immediately executes `delete_site_transient('update_plugins')` and `delete_transient('gnn_logos_github_update_check')` |

---

## 6. Architectural Design Patterns

<details>
<summary><b>[Deep Dive: Architectural Design Patterns]</b></summary>

- **Singleton Pattern (`GNN_Logos`):**
  - *Evidence:* `gnn-logos/gnn-logos.php#L35-L76`
  - *Rationale:* Ensures only a single instance of the coordinator class exists in memory, guaranteeing hooks are registered exactly once per request.

- **Modular Separation of Concerns (MVC Variant):**
  - *Evidence:* `includes/class-gnn-logos-cpt.php` (Model/Data), `includes/class-gnn-logos-shortcode.php` (Controller/Presenter), `includes/class-gnn-logos-admin.php` (Admin View/Config).
  - *Rationale:* Isolates frontend public display from administrative metadata editing and core asset compilation.

- **Zero-Dependency Progressive Enhancement:**
  - *Evidence:* `gnn-logos/assets/css/gnn-logos-frontend.css` (CSS Scroll-Snap and Keyframe transforms) and `gnn-logos/assets/js/gnn-logos-frontend.js` (<3 KB micro-controller).
  - *Rationale:* Eliminates heavy JS bundles (Swiper 150KB+, Slick, jQuery slider plugins), preserving sub-second Core Web Vitals (LCP, CLS, FID) even on mobile devices.

- **Defensive Transient Read/Write Synchronization:**
  - *Evidence:* `gnn-logos/inc/updater.php#L55-L60` and `ADR-0006`.
  - *Rationale:* Hooks both `pre_set_site_transient_update_plugins` (write path) and `site_transient_update_plugins` (read path) to eliminate stale cached notifications.
</details>

### 6.1 Historical Trade-offs & Ecosystem Drivers
- **Ecosystem Menu Slot ('79.109'):** Positioned strictly within the GNN product family registry, docking directly adjacent to WordPress Settings (`80`) to ensure consistent administrative ergonomics across multi-plugin GNN deployments.
- **Zero-Dependency Decision:** Third-party slider libraries (Slick, Owl Carousel, Swiper) were intentionally rejected to prevent jQuery script collisions, version deprecation cycles, and frontend payload bloat. The layout engine relies exclusively on modern CSS Scroll-Snap, GPU-accelerated Keyframe transforms, and a sub-3KB Vanilla JS touch controller.
