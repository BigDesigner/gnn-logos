# ADR 0003: Certificate Codes, Badges, and Card Layout Architecture

> [!WARNING]
> **SUPERSEDED ARCHITECTURAL DECISION**
> This decision was superseded on 2026-10-01 by [ADR-0005: Multi-Standard Tag Chips and Certificate Badges Layout Engine](file://.memory-bank/adr/0005-multi-standard-badges-and-layout-engine.md).
> Refer to the succeeding record for active multi-standard badge arrays and 3D layout/alignment controls.

- **Status**: Superseded by [ADR-0005: Multi-Standard Tag Chips and Certificate Badges Layout Engine](file://.memory-bank/adr/0005-multi-standard-badges-and-layout-engine.md)
- **Confidence**: Verified
- **Date**: 2026-09-30
- **Category**: Persistence & Schema Evolution
- **Supersedes**: None
- **Superseded By**: [ADR-0005: Multi-Standard Tag Chips and Certificate Badges Layout Engine](file://.memory-bank/adr/0005-multi-standard-badges-and-layout-engine.md)

## Context
The user specified that when displaying certificates (e.g., ISO standards, TSE norms such as `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2`), there must be a visually appealing, beautifully styled caption/code area underneath each certificate logo/mark.

## Decision
1. **Data Model Extension**: Add dedicated meta fields to the `gnn_logo` custom post type:
   - `_gnn_subtitle` / `_gnn_cert_code`: Specific standard/certificate code (e.g. `TS EN 12201-2`).
   - `_gnn_description`: Optional secondary text/issuing body info.
   - `_gnn_link_url` & `_gnn_link_target`: Optional URL target (e.g., link to certificate PDF or verification portal).
2. **Visual Presentation & Typography**:
   - Certificate card styling `.gnn-logo-card`: Clean card container with subtle border (`#e2e8f0`), rounded corners (`border-radius: 10px`), and soft hover lift (`transform: translateY(-3px); box-shadow: ...`).
   - Standard Code Badge `.gnn-cert-code`: Prominent yet refined badge typography with uppercase tracking (`letter-spacing: 0.5px; font-weight: 600; font-size: 13px;`), clean padding, and subtle background accent.
3. **Shortcode Controls**:
   - Parameters `style="card|bordered|minimal"`, `show_code="true|false"`, `show_title="true|false"`, `aspect_ratio="4/3|1/1|auto"`.

## Consequences
- Supports dual usage: minimal client/partner logo showcases AND formal, structured compliance/certificate showcases within the exact same plugin.

## Evidence
- User requirement for `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2` display.
- Design specs in `gnn_logos_implementation_plan.md`.
