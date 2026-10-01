# Developer Playbook, Conventions & Troubleshooting

<!-- Verified from: .specs/constitution.md#L1-L34 -->
<!-- Verified from: .specs/boundary-conditions.md#L1-L50 -->
<!-- Verified from: .github/workflows/release.yml#L1-L50 -->

## 1. Engineering Disciplines & Code Standards

### 1.1 WordPress Coding Standards (WPCS)
- **Direct Access Guard:** Every PHP file must begin with:
  ```php
  defined('ABSPATH') || exit;
  ```
  *(Or `defined('WP_UNINSTALL_PLUGIN') || exit;` for lifecycle scripts).*
- **Variable Unslashing & Sanitization:** Never sanitize superglobals without unslashing first:
  ```php
  $clean = sanitize_text_field(wp_unslash($_POST['key']));
  $clean_url = esc_url_raw(wp_unslash($_POST['url']));
  ```
- **Contextual Output Escaping:** Every dynamic value emitted into HTML templates must use explicit context escaping:
  - HTML text node: `esc_html($var)`
  - Tag attribute: `esc_attr($var)`
  - URL attribute: `esc_url($var)`
  - Internationalized string: `esc_html__('Text', 'gnn-logos')`
- **Zero External Frontend Dependencies Invariant:**
  - Absolute prohibition against jQuery slider plugins (Slick, Owl Carousel) or heavy CDN scripts (Swiper 150KB+).
  - All carousel interactions must use native CSS Scroll-Snap + lightweight Vanilla JS (<3 KB).
  - All ticker/marquee animations must use 100% pure CSS GPU keyframe transforms (`translate3d`).

---

## 2. Testing & Static Analysis Playbook

### 2.1 Syntax Linting (`php -l`)
Prior to every commit, every modified or added PHP file must be checked for syntax errors:
```bash
# Windows PowerShell batch linting:
Get-ChildItem -Recurse -Filter *.php gnn-logos | ForEach-Object { php -l $_.FullName }

# Linux / macOS Bash:
find gnn-logos -name "*.php" -exec php -l {} \;
```

### 2.2 Security Auditing & Sentinel Tools
```bash
# Verify Sentinel Memory Bank integrity:
/sentinel-doctor

# Verify Architecture Decision Records (ADRs):
/sentinel-adr

# Regenerate technical documentation:
/sentinel-docgen
```

---

## 3. Local Troubleshooting & Debugging Recipes

### 3.1 WordPress Debug Logging
To monitor runtime errors, notices, or database query issues, enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
Logs will be written to `wp-content/debug.log`.

### 3.2 Forcing an Update Check & Flushing Transients
If a new release was pushed to GitHub but WordPress does not immediately display the "Update Available" notification:
1. Navigate to:
   `https://example.com/wp-admin/plugins.php?gnn_logos_check_update=1`
2. This invokes `GNN_Logos_Updater::handle_manual_check()`, deleting `gnn_logos_github_update_check` and `_site_transient_update_plugins` transients, and safely redirecting to `update-core.php?force-check=1`.

### 3.3 Media Uploader Script Enqueueing
If the "Gözat / Logo Seç" button fails to open the WordPress media modal:
- Verify that `wp_enqueue_media()` is firing on the target admin screen.
- In `class-gnn-logos-admin.php#L130-L135`, scripts are enqueued conditionally only on `post_type === 'gnn_logo'` or `page === 'gnn-logos-generator'`.

---

## 4. Git & Contribution Workflow

- **Branch Strategy:** Work occurs on `main` for release branches or feature branches (`feat/feature-name`, `fix/issue-name`).
- **Commit Convention:** Conventional Commits standard:
  - `feat: release v1.2.0 with security hardening`
  - `fix(cpt): preserve newlines in legacy cert codes`
  - `docs(readme): fix shields badge endpoints with static svg`
  - `chore(sentinel): standardize ADR lineage and memory bank baseline`
- **Tagging Protocol:** Releases must be tagged with exact semantic versioning:
  ```bash
  git tag -a v1.2.0 -m "Release v1.2.0: Security hardening, sanitization alignment, and complete lifecycle cleanup"
  git push origin v1.2.0
  ```

---

## 5. Release & Deployment Checklist

Prior to publishing any new version to GitHub Releases:
- [ ] Update version in `gnn-logos/gnn-logos.php` (`Version: X.Y.Z` header and `GNN_LOGOS_VERSION` constant).
- [ ] Update `Stable tag: X.Y.Z` in `gnn-logos/readme.txt`.
- [ ] Update `@version X.Y.Z` in `gnn-logos-frontend.css`, `gnn-logos-admin.css`, `gnn-logos-frontend.js`, `gnn-logos-admin.js`.
- [ ] Run `php -l` across all PHP files to verify 0 syntax errors.
- [ ] Verify `README.md` badge URLs match the target version.
- [ ] Commit and push changes to `main`.
- [ ] Push Git tag `vX.Y.Z` to `origin`.
- [ ] Trigger GitHub Actions manual release workflow: `gh workflow run release.yml`.
- [ ] Verify GitHub Releases page contains `gnn-logos-X.Y.Z.zip` asset.
- [ ] Verify live WordPress installation receives and applies update cleanly.
