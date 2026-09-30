# Bootstrap & Environment Setup Specification

- **Project**: GNN Logos
- **Type**: WordPress Plugin
- **Confidence**: Verified

## 1. Prerequisites
- **Repository**: `https://github.com/BigDesigner/gnn-logos.git` (Verified)
- **PHP**: 8.0 or newer (Verified)
- **WordPress**: 5.8+ (tested up to 6.x) (Verified)
- **Web Server**: Apache / Nginx / LocalWP / Laragon / Docker WordPress (Verified)
- **Database**: MySQL 5.7+ or MariaDB 10.3+ (WordPress native) (Verified)

## 2. Installation & Directory Placement
1. Copy or clone the `gnn-logos` directory into the WordPress plugins directory:
   `wp-content/plugins/gnn-logos/`
2. Navigate to WordPress Admin Dashboard -> Plugins -> Installed Plugins.
3. Activate **GNN Logo Showcase**.

## 3. Directory Layout
```text
gnn-logos/
├── gnn-logos.php                     # Main plugin entry point
├── README.md                          # Usage documentation & shortcode parameters
├── includes/
│   ├── class-gnn-logos-cpt.php        # CPT & Taxonomy & Meta Boxes
│   ├── class-gnn-logos-shortcode.php  # Shortcode [gnn_logos] handler
│   └── class-gnn-logos-admin.php      # Admin pages & Shortcode Generator
└── assets/
    ├── css/
    │   ├── gnn-logos-frontend.css     # Frontend layout, cards, animations
    │   └── gnn-logos-admin.css        # Admin meta box styling
    └── js/
        ├── gnn-logos-frontend.js      # Ultra-light vanilla carousel controller
        └── gnn-logos-admin.js         # Media uploader integration
```

## 4. Suggested Validation Commands
| Command | Purpose | Prerequisite | Notes |
|---|---|---|---|
| `php -l <file.php>` | Lint PHP file for syntax errors | PHP CLI installed | Mandatory prior to commit |
| `git status` | Verify clean working tree | Git | Essential for change tracking |
