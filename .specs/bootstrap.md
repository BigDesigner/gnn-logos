# Bootstrap & Environment Setup Specification

- **Project**: GNN Logos
- **Type**: WordPress Plugin
- **Confidence**: Verified

## 1. Prerequisites
- **Repository**: `https://github.com/BigDesigner/gnn-logos.git` (Verified)
- **PHP**: 8.0 or newer (Verified)
- **WordPress**: 5.8+ (tested up to 7.1) (Verified)
- **Web Server**: Apache / Nginx / LocalWP / Laragon / Docker WordPress (Verified)
- **Database**: MySQL 5.7+ or MariaDB 10.3+ (WordPress native) (Verified)

## 2. Installation & Directory Placement
1. Copy or clone the `gnn-logos` directory into the WordPress plugins directory:
   `wp-content/plugins/gnn-logos/`
2. Navigate to WordPress Admin Dashboard -> Plugins -> Installed Plugins.
3. Activate **GNN Logos**.

## 3. Directory Layout
```text
gnn-logos/
├── gnn-logos.php                      # Main plugin entry point
├── readme.txt                         # WordPress plugin repository metadata
├── uninstall.php                      # Safe cleanup routine
├── languages/
│   └── gnn-logos.pot                  # Translation template (177 strings)
├── inc/
│   └── updater.php                    # Unified GNN GitHub Releases auto-updater
├── includes/
│   ├── class-gnn-logos-cpt.php        # CPT, Taxonomy, Meta Boxes, Reorder & Bulk AJAX
│   ├── class-gnn-logos-shortcode.php  # Shared [gnn_logos] & Block rendering engine
│   ├── class-gnn-logos-admin.php      # Admin menu ('79.109') & Live Preview Generator
│   └── class-gnn-logos-block.php      # Native Gutenberg block registration & callback
├── blocks/
│   └── showcase/
│       ├── block.json                 # Block API v3 schema (gnn-logos/showcase)
│       └── editor.js                  # Zero-build Gutenberg Inspector + ServerSideRender
└── assets/
    ├── css/
    │   ├── gnn-logos-frontend.css     # Theme tokens, Glass, Marquee, Carousel, Lightbox
    │   └── gnn-logos-admin.css        # CPT table, Bulk bar, Live Preview Generator UI
    └── js/
        ├── gnn-logos-frontend.js      # Vanilla JS Marquee Fill, Carousel, Lightbox
        └── gnn-logos-admin.js         # Reorder, Bulk Upload, Media, Live Preview Wizard
```

## 4. CI/CD Pipelines
- **Pipeline File**: `.github/workflows/release.yml` (Verified)
- **Trigger**: Manual `workflow_dispatch`
- **Jobs**:
  - `build-and-release`: Syntax linting with `php -l`, automated packaging of `gnn-logos/` directory into zip, and artifact release upload via `softprops/action-gh-release@v3`.

## 5. Suggested Validation Commands
| Command | Purpose | Prerequisite | Notes |
|---|---|---|---|
| `php -l <file.php>` | Lint PHP file for syntax errors | PHP CLI installed | Mandatory prior to commit |
| `node --check <file.js>` | Validate JS syntax | Node.js installed | Recommended for frontend/admin/block JS |
| `Get-Content gnn-logos/blocks/showcase/block.json -Raw \| ConvertFrom-Json` | Validate Block JSON schema | PowerShell | Mandatory after block.json edits |
| `git status` | Verify clean working tree | Git | Essential for change tracking |
