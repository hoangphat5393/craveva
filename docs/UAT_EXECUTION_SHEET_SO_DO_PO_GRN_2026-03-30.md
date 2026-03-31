# UAT Execution Sheet - SO/DO + PO/GRN (2026-03-30)

## Scope

- Sales flow: `SO -> Sales DO -> Invoice`
- Purchase flow: `PO -> GRN -> Bill`
- Environment: `staging`
- Goal: GO/NO-GO decision for business UAT window

## Preconditions

- [ ] `PURCHASE_FLOW_NAMING_MODE=compat_v2`
- [ ] `PURCHASE_DO_GRN_CUTOVER_ENABLED=true`
- [ ] HTTP health = `200` on `https://staging.craveva.com/`
- [ ] Latest DB backup exists in `storage/app/backups/phase3/`
- [ ] Rollback manifests are available:
    - Sales DO: `storage/app/reports/sales-do-migrate-manifest-*.json`
    - GRN: `storage/app/reports/grn-migrate-manifest-*.json`

## A. Sales UAT (SO -> Sales DO -> Invoice)

### A1. Sales DO Document Lifecycle

- [ ] Create Sales DO from existing SO.
- [ ] Confirm Sales DO (`draft -> confirmed`).
- [ ] Ship Sales DO (`confirmed -> shipped`).
- [ ] Deliver Sales DO (`shipped -> delivered`).
- [ ] Cancel/Reverse path works with expected guard rules.

### A2. Sales Stock Integrity

- [ ] Outbound stock posted exactly once on ship.
- [ ] Re-ship/retry does not double-post stock.
- [ ] Reverse restores stock correctly.

### A3. Invoice Interaction

- [ ] Invoice creation still works after Sales DO shipped/delivered.
- [ ] No duplicate outbound posting from invoice path when shipment mode active.

## B. Purchase UAT (PO -> GRN -> Bill)

### B1. GRN Document Lifecycle

- [ ] Create GRN from existing PO.
- [ ] Status progression works (`draft -> inbound -> received`).
- [ ] Edit/update GRN retains valid item payload.

### B2. Purchase Stock Integrity

- [ ] Inbound stock posted exactly once at `received`.
- [ ] Observer guard prevents PO+GRN double inbound.
- [ ] Batch/expiry/rule fields behave as expected when provided.

### B3. Bill Interaction

- [ ] Bill flow still works from PO/GRN context.
- [ ] No regression in vendor-facing purchase summary.

## C. Security and Access

- [ ] New Sales DO permissions enforce access correctly.
- [ ] New GRN permissions enforce access correctly.
- [ ] Legacy-only permission no longer bypasses access when cutover flag enabled.

## D. Rollback Readiness Drill

- [ ] Sales DO rollback dry-run from latest manifest.
- [ ] GRN rollback dry-run from latest manifest.
- [ ] Team confirms rollback owner and execution command are known.

## Automated Verification Snapshot

Fill from latest automated run:

- Command batch: `php artisan test ...`
- Result:
    - [ ] Pass
    - [ ] Fail
- Notes:
    - ...

## UAT Sign-off

- UAT Owner:
- QA Owner:
- Tech Owner:
- Date/Time:
- Decision:
    - [ ] GO
    - [ ] NO-GO
- Blocking issues (if NO-GO):
    - ...
