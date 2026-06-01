# Production Module Workflow (Non-Technical SOP)

**Audience:** Factory supervisor, production planner, warehouse staff, sales support  
**System:** Craveva ERP — Production module  
**Version:** 2026-05-27  
**Purpose:** End-to-end guide to plan manufacturing (bill of materials first), reserve materials at release, run batches, update stock, then sell or ship.

---

## Status overview

| Status          | Meaning                                                                                                                                                         |
| --------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Draft**       | Planning only. The order can be edited. Raw materials are **not** reserved yet.                                                                                 |
| **Released**    | Production is committed. Bill of materials is **frozen** on the order; raw materials are **reserved**. First production batch is usually created automatically. |
| **In progress** | Raw materials have been **deducted** from warehouse (at least one batch posted).                                                                                |
| **Completed**   | Finished goods have been **received** into the manufactured product warehouse (all batches posted).                                                             |
| **Cancelled**   | Order stopped (rules in section 11).                                                                                                                            |

---

## 0. Product types — read before creating a BOM (required)

New users often add a “product” without choosing the right **Product type**. In Craveva, `products.type` controls where the item appears in Production.

| Products form label      | System value    | Used for                                                                            |
| ------------------------ | --------------- | ----------------------------------------------------------------------------------- |
| **Manufactured product** | `goods`         | **BOM output** · finished goods on the production order · sales / delivery          |
| **Raw Material**         | `raw_material`  | **BOM component** · purchase orders · stock deduction when you deduct raw materials |
| **Packaging**            | `packaging`     | **BOM component** (cartons, pouches, …)                                             |
| **Semi Finished**        | `semi_finished` | **BOM component** when you have a WIP step                                          |
| **Service**              | `service`       | **Not** on BOM / no stock                                                           |

**Quick rules:**

- To build a BOM you need at least one **`goods`** (manufactured product) and at least one **`raw_material`** (ingredient).
- Do **not** create ingredients as **Manufactured product** and expect them in the BOM component list — they will **not** appear.
- Single-step pilot (raw materials → finished goods only): you may skip `semi_finished`; still use `packaging` if you track packaging stock.

**Detail + diagram:** [`FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_EN.md`](../FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_EN.md) · [`FUNC_LOGIC/PRODUCTION_TERMINOLOGY_CODE_VS_UI_VI.md`](../FUNC_LOGIC/PRODUCTION_TERMINOLOGY_CODE_VS_UI_VI.md) (code `fg_`/`rm_` vs labels — **do not use FG/RM in customer text**)

---

## 1. Create finished goods — Manufactured product (master data)

**Go to:** `Operations → Products` → **Add Product**

| Field            | Recommended value                                                                                      |
| ---------------- | ------------------------------------------------------------------------------------------------------ |
| **Product type** | **Manufactured product** (`goods`) — not Raw Material / Service                                        |
| **Name / SKU**   | Sellable name and internal code                                                                        |
| **Unit**         | How you manufacture and sell (box, bottle, kg, …)                                                      |
| **Purchasable**  | Usually **off** (you manufacture finished goods; you do not buy them from a vendor like raw materials) |

**After save:** the item appears in the BOM **output** dropdown at `Production → Bill of Materials`.

**Examples:** 3-in-1 coffee box · 6-pack cake box.

---

## 2. Create raw materials & packaging (master data)

**Go to:** `Operations → Products` → **Add Product** (one record per SKU)

| Formula item                                 | Product type to select |
| -------------------------------------------- | ---------------------- |
| Powders, sugar, milk, flavours, water, …     | **Raw Material**       |
| Cartons, pouches, labels, caps, …            | **Packaging**          |
| Pre-mixed bulk used in a later step (if any) | **Semi Finished**      |

| Field             | Notes                                                                      |
| ----------------- | -------------------------------------------------------------------------- |
| **Unit**          | Match how you buy and how you enter BOM quantities (g, kg, pcs)            |
| **Purchasable**   | Enable if you buy via **Purchase Order**                                   |
| **Opening stock** | Hint only — you must **Add Inventory** to a real **warehouse** (section 3) |

**After save:** items appear in the BOM **component** dropdown (grouped by Raw Material / Semi Finished / Packaging).

