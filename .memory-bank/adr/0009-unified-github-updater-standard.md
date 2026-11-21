# ADR 0009: Unified GNN GitHub Updater Standard

- **Status**: Accepted
- **Date**: 2026-11-21
- **Supersedes**: [ADR-0006: Robust WordPress Update Transient Synchronization and Slug Normalization](file://.memory-bank/adr/0006-updater-transient-synchronization.md)
- **Superseded By**: None

## Context
`gnn-logos/inc/updater.php` (`GNN_Logos_Updater`) diverged from the unified GNN GitHub Updater standard in several areas:
- Hooked `site_transient_update_plugins` (causing unnecessary execution on every transient read).
- Lacked `upgrader_pre_install` active-state capture and post-install `activate_plugin()` / `wp_clean_plugins_cache(true)` calls.
- Lacked `wp_remote_head` + `expanded_assets` HTML fallback when the GitHub Releases API is rate-limited or unavailable.
- Used `strpos($name, '.zip') !== false` instead of suffix matching, disallowed `release-assets.githubusercontent.com` and `*.githubusercontent.com` in SSRF checks, and omitted prerelease semver suffixes.
- Deleted `update_plugins` site transient on `load-update-core.php` (`clear_cache`) and `after_install`, and omitted HTTP 403 status on `wp_die` in `handle_manual_check`.

## Decision
Standardized `GNN_Logos_Updater` in `gnn-logos/inc/updater.php` to the Unified GNN GitHub Updater Standard:
1. **Hooks**: Use `pre_set_site_transient_update_plugins` only (removed `site_transient_update_plugins`), `plugins_api` (priority 20, 3 args), `upgrader_pre_install` (`capture_active_state`), `upgrader_post_install` (`after_install`, priority 10, 3 args), `admin_init` (`handle_manual_check`), and `load-update-core.php` (`clear_cache`).
2. **Release Fetching & Fallback**: Primary GitHub Releases API (`https://api.github.com/repos/BigDesigner/gnn-logos/releases/latest`) with `wp_remote_head(..., ['redirection' => 0])` + `/releases/expanded_assets/{tag}` fallback, `.zip` suffix preference, semver validation (`^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$`), SSRF host allowlist (`sanitize_github_url`), 12-hour positive cache, and 300-second `(object)['failed' => true]` negative cache.
3. **Transient & Upgrade Lifecycle**: Guard `check_for_update` against empty `$transient->checked`, populate `response` or `no_update` cleanly, normalize paths in `after_install` with `$wp_filesystem->exists($destination)` check, flush plugin cache (`wp_clean_plugins_cache(true)`), restore active state via `$this->was_active`, and restrict `clear_cache()` to deleting only `$this->transient_key`.

## Consequences
- Eliminates rate-limit update blind spots via HTML redirect fallback.
- Preserves plugin activation state reliably across upgrades.
- Aligns update behavior, SSRF protections, and cache invalidation with all GNN plugins.
