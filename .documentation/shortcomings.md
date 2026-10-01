# Technical Debt, Shortcomings & Runtime Risks

<!-- Verified from: gnn-logos/inc/updater.php#L85-L115 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php#L130-L160 -->
<!-- Verified from: .specs/boundary-conditions.md#L50-L75 -->

## 1. Code Debt Markers Inventory

A comprehensive static inspection of all PHP, JavaScript, and CSS files was performed searching for `TODO`, `FIXME`, `HACK`, `BUG`, `XXX`, and `DEPRECATED` annotations:

| Marker Type | File Path | Line | Code Excerpt / Summary | Status / Severity |
|---|---|---|---|---|
| None | N/A | N/A | Zero debt markers detected across all 10 project source files | [OK] Clean Baseline |

---

## 2. Unverified Runtime Risks & External Boundaries

<!-- Verified from: gnn-logos/inc/updater.php#L95-L110 -->

> [!WARNING]
> Static syntax validation (`php -l`) and unit linting prove only that PHP code parses without fatal syntax errors. The following operational boundaries depend on runtime server environments and external infrastructure:

| Risk Identifier | Component | Boundary Description | Potential Failure Mode | Mitigating Architecture |
|---|---|---|---|---|
| **R-001: GitHub API Rate Limits** | `GNN_Logos_Updater` | GitHub's public API limits unauthenticated requests to 60 requests/hour per originating IP address. | If a shared hosting server hosts multiple sites checking updates or an admin repeatedly flushes cache, GitHub returns `403 Forbidden` (`API rate limit exceeded`). | Mitigated by a 12-hour transient cache (`gnn_logos_github_update_check`). Failures are cached for 5 minutes (`300s`) to prevent rapid retry loops. |
| **R-002: Filesystem Move Permissions** | `GNN_Logos_Updater::after_install` | WordPress `WP_Filesystem::move()` is invoked to normalize the unzipped GitHub folder to `wp-content/plugins/gnn-logos/`. | On servers with restrictive file ownership (e.g. PHP running under `www-data` while files are owned by `root`), the filesystem move operation may fail. | WordPress core prompts for FTP/SSH credentials if direct filesystem writes fail. Standard `upgrader_post_install` error return is preserved. |
| **R-003: Large Dataset Memory Saturation** | `GNN_Logos_Shortcode` | When `limit="-1"` is specified, `WP_Query` retrieves all published `gnn_logo` posts into memory. | On sites with hundreds of logos, fetching all post records and looping through attachment metadata in a single request could cause PHP memory exhaustion or slow TTFB. | Recommend setting practical `limit` attributes (e.g. `20` or `30`) for marquee and carousel showcases. |
| **R-004: Browser CSS Aspect Ratio Fallback** | `gnn-logos-frontend.css` | Uses modern CSS `aspect-ratio` property. | Legacy browsers (Chrome <88, Safari <15) do not support native CSS `aspect-ratio`. | Handled progressively; fallback relies on `max-height: 100%` and `object-fit: contain` on `.gnn-logo-img` to avoid horizontal layout breaking. |

---

## 3. Boundary Hardening History (Resolved in v1.2.0)

During the recent Sentinel security audit (Commit `5e32cbe`), several architectural seams were identified and permanently resolved:

1. **Legacy Certification Code Newline Stripping (H-1):**
   - *Previous Risk:* `sanitize_text_field()` stripped `\r\n` characters before `preg_split` could parse them, breaking multi-standard line separation.
   - *Resolution:* Upgraded to `sanitize_textarea_field(wp_unslash($_POST['gnn_cert_code']))`.
2. **Shortcode `orderby` Parameter Allowlist (H-3):**
   - *Previous Risk:* Unrestricted query string input passed to `WP_Query` orderby parameter.
   - *Resolution:* Enforced strict in_array allowlist (`menu_order`, `date`, `title`, `rand`, `ID`, `author`, `name`, `modified`, `parent`, `none`).
3. **DOM-based XSS Prevention in Admin UI (H-4):**
   - *Previous Risk:* Image preview box used `.html('<img src="' + url + '">')` string concatenation.
   - *Resolution:* Replaced with native jQuery element construction (`$('<img>').attr('src', url)`).
4. **Orphaned Database Records upon Deletion (M-1):**
   - *Previous Risk:* `uninstall.php` only deleted update transients, leaving custom post entries and taxonomy terms in the database.
   - *Resolution:* Upgraded `uninstall.php` to completely purge all `gnn_logo` posts, postmeta, `gnn_logo_group` terms, and transients.

---

## 4. Product Roadmap & Strategic Evolution
- **Native Block & Page Builder Extensions:** Development of dedicated Gutenberg native blocks (`@wordpress/block-editor`) and Elementor custom widgets to provide visual controls alongside the existing shortcode engine.
- **Multi-Lingual Localization:** Formal integration and compatibility testing with WPML and Polylang for multi-language logo titles, descriptions, and certificate badge chips.
