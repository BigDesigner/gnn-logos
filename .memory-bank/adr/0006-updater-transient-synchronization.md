# ADR 0006: Robust WordPress Update Transient Synchronization and Slug Normalization

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-10-01
- **Category**: External Integrations & Structural Topology
- **Supersedes**: [ADR-0004: GNN Ecosystem Menu Position Registry & GitHub Auto-Updater](file://.memory-bank/adr/0004-admin-menu-position-and-auto-updater.md) (Section 3: Updater Engine)
- **Superseded By**: None

## Context
When updating a custom GitHub-hosted plugin, users frequently encounter a false positive "Update Available" notification on `plugins.php` even after upgrading to the latest version.
This occurs because WordPress caches the `update_plugins` transient in `wp_options` for 12 hours. If an update response is present in `$transient->response[$plugin_slug]`, WordPress displays the update notice regardless of the local version on disk unless the transient is explicitly sanitized on read or invalidated.

## Decision

### 1. Dual-Hook Filter Architecture
- Attached `check_for_update` to both:
  - `pre_set_site_transient_update_plugins` (triggered when WordPress saves the transient).
  - `site_transient_update_plugins` (triggered when WordPress reads `get_site_transient('update_plugins')`).

### 2. Explicit Stale Response Purging
- When `version_compare($release->version, $local_version, '<=')` is true (i.e., local is up to date or newer):
  - Strictly `unset($transient->response[$this->plugin_slug])`.
  - Populates `$transient->no_update[$this->plugin_slug]` with current metadata.
- Completely prevents stale update banners from lingering on `plugins.php`.

### 3. Immediate Cache Invalidation
- Added `delete_site_transient('update_plugins')` to:
  - `after_install()` (`upgrader_post_install` hook after plugin update completes).
  - `clear_cache()` (`load-update-core.php` hook).
  - `handle_manual_check()` (Action link `plugins.php?gnn_logos_check_update=1`).

### 4. Dynamic Plugin Slug Resolution
- Replaced hardcoded string with `plugin_basename(GNN_LOGOS_FILE)` during constructor initialization.
- Guarantees 100% key matching across custom directory names or folder variations.

## Consequences
- Eliminates recurring false-positive update notices.
- WordPress Admin accurately reflects the real-time installation status.
- Zero extra database queries; operates purely in memory on transient objects.

## Lineage & Migration
1. **Deficiency of ADR-0004 Section 3**: ADR-0004 hooked only `pre_set_site_transient_update_plugins` and did not unset response entries when versions matched. This left stale transient data in `wp_options` for up to 12 hours after an upgrade.
2. **Migration Path**: The updater now filters transient reads (`site_transient_update_plugins`), immediately sanitizing existing cache entries on every admin screen load.
3. **Backward Compatibility**: Fully transparent to end users; no settings changes required.

## Evidence
- User screenshot `media_1790813125491.png` showing version 1.0.2 installed while simultaneously prompting to update to 1.0.2.
- Verified fix in `inc/updater.php` running seamlessly against GitHub Releases API.
