# UAT Walkthrough - Staging (SO/DO + PO/GRN)

## Current technical baseline

- Sales DO migration dry-run: pending `0`
- GRN migration dry-run: pending `0`
- Recent staging error log (15:00+): `0` critical/error entries

## Tester roles

- Sales operator
- Purchase operator
- Warehouse operator
- QA reviewer (final sign-off)

## 1) Sales flow walkthrough (SO -> Sales DO -> Invoice)

### Step 1 - Create Sales DO from SO

1. Open an existing SO with at least one stockable item.
2. Click create/add Sales DO.
3. Fill warehouse, quantities, date, save.
4. Expected:
    - Document saved without validation error.
    - Sales DO appears in list.

### Step 2 - Confirm -> Ship -> Deliver

1. Open created Sales DO.
2. Click `Confirm`, then `Ship`, then `Deliver`.
3. Expected:
    - Status transitions correctly.
    - No duplicate/invalid transition errors.

### Step 3 - Stock integrity

1. Record stock before ship.
2. Ship once and refresh stock.
3. Retry ship action (or re-open and refresh).
4. Expected:
    - Stock decreases once only.
    - No double deduction.

### Step 4 - Reverse/Cancel safety

1. Trigger reverse/cancel path on shipped/delivered DO.
2. Expected:
    - Status guard behaves correctly.
    - Stock is restored exactly once.

### Step 5 - Invoice compatibility

1. Create invoice from same SO flow.
2. Expected:
    - Invoice still created normally.
    - No extra outbound stock posting from invoice path.

## 2) Purchase flow walkthrough (PO -> GRN -> Bill)

### Step 1 - Create GRN from PO

1. Open an existing PO.
2. Create GRN and save.
3. Expected:
    - GRN is created and visible.

### Step 2 - GRN lifecycle

1. Change GRN status `draft -> inbound -> received`.
2. Expected:
    - Valid transitions succeed.
    - Invalid transition is blocked with proper message.

### Step 3 - Inbound stock integrity

1. Capture stock before receiving.
2. Set GRN to `received`.
3. Expected:
    - Inbound stock posted once.
    - PO/GRN double-inbound guard prevents duplicate stock.

### Step 4 - Bill compatibility

1. Continue bill flow from PO/GRN context.
2. Expected:
    - Bill creation/edit still works.
    - No regression in vendor summaries.

## 3) Permission validation

1. Login user with new Sales DO permissions.
2. Verify allowed actions are visible and functional.
3. Login user without required permissions.
4. Expected:
    - Unauthorized actions blocked.
    - No legacy permission bypass when cutover enabled.

## 4) Result template

- Sales flow:
    - [ ] Pass
    - [ ] Fail
- Purchase flow:
    - [ ] Pass
    - [ ] Fail
- Permission checks:
    - [ ] Pass
    - [ ] Fail
- Final:
    - [ ] GO
    - [ ] NO-GO
- Notes:
    - ...
