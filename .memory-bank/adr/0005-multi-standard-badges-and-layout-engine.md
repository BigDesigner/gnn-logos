# ADR 0005: Multi-Standard Tag Chips and Certificate Badges Layout Engine

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-10-01
- **Category**: Persistence & Schema Evolution
- **Supersedes**: [ADR-0003: Certificate Codes, Badges, and Card Layout Architecture](file://.memory-bank/adr/0003-certificate-card-layout.md)
- **Superseded By**: None

## Context
Industrial certificates, technical compliance documents, and partner logos often require displaying multiple standards simultaneously (e.g., `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2`, `ISO 9001:2015`).
Initially, a single text input combined all standards into one block, which clumped together into an overflowing container on frontend cards. Furthermore, cards with differing numbers of badges suffered from bottom-stuck badges with excessive empty whitespace between the logo and the badge.

## Decision

### 1. Dynamic Admin Tag Manager
- Replaced the single text input with an interactive tag chips manager in `class-gnn-logos-cpt.php`.
- Admins can add multiple standards with Enter key or bulk paste (comma/newline separated).
- Meta key `_gnn_cert_codes` stores an array of sanitized standard strings.
- Backward compatibility: Legacy string values in `_gnn_cert_code` are automatically parsed and migrated into arrays.

### 2. Multi-Dimensional Layout and Alignment Controls
Added three granular control attributes to `[gnn_logos]`:
1. `badges_layout` (`wrap` | `stacked`):
   - `wrap`: Flex-wrap horizontal pills side-by-side.
   - `stacked`: Single-column vertical stack for clean readability on industrial product cards.
2. `badges_align` (`center` | `left` | `right`):
   - Controls horizontal alignment of badges within the card.
3. `badges_valign` (`bottom` | `top` | `center`):
   - `bottom` (default): Pushes badges to the bottom of the card (`margin-top: auto;`).
   - `top`: Positions badges directly 14px beneath the logo (`justify-content: flex-start;`). Eliminates awkward empty vertical whitespace when adjacent cards have differing badge counts.
   - `center`: Centers logo and badges vertically as a cohesive unit.

### 3. Responsive Overflow Protection
- All `.gnn-cert-badge` chips feature `max-width: 100%`, `box-sizing: border-box`, `overflow: hidden`, and `text-overflow: ellipsis` to guarantee zero layout breakage on mobile devices.

### 4. Shortcode Wizard Integration
- Integrated all three dimensions ("Rozet Dizilimi", "Yatay Hizalama", "Dikey Hizalama") into the Shortcode Builder form in `class-gnn-logos-admin.php` and `gnn-logos-admin.js`.
- Explicitly emits attributes even when default (`badges_align="center"`, `badges_layout="wrap"`, `badges_valign="bottom"`), ensuring predictable output.

## Consequences
- High-density standard codes render cleanly across varying screen sizes.
- Users have full visual authority over badge placement and card aesthetics.
- Zero external libraries or bloated CSS frameworks introduced.

## Lineage & Migration
1. **Deficiency of ADR-0003**: ADR-0003 only supported a single string field (`_gnn_cert_code`) and a single `.gnn-cert-code` banner. This caused standard codes to clump into a single overflowing text block when multiple standards were required.
2. **Migration Path**: Existing single strings stored in `_gnn_cert_code` are automatically detected and parsed into the new `_gnn_cert_codes` array upon load and save.
3. **Backward Compatibility**: Any existing shortcodes without `badges_layout`, `badges_align`, or `badges_valign` maintain their visual appearance via safe defaults (`wrap`, `center`, `bottom`).

## Evidence
- User feedback and screenshot verification (`media_1790811958987.png`, `media_1790812476750.png`, `media_1790814367888.png`).
