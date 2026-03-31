# Phase 4 Go-Live Readiness (2026-03-30)

## Current Decision

- Status: **Ready for controlled go-live** (technical cutover on staging completed).
- Scope migrated: company `20`.

## Evidence (Staging)

- Cutover flags effective:
    - `purchase.flow_naming_mode=compat_v2`
    - `purchase.do_grn_cutover_enabled=true`
- Route aliases available:
    - `sales-do.*`
    - `grn.*`
- Health:
    - App bootstrap: pass
    - DB connectivity: pass
    - HTTP: `200`
    - Disk free: ~`3.0GB`
- Data migration status:
    - Post-cutover dry-run report shows:
        - `source.shipments_count=1`
        - `target.headers_migrated_count=1`
        - `target.items_migrated_count=1`
        - `pending.shipments_count=0`
        - `pending.items_count=0`
- Rollback capability validated with execute rehearsal (manifest-based delete and restore rehearsal path documented).

## Remaining Before Production Go-Live

- Run business UAT checklist with end users (SO -> Sales DO -> Invoice, PO -> GRN -> Bill).
- Normalize deployment path:
    - Commit/push all cutover files from local branch.
    - Deploy by `git pull --ff-only` on target environment.
    - Avoid hot-upload drift (`scp`-only state).
- Keep rollback manifest and latest DB backup accessible during go-live window.

## Manual UAT Quick Checklist

- Sales outbound:
    - Create Sales DO from SO.
    - Confirm -> Ship -> Deliver lifecycle works.
    - Stock outbound posted once (no double post).
    - Reverse/Cancel path restores stock correctly.
- Purchase inbound:
    - Create GRN from PO.
    - Change status `draft -> inbound -> received`.
    - Stock inbound posted once.
- Permissions:
    - New permissions for Sales DO/GRN users can access expected actions.
    - Legacy-only users cannot bypass after cutover flag enabled.
