# Production Operations Live (EN)

_Purpose: a live operations document for the Production module, so business questions can be answered without re-reading code._

## 0) Product types (master data before BOM)

| Production role              | `products.type`                              | Typical form label                       |
| ---------------------------- | -------------------------------------------- | ---------------------------------------- |
| BOM output / order FG        | `goods`                                      | Manufactured product                     |
| BOM components (consumption) | `raw_material`, `semi_finished`, `packaging` | Raw Material / Semi Finished / Packaging |
| Not used                     | `service`                                    | Service                                  |

- BOM **output** dropdown = `forBomOutput()` → `goods` only.
- BOM **component** dropdown = `forBomComponents()` → the three component types.
- Customer SOP: [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_EN.md`](../PROJECT%20BIOMIXING/PRODUCTION_MODULE_SOP_EN.md) sections 0–2 · detail [`PRODUCTION_PRODUCT_TYPES_EN.md`](./PRODUCTION_PRODUCT_TYPES_EN.md).

## 1) Production order lifecycle

- `Draft` -> `Released` -> `In progress` -> `Completed`
- `Cancel` is allowed when:
    - `Draft` (always allowed)
    - `Released` but **raw materials not posted yet** and **manufactured product not posted yet**
- `Cancel` is not allowed when:
    - `In progress`
    - `Completed`

## 2) Inventory and reservation rules (implemented)

### On Release

- The system captures a **bill of materials snapshot** based on `planned_quantity`.
- The system checks available inventory:
    - `available = on_hand - reserved` (already excluding **delivery order** reservations and other production order reservations)
- If insufficient -> release is blocked (`insufficientRmToReserve`).
- If sufficient -> **raw material** reservations are created via `StockReservationService`, `reference_type = ProductionOrder`.
- Raw material lots are allocated on a **first expiry, first out** basis (earliest expiry first), aligned with outbound behavior; users do not need to assign lots before release.

**Product decision (finalized, previously in plan `19_*`):**

| Event                                   | Reserve?                                                       |
| --------------------------------------- | -------------------------------------------------------------- |
| Draft (create/edit planning)            | **No**                                                         |
| **Release**                             | **Yes** — production commitment                                |
| Assign raw material lot on batch screen | **No** — only lot selection for posting; reserve is at Release |
| Post raw materials (Deduct)             | Deducts real `quantity`; does not create extra reservation     |

### On Cancel (Released)

- The system `release`s all active reservations for the order.

### On post raw materials (Deduct raw materials)

- Raw materials are issued from inventory (`quantity` decreases).
- When all batches of an order have posted raw materials -> the order reservation is `consume`d.
- Then the order moves to `In progress` (if previously `Released`).

### On post manufactured product

- Manufactured product is received into inventory.
- When there is no unposted output -> order moves to `Completed`.

## 3) Material shortage summary (current operations)

- Purpose: aggregate **raw material** shortages by `raw material + warehouse` across **multiple orders** (no manual sum per order).
- Per-row formula: `shortage = max(0, total_required - available)`; `available = on_hand - reserved` (base unit of measure).
- **Status meaning:**
    - **Draft** — planning / early procurement; no Production reservation.
    - **Released / In progress** — committed demand; `available` must reflect Production reservations (+ **delivery order** reservations if any).
- **Default status filter:** `active` = **Released + In progress** (committed demand, with reservations). Other options: `draft`, `all` (Draft + Released + In progress), `released`, `in_progress`.
- `Completed` / `Cancelled`: excluded from summary scope.

## 4) Quick business meaning for project managers and quality assurance

- `Draft`: planning stage, no Production reservation yet.
- `Released`: production is committed, raw materials already reserved.
- `In progress`: raw material deduction has started / batch execution in progress.
- `Completed`: manufactured product posting is finished.

## 5) Canonical flow/test references

- Flow test run (Vietnamese): `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`
- Flow test run (English): `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd`
- UAT test cases: `FUNC_IMPROVE/19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`
- Biomixing test matrix: `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`
- Non-technical SOP (English): `PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_EN.md`

## 6) Do not use old implementation plans

- Completed implementation plan documents should be treated as historical.
- For current operations, prioritize this file + the flow/test references in section 5.

## 7) Batch — planned raw materials (former Step 1)

- The checklist step and **Create planned raw material lines from bill of materials snapshot** button are **hidden** by default.
- On **release** (first batch created) and when **opening the batch screen** (if no RM lines yet), the system auto-inserts `production_batch_consumptions` from the order **BOM snapshot** (frozen at release).
- The batch checklist starts at **assign raw material warehouse batch** → deduct → add manufactured product → post FG.
- To restore manual Step 1: see `FUNC_LOGIC/PRODUCTION_BATCH_STEP1_RESTORE_VI.md` (`production.ui.auto_apply_bom_snapshot_on_batch`, `show_batch_workflow_step_planned_lines`, `show_apply_planned_from_snapshot_button`).