**Examples — raw materials:** Arabica coffee powder · white sugar. **Examples — packaging:** 20-sachet carton.

---

## 3. Add inventory stock

**Go to:** `Operations → Inventory` and/or `Warehouse`

**Ways to add stock:**

- **Manual / opening:** `Operations → Inventory → Add Inventory` — select warehouse, product, quantity.
- **Purchase:** Purchase Order → goods receipt → stock increases in the chosen warehouse.

**Purpose:** The system must know **on-hand** quantity in the **raw material warehouse** used on the production order.

**Important:** Entering “opening stock” on the product form alone may **not** post to a physical warehouse until you add inventory to a **specific warehouse**.

---

## 4. Create bill of materials

**Go to:** `Production → Bill of Materials`

**Prerequisites:** Master data sections **0–2** (correct product types) and stock in section **3** if you plan to release soon.

**Steps:**

1. **Manufactured product (output):** only `goods` items from section 1 appear here.
2. **Components:** add lines — dropdown lists only `raw_material`, `semi_finished`, `packaging` from section 2.
3. Enter **quantity consumed per 1 unit of finished goods** (not the production order planned quantity).
4. Save the bill of materials (at least one component line).

**Example — 1 box of coffee:**

| Material      | Quantity |
| ------------- | -------- |
| Coffee powder | 10 g     |
| Sugar         | 5 g      |
| Packaging box | 1 pc     |

**Rule:** Production orders **require** a bill of materials with lines before **Release** (current Biomixing setup).

---

## 5. Create production order (bill of materials first)

**Go to:** `Production → Production Orders` → **New production order**

**Steps:**

1. Select **bill of materials** from the dropdown (placeholder — the system does **not** auto-pick the first bill of materials).
2. The **manufactured product** is filled in automatically from the bill of materials you chose.
3. Enter **planned quantity**.
4. Select **raw material warehouse** and **manufactured product warehouse**.
5. Optionally link a **Sales Order**.
6. Review the **material requirements preview** on the form (updates when you change bill of materials, quantity, or raw material warehouse). This preview uses the **master** bill of materials; the shop floor uses the **frozen** copy after release.
7. Save as **Draft**.

**Purpose:** Draft = planning; you can still change bill of materials, quantity, and warehouses.

**Optional:** From a **Sales Order**, use **Create production order** to prefill sales link and quantities.

**You cannot release** if no bill of materials is selected or the bill of materials has no lines.

---

## 6. Check material availability

### A) On the production order (detail page)

- Open the order and review **total raw materials** (from the order bill of materials / snapshot after release).
- Compare **required** vs **available** in the raw material warehouse.

### B) Across many orders (planning / purchasing)

**Go to:** `Production → Production Orders` → **Material shortage summary**

| Filter                               | Use when                                                          |
| ------------------------------------ | ----------------------------------------------------------------- |
| **Released + In progress** (default) | Committed jobs — stock already reserved for other released orders |
| **Draft**                            | Early planning / what to buy before release                       |
| **All eligible**                     | Draft + Released + In progress                                    |

**If not enough material:**

- Receive more stock (purchase order / goods receipt / Add Inventory), **or**
- Reduce planned quantity, **or**
- Do not release until stock arrives.

---

## 7. Release production order

**Go to:** Production order detail → **Release** (Draft → **Released**)

**What the system does:**

- Saves a **bill of materials snapshot** on the order (frozen for this job).
- Checks **available** = on-hand minus already **reserved** (delivery orders, other production orders).
- If not enough → **Release is blocked**.
- If enough → **reserves** raw materials in the raw material warehouse (hold only — not deducted yet).
- Creates the **first production batch** if none exists.
- **Automatically** creates **planned raw material lines** on that batch from the order snapshot (no separate “generate planned lines” button in the default setup).

**Business meaning:** “We commit to run this job.”

**Recommended:** Only supervisor / planner with permission releases orders.

**Note:** **Draft** orders do **not** reserve production materials.

---

## 8. Production batch — shop floor workflow (4 steps)

**Go to:** Production order → **Batches** → open the batch (often already created at release)

