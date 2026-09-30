# Project Constitution & Engineering Standards

- **Project**: GNN Logos
- **Status**: Active Standard
- **Confidence**: Verified

## 1. Code Quality & Formatting
- **Standard**: Adhere strictly to the official WordPress Coding Standards (WPCS) and PHP-FIG PSR-12 principles.
- **Naming Conventions**:
  - Class names: `class-gnn-logos-<feature>.php` (e.g. `class-gnn-logos-cpt.php`), class defined as `GNN_Logos_CPT`.
  - Function & method names: lowercase with underscores (snake_case) e.g., `render_shortcode()`, `register_post_type()`.
  - Constants: uppercase snake_case with plugin prefix e.g., `GNN_LOGOS_VERSION`, `GNN_LOGOS_PATH`.
  - Hook identifiers: prefixed with `gnn_logos_` e.g., `gnn_logos_before_render`.

## 2. Architecture & Modularity
- **Separation of Concerns**:
  - `class-gnn-logos-cpt.php`: Solely handles custom post types, custom taxonomies, and meta boxes.
  - `class-gnn-logos-shortcode.php`: Solely handles shortcode parsing, WP_Query execution, and HTML output rendering.
  - `class-gnn-logos-admin.php`: Solely handles admin dashboard menus, scripts enqueueing, and Shortcode Generator UI.
- **No Global Scope Pollution**: All plugin functionality must be wrapped inside classes or namespaced functions.
- **Single Source of Truth**: Assets, versions, and paths defined in the main plugin file `gnn-logos.php`.

## 3. UI/UX Standards
- **Admin UI**: Seamlessly inherit native WordPress admin styling (`wp-core-ui`, Dashicons). No clashing custom fonts or foreign admin redesigns.
- **Frontend Presentation**:
  - High aesthetic standards for certificates and logos: clean cards, subtle borders (`#e2e8f0`), rounded corners (`10px`), and refined typography for standard codes (e.g. `TS EN 12201-2`).
  - Seamless responsive scaling across Desktop (>1024px), Tablet (768px-1024px), and Mobile (<768px).
  - Smooth 60fps transitions using CSS GPU transforms (`translate3d`).

## 4. Agent Behavior & Discipline
- **No Unapproved Source Alterations**: Never alter production code without an explicit task or plan.
- **Syntax Validation**: Run `php -l` on every PHP file after creation or modification.
- **Zero Hallucination of APIs**: Verify that any used WordPress function exists in WordPress 5.8+.
