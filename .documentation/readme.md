# GNN Logos - Technical Documentation Suite

<!-- Verified from: gnn-logos/gnn-logos.php#L1-L25 -->
<!-- Verified from: gnn-logos/readme.txt#L1-L20 -->

**Architecture Category:** WordPress Plugin (Logo, Partner & Quality Certificate Showcase)  
**Primary Stack:** PHP 8.0+, Pure CSS (GPU Keyframes & CSS Scroll-Snap), Vanilla JavaScript (<3 KB)  
**Current Version:** 1.2.0  
**License:** GNU General Public License v2 (GPL-2.0)  

---

## 1. Executive Summary

GNN Logos is an ultra-lightweight, zero-dependency WordPress showcase plugin engineered for presenting logos, reference partners, sponsors, and multi-standard quality certificates (e.g. `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2`) without third-party page builders or heavy JavaScript slider libraries (such as Slick, Owl Carousel, or Swiper). 

The plugin provides:
- A custom post type (`gnn_logo`) and hierarchical taxonomy (`gnn_logo_group`) with custom meta boxes for native WordPress Media Library image selection.
- Multi-standard tag chips supporting dynamic Enter/paste entry and structured flex-wrap monospace badge pill styling.
- Layout engine supporting Responsive Grid, CSS Scroll-Snap horizontal Carousel, and GPU-accelerated Keyframe Marquee/Ticker with left/right direction and pause-on-hover.
- Custom aspect-ratio container preservation (`16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto`) and `object-fit: contain` to prevent distortion.
- An interactive Shortcode Generator admin interface at position `'79.109'`.
- Native GitHub Releases automatic updater integrating directly with core WordPress update transients.

### 1.1 Business Objective & Target Audience
- **Target Sectors:** Industrial manufacturing, engineering, B2B services, corporate enterprises, and e-commerce platforms requiring verified compliance badge displays (TSE, EN, ISO standards).
- **Core Problem Solved:** Eliminates performance degradation, layout shifts (CLS), and PageSpeed penalties caused by bloated page-builder add-ons and external JavaScript slider libraries (Swiper 150KB+, Slick, Owl).
- **Agency & Developer Utility:** Provides a plug-and-play, zero-conflict solution for digital agencies and WordPress engineers to rapidly integrate clean, accessible logo and certificate showcases across client installations without licensing overhead or CDN vulnerabilities.

---

## 2. Prerequisites & System Requirements

<!-- Verified from: gnn-logos/readme.txt#L5-L10 -->
<!-- Verified from: gnn-logos/gnn-logos.php#L1-L20 -->

| Dimension | Minimum Requirement | Verified Supported Target | Notes |
|---|---|---|---|
| **PHP Runtime** | `8.0` | `8.0`, `8.1`, `8.2`, `8.3` | Uses modern type safety, strict string functions, and null coalescing |
| **WordPress Core** | `5.8` | Tested up to `6.7` | Requires `wp_get_attachment_image`, `wp_safe_redirect`, and native block compatibility |
| **Database** | MySQL `5.7+` / MariaDB `10.3+` | WordPress native default | Uses standard `wp_posts`, `wp_postmeta`, `wp_terms`, `wp_term_taxonomy` |
| **Web Server** | Apache / Nginx / LiteSpeed | HTTP/2 or HTTP/3 recommended | Requires standard PHP rewrite modules for WordPress pretty permalinks |
| **External Dependencies** | None (`0`) | Strictly zero npm/CDN dependencies | Eliminates jQuery slider plugins, CDNs, or foreign font dependencies |

---

## 3. Quick Start Installation

<!-- Verified from: .specs/bootstrap.md#L14-L20 -->

### Method A: Manual Installation
1. Download `gnn-logos.zip` from the [GitHub Releases](https://github.com/BigDesigner/gnn-logos/releases) page.
2. In the WordPress Admin, navigate to **Plugins** -> **Add New** -> **Upload Plugin**.
3. Upload the archive and click **Activate Plugin**.

### Method B: Direct Filesystem / Git Clone
1. Navigate to your WordPress installation's plugin directory:
   ```bash
   cd wp-content/plugins/
   ```
2. Clone the repository into `gnn-logos`:
   ```bash
   git clone https://github.com/BigDesigner/gnn-logos.git gnn-logos
   ```
3. Activate the plugin via WP-CLI:
   ```bash
   wp plugin activate gnn-logos
   ```
   Or activate via the WordPress Admin Dashboard (**Plugins** -> **Installed Plugins** -> **GNN Logos**).

---

## 4. Available Verification & CI/CD Scripts

<!-- Verified from: .github/workflows/release.yml#L1-L50 -->

| Command / Trigger | Environment | Purpose | Source / Citation |
|---|---|---|---|
| `php -l <file.php>` | Local CLI | Validates PHP syntax on individual files before staging | `.specs/constitution.md#L32` |
| `Get-ChildItem -Recurse -Filter *.php gnn-logos \| ForEach-Object { php -l $_.FullName }` | PowerShell (Windows) | Batch linting of all PHP files in plugin package | Local Dev Workflow |
| `gh workflow run release.yml` | GitHub CLI | Triggers automated GitHub Release build and zip packaging | `.github/workflows/release.yml#L1-L50` |
| `git status` | Git CLI | Verifies working tree cleanliness and tracking state | `.memory-bank/system-coherence.md#L11` |

---

## 5. Configuration & Constants Inventory

<!-- Verified from: gnn-logos/gnn-logos.php#L16-L20 -->
<!-- Verified from: gnn-logos/inc/updater.php#L20-L45 -->

| Identifier | Type | Default Value | Description | File Citation |
|---|---|---|---|---|
| `GNN_LOGOS_VERSION` | Constant (string) | `'1.2.0'` | Current release semantic version tag | `gnn-logos/gnn-logos.php#L17` |
| `GNN_LOGOS_FILE` | Constant (string) | `__FILE__` | Absolute path to the main plugin loader file | `gnn-logos/gnn-logos.php#L18` |
| `GNN_LOGOS_DIR` | Constant (string) | `plugin_dir_path(__FILE__)` | Absolute server directory path with trailing slash | `gnn-logos/gnn-logos.php#L19` |
| `GNN_LOGOS_URL` | Constant (string) | `plugin_dir_url(__FILE__)` | Public URI to the plugin root with trailing slash | `gnn-logos/gnn-logos.php#L20` |
| `$repo` | Property (string) | `'BigDesigner/gnn-logos'` | Remote GitHub repository slug for update polling | `gnn-logos/inc/updater.php#L24` |
| `$transient_key` | Property (string) | `'gnn_logos_github_update_check'` | Transient option key for caching remote GitHub releases | `gnn-logos/inc/updater.php#L38` |
| `$cache_duration`| Property (int) | `43200` (12 hours) | Time-to-live in seconds for update release cache | `gnn-logos/inc/updater.php#L45` |
| Menu Position | String | `'79.109'` | Dedicated WordPress admin sidebar slot for GNN family | `gnn-logos/includes/class-gnn-logos-admin.php#L38` |
