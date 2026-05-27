# Product types & Production / BOM (operations guide)

**Audience:** Planners, master data, warehouse, Biomixing rollout  
**Updated:** 2026-05-27  
**Code:** `App\Enums\ProductType`, `products.type`, scopes `Product::forBomOutput()` / `forBomComponents()`

**Terminology (no abbreviations on customer docs):** [`PRODUCTION_TERMINOLOGY_CODE_VS_UI_VI.md`](./PRODUCTION_TERMINOLOGY_CODE_VS_UI_VI.md)

**See also:** [`FUNC_IMPROVE/PRODUCT_TYPE_BUYER_VS_INVENTORY_VI.md`](../FUNC_IMPROVE/PRODUCT_TYPE_BUYER_VS_INVENTORY_VI.md) · [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_EN.md`](../PROJECT%20BIOMIXING/PRODUCTION_MODULE_SOP_EN.md)

---

## 1. Why product type matters

Craveva **filters dropdowns** by `products.type`. Wrong type → the item **does not appear** on BOM / production order forms, or stock will not move correctly.

| Business question                           | Type to create                                   |
| ------------------------------------------- | ------------------------------------------------ |
| What do we sell / ship to customers?        | **Finished Goods** (`goods`)                     |
| What do we buy and consume in a formula?    | **Raw Material** (`raw_material`)                |
| Boxes, bags, labels, caps tracked in stock? | **Packaging** (`packaging`)                      |
| Intermediate mix used in another BOM line?  | **Semi Finished** (`semi_finished`)              |
| Non-stock service?                          | **Service** (`service`) — **not** for Production |

---

## 2. Product types (UI ↔ database)

| Products form label (typical)             | DB `type`       | In Production module                                                                         |
| ----------------------------------------- | --------------- | -------------------------------------------------------------------------------------------- |
| **Manufactured product** / Finished Goods | `goods`         | **BOM output** · finished goods on the order · inbound after posting finished goods to stock |
| **Raw Material**                          | `raw_material`  | **BOM component** · outbound when you deduct raw materials                                   |
| **Semi Finished**                         | `semi_finished` | **BOM component** (work in progress) · outbound when used                                    |
| **Packaging**                             | `packaging`     | **BOM component** · outbound when packing                                                    |
| **Service**                               | `service`       | **Excluded** from BOM / stock                                                                |

**UI note:** Type `goods` is usually labeled **Manufactured product** on the product form, not the word “Goods”.

---

## 3. System rules (BOM & production orders)

| Role                                    | Allowed types                                | Where                                            |
| --------------------------------------- | -------------------------------------------- | ------------------------------------------------ |
| **BOM output**                          | `goods` only                                 | Manufactured product dropdown on BOM create/edit |
| **Finished goods on order** (BOM-first) | `goods` only                                 | Filled automatically from selected BOM           |
| **BOM components**                      | `raw_material`, `semi_finished`, `packaging` | Component lines (grouped by type in UI)          |
| **Service**                             | None                                         | Not selectable on BOM                            |

Requests: `StoreProductionBomRequest` / `UpdateProductionBomRequest` enforce type and “component ≠ output”.

---

## 4. Recommended master data order (before BOM)

1. **Raw materials** — powders, sugar, flavours, …
2. **Packaging** (if tracked) — cartons, pouches, bottles, …
3. **Semi finished** (only if you have an intermediate step).
4. **Finished goods (`goods`)** — sellable SKU / factory output.
5. **Stock** — `Add Inventory` into the correct **warehouse**.
6. **Bill of materials** — finished goods output + component lines + quantities **per one finished unit**.
7. **Production order** — select BOM first (Biomixing default).

---

## 5. Biomixing single-step pilot (raw materials → finished goods)

Many pilots **do not** use `semi_finished`:

- BOM lines are **raw materials** (+ **packaging** if needed).
- Output remains **goods** (manufactured product).

You must still create ingredients as **`raw_material`** — do not create them as finished goods and expect them in the component dropdown.

---

## 6. Common mistakes

| Symptom                                            | Cause                                               | Fix                                                                                               |
| -------------------------------------------------- | --------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Product missing from BOM **output** list           | Type is not `goods`                                 | Set type to **Manufactured product** / Finished Goods                                             |
| Material missing from BOM **component** list       | Type is `goods` or `service`                        | Recreate as **Raw Material** (or Packaging / Semi Finished)                                       |
| BOM save error “component must differ from output” | Same product on both sides                          | Separate finished goods and raw material master records                                           |
| Release / deduct shows shortage                    | No stock in **raw material warehouse** on the order | `Add Inventory` to that warehouse                                                                 |
| Confused order qty (2) with BOM line qty (10)      | Different concepts                                  | Order qty = how many **finished units** to make; BOM qty = raw material **per one finished unit** |

---

## 7. Related docs

- Full SOP: [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_EN.md`](../PROJECT%20BIOMIXING/PRODUCTION_MODULE_SOP_EN.md)
- Stock & reserve: [`PRODUCTION_OPERATIONS_LIVE_EN.md`](./PRODUCTION_OPERATIONS_LIVE_EN.md)