The on-screen checklist shows **four steps** (numbered 1–4). There is **no** separate checklist step to “create planned raw material lines” — the system already inserted them.

| Step | What you do on screen                                        | Stock effect                                                                  |
| ---- | ------------------------------------------------------------ | ----------------------------------------------------------------------------- |
| 1    | **Assign warehouse batch/lot** on each raw material line     | No stock move; reserve was created at Release                                 |
| 2    | **Deduct raw materials**                                     | **Raw material quantity decreases**                                           |
| 3    | **Add manufactured product output** (quantity, batch number) | Planning only until step 4                                                    |
| 4    | **Post finished goods receipt**                              | **Manufactured product quantity increases** in manufactured product warehouse |

**After posting:**

- Order **In progress** — when raw materials are deducted (per batch rules).
- Order **Completed** — when all batches have posted finished goods.

**Important:**

- Clicking **Completed** on the order alone does **not** move stock.
- You **cannot** manually add extra raw material lines on the batch in the default setup (lines come from the snapshot only).
- Opening an older batch with no lines yet triggers the same **automatic** planned lines if the order already has a snapshot.

**Optional (admin):** Variance approval may be required before posting finished goods if quantity is outside policy (`Settings → Production`).

**Optional:** **Print label slip** on the batch screen for shop-floor labels.

---

## 9. Traceability (optional)

**Go to:** Batch screen → **Trace**

- Links production batch ↔ warehouse batches (raw materials → production → manufactured product).
- Use for audits, recalls, or customer questions.

---

## 10. Manufactured product → sales & delivery

After **Post finished goods**, stock is in the **manufactured product warehouse**. It can be used for:

- Sales orders
- Delivery orders (confirm / ship — may reserve or deduct stock per company settings)
- Invoices (financial; usually does not change stock again)

**Optional (often enabled):** Delivery shipping may be blocked until linked production orders are **Completed** (quality lock).

---

## 11. Cancel an order

| Current status                                                    | Can cancel?                                    |
| ----------------------------------------------------------------- | ---------------------------------------------- |
| **Draft**                                                         | Yes                                            |
| **Released** (no raw materials / manufactured product posted yet) | Yes — system **releases** material reservation |
| **In progress** or **Completed**                                  | **No** — stock already posted                  |

---

## 12. Recommended roles

| Action                                                               | Typical role           |
| -------------------------------------------------------------------- | ---------------------- |
| Create bill of materials / draft order                               | Planner                |
| Release order                                                        | Supervisor             |
| Batch — assign lots, deduct raw materials, post manufactured product | Production / warehouse |
| Variance approval                                                    | Manager                |
| Material shortage / purchase order                                   | Planner / buyer        |

---

## 13. Common mistakes

1. Wrong **product type** — ingredient created as Manufactured product → missing from BOM component list (see section 0).
2. Releasing without enough stock — Release fails; fix stock first.
3. Releasing without selecting a bill of materials (or empty bill of materials) — Release fails.
4. Expecting to pick manufactured product first — choose **bill of materials** first; product follows.
5. Skipping batch steps 1–4 — No stock movement.
6. Wrong raw material or manufactured product warehouse on the order.
7. Confusing **form preview** (master bill of materials) with **batch lines** (snapshot at release).
8. Expecting **Draft** to reserve stock — only **Released** reserves.
9. Confusing **BOM line quantity** (per one finished unit) with **order planned quantity** (how many finished units to make).

---

## 14. Quick menu reference

| Task                                | Menu path                                                  |
| ----------------------------------- | ---------------------------------------------------------- |
| Products                            | Operations → Products                                      |
| Add stock                           | Operations → Inventory · Warehouse                         |
| Bill of materials                   | Production → Bill of Materials                             |
| Work order                          | Production → Production Orders                             |
| Shortage report                     | Production → Production Orders → Material shortage summary |
| Manufactured product policy (admin) | Settings → Production                                      |

---

## Related technical docs (internal)

- [`FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_EN.md`](../FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_EN.md) — product types & BOM
- [`FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_EN.md`](../FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_EN.md) — buying vs inventory (§1)
- [`FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_EN.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_EN.md) §7
- [`PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd`](./PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd)
