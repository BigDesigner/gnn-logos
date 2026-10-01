# ADR 0001: Initial Architecture & Technology Stack

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-09-30
- **Category**: Foundational Dependencies & Persistence
- **Supersedes**: None
- **Superseded By**: None

## Context
The project requires a lightweight, performant WordPress plugin to manage and display partner logos, client references, certificates, and accreditation marks across any WordPress page using shortcodes. The plugin must run natively within WordPress environments without imposing heavy dependencies or breaking Core Web Vitals.

## Decision
1. **Platform**: Standard WordPress Plugin architecture adhering to WordPress Coding Standards (WPCS).
2. **Backend**: Object-Oriented PHP 8.0+ utilizing WordPress native Custom Post Types (`gnn_logo`), Custom Taxonomies (`gnn_logo_group`), and Meta Boxes.
3. **Admin Integration**: WordPress Native Media Uploader (`wp.media`) for selecting, uploading, and managing logo assets directly from the WordPress Media Library.
4. **Data Storage**: Native WordPress database schema (`wp_posts`, `wp_postmeta`, `wp_term_relationships`) to eliminate custom database table overhead and maximize backup/migration compatibility.

## Consequences
- Zero custom SQL table creation or migration headaches.
- Native compatibility with popular caching plugins, object caches (Redis/Memcached), and standard WordPress backup tools.
- Strict requirement for nonce validation, capability checks (`manage_options`), and output escaping across all admin interfaces.

## Evidence
- WordPress Core Plugin Developer Handbook
- Architecture specification in `gnn_logos_implementation_plan.md`
