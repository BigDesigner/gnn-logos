# ADR 0004: GNN Ecosystem Menu Position Registry & GitHub Auto-Updater

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-10-01

## Context
Following the established architecture of sibling GNN WordPress plugins (`gnn-smtpmail`, `gnn-filehub`, `gnn-terms-popup`), all GNN plugins follow consistent brand identity, author attribution, admin menu positioning, and seamless GitHub release auto-updating.

## Decision

### 1. Developer & Branding Standards
- **Plugin Name**: `GNN Logos` (GNN Logo & Sertifika Vitrini)
- **Author**: `BigDesigner`
- **Author URI**: `https://github.com/BigDesigner`
- **Plugin URI**: `https://github.com/BigDesigner/gnn-logos`
- **Text Domain**: `gnn-logos`
- **Donate Link**: `https://buymeacoffee.com/bigdesigner` (Plugin list actions)

### 2. Admin Menu Position Registry
- GNN plugins sit in the `'78.xyz'`–`'79.xyz'` slot band adjacent to WordPress Settings.
- As assigned, GNN Logos is assigned exact slot: `'79.109'`.
- Must be registered as a quoted string literal `'79.109'` (never a float) to avoid PHP float truncation collisions in WordPress `$menu`.
- Top-level menu: `GNN Logos` (Dashicon: `dashicons-images-alt2`, position `'79.109'`).
- Submenus:
  - `Tüm Logolar & Sertifikalar` (CPT edit screen)
  - `Yeni Ekle` (CPT new post)
  - `Logo Grupları` (Taxonomy `gnn_logo_group`)
  - `Shortcode Oluşturucu` (Interactive Shortcode Generator)
  - `Güncellemeleri Kontrol Et` (Manual update trigger)

### 3. Native GitHub Releases Auto-Updater (`inc/updater.php`)
- Integrated with WordPress transients and core update API (`pre_set_site_transient_update_plugins`, `plugins_api`, `upgrader_post_install`).
- Repository: `BigDesigner/gnn-logos`.
- Cache transient: `gnn_logos_github_update_check` (12-hour TTL).
- Manual check with nonces on `plugins.php?gnn_logos_check_update=1`.
- Post-install directory normalization to `gnn-logos`.

### 4. Repository & Shipping Layout
- Root directory contains repository meta, Sentinel Memory Bank, Specs, Tasks, and GitHub Actions workflow (`.github/workflows/release.yml`).
- Subfolder `gnn-logos/` contains the actual ship-ready plugin source, ensuring clean `.zip` packaging for GitHub releases and standard WordPress installation.

## Consequences
- Full parity with sibling GNN plugins.
- Users receive one-click update notifications inside WordPress Admin whenever a new GitHub release is tagged.
- Standardized, clutter-free menu layout positioned at `'79.109'`.

## Evidence
- Sibling plugin inspection: `gnn-smtpmail`, `gnn-terms-popup`, `gnn-filehub`.
- User explicit slot specification: `79.109`.
