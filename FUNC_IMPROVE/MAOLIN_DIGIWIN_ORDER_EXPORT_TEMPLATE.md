# Maolin / DigiWin — Order export template (future implementation note)

**Status:** Spec captured from customer sample — **export not implemented yet**. Use this when building evening sync / bi-directional API file export (ERP → DigiWin).

**Customer requirement (2026-05-25, chat):** Export from Craveva ERP must match the **DigiWin sales order write-in template** (“your export file need to be like this”).

---

## 1) Canonical sample file (do not rename lightly)

| Item                 | Value                                                           |
| -------------------- | --------------------------------------------------------------- |
| **Path in repo**     | `PROJECT MAOLIN/(Order Sample) 訂單寫入模板資料_260525.xlsx`    |
| **Meaning**          | 訂單寫入模板資料 = order entry / write-in template data         |
| **Date in filename** | 260525 = 2026-05-25                                             |
| **Format**           | Excel `.xlsx`, single sheet, **11 columns** (A–K), header row 1 |

Keep this file as the **golden reference** for UAT with Miaolin/DigiWin. If the customer sends a newer template, add a new dated copy and update this note.

---

## 2) Column spec (11 columns, exact header text)

Order and labels must match DigiWin import expectations (Traditional Chinese headers as below).

| #   | Column (ZH) | English (for dev/PM)   | Suggested ERP source (Craveva)                                             | Notes                                                              |
| --- | ----------- | ---------------------- | -------------------------------------------------------------------------- | ------------------------------------------------------------------ |
| 1   | 訂單編號    | Order number           | `orders.order_number` (or formatted display number)                        | Stable per SO header; repeat on each line if one row per line item |
| 2   | 訂購日期    | Order date             | `orders.order_date`                                                        | Same date format as agreed with customer (confirm with DigiWin)    |
| 3   | 客戶名稱    | Customer name          | `users.name` / `client_details.company_name`                               | Display name                                                       |
| 4   | 客戶代號    | Customer code          | `client_details.client_code`                                               | **Key** — must match import master                                 |
| 5   | 產品品號    | Product part no. / SKU | `products.sku` or `order_items.sku`                                        | **Key** — must match product master                                |
| 6   | 產品名稱    | Product name           | `order_items.item_name` or `products.name`                                 |                                                                    |
| 7   | 包裝規格    | Packaging / pack spec  | Product UOM label or packaging field                                       | Confirm mapping with customer (may be unit conversion label)       |
| 8   | 數量        | Quantity               | `order_items.quantity`                                                     |                                                                    |
| 9   | 單價        | Unit price             | `order_items.unit_price`                                                   | Align decimals with DigiWin                                        |
| 10  | 預計交貨日  | Expected delivery date | SO/DO delivery date if available                                           | Confirm field on ERP side when export is built                     |
| 11  | 出貨倉別    | Shipping warehouse     | `warehouses.name` or `warehouses.code` via client default / line warehouse | Map from `default_warehouse_id` or outbound warehouse rule         |

**Row model:** Template sample is header-only; assume **one spreadsheet row per order line** (same `訂單編號` repeated for multiple SKUs) unless customer confirms otherwise.

---

## 3) Scope in Phase 1 / bi-directional sync

From `MAOLIN_BUSINESS.md` and `customer do.txt`:

- **Morning:** DigiWin → import into Craveva (master + transactions).
- **Evening:** Craveva → **export file in DigiWin template format** → customer imports into DigiWin.

This template defines the **evening sales order export** shape. Other domains (product, inventory, pricing) use different DigiWin files — see `MAOLIN_IMPORT_MAPPING.md` / `../docs/API_SYSTEM_REFERENCE.md`.

Related PM/customer SOP: `FUNC_LOGIC/CUSTOMER_API_REQUIREMENTS_EN.md`.

---

## 4) Implementation checklist (when dev starts export)

- [ ] Confirm date format (e.g. `YYYY-MM-DD` vs `YYYY/MM/DD`) with customer/DigiWin.
- [ ] Confirm whether 訂單編號 is Craveva `order_number` or a DigiWin-specific number series.
- [ ] Confirm 包裝規格 source (UOM conversion label vs fixed text).
- [ ] Confirm 預計交貨日 source (SO only vs linked DO).
- [ ] Confirm 出貨倉別: warehouse **name** vs **code** (inventory imports prefer `warehouse_code` elsewhere).
- [ ] Export only orders in agreed statuses (e.g. completed / approved — TBD with business).
- [ ] Delta vs full snapshot per evening run (TBD).
- [ ] Generate `.xlsx` with same sheet name/column order as sample (or `.csv` if DigiWin accepts — **verify with customer**).
- [ ] UAT: customer imports export into DigiWin without column errors; reconcile 5–10 sample orders.

---

## 5) How this was verified in repo

```bash
php scripts/peek_maolin_sheet.php "PROJECT MAOLIN/(Order Sample) 訂單寫入模板資料_260525.xlsx"
```

Header row (R1): 訂單編號, 訂購日期, 客戶名稱, 客戶代號, 產品品號, 產品名稱, 包裝規格, 數量, 單價, 預計交貨日, 出貨倉別.

---

## 6) Changelog

| Date       | Change                                                                                     |
| ---------- | ------------------------------------------------------------------------------------------ |
| 2026-05-28 | Initial note from customer sample + chat requirement (export must match DigiWin template). |
