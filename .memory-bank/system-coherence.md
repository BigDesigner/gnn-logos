# System Coherence & Operational Governance

- **Project**: GNN Logos (WordPress Logo & Certificate Showcase Plugin)
- **Status**: Active
- **Governance Version**: 1.1.0

## 1. Session Start Protocol
1. Confirm memory bank integrity by reading `.memory-bank/active-session.json`.
2. Inspect `.memory-bank/.session.lock`. If locked within the last 10 minutes, halt and wait. If stale (>10 min), remove lock, log warning in `bug-list.md`, and proceed.
3. Verify current task in `.tasks/pipeline.md`.
4. Ensure working directory is clean before major code modifications.

## 2. Operating Mode Protocol
- **Interactive Mode**: All user-facing decisions and destructive actions require explicit user approval. Communications must strictly use the confirmed `preferred_language` (Turkish).
- **CI Mode**: Non-interactive; writes to `ci-run-summary.md` and treats unconfirmed decisions as `Proposed` ADRs.

## 3. Context Drift Prevention
- Source of truth for functional and technical specifications resides in `.specs/constitution.md` and `.specs/boundary-conditions.md`.
- No architectural modifications may be introduced without creating a corresponding numbered ADR under `.memory-bank/adr/`.
- Every feature must strictly adhere to the zero-external-dependency rule for frontend assets (maximum payload budget: CSS < 10KB, JS < 5KB).

## 4. Pre-Change Checklist
- [ ] Check `.memory-bank/bugs/bug-list.md` for active blockers.
- [ ] Read corresponding spec requirements in `.specs/boundary-conditions.md`.
- [ ] Verify PHP syntax safety and WordPress hook compatibility.

## 5. Post-Change Checklist
- [ ] Run PHP syntax checks (`php -l`) on all modified or new PHP files.
- [ ] Verify escaping (`esc_html`, `esc_attr`, `esc_url`) and sanitization routines.
- [ ] Verify nonce verification in admin actions and settings submissions.
- [ ] Update `.tasks/pipeline.md` and `.memory-bank/changelog/verified-worklog.md`.
- [ ] Update `.tasks/handoff.md` when closing or transferring sessions.

## 6. Concurrency & Lock Management
- Always create `.memory-bank/.session.lock` on session start.
- Always delete `.memory-bank/.session.lock` on session finish.
- Perform atomic writes to `active-session.json` via `.tmp.json`.
