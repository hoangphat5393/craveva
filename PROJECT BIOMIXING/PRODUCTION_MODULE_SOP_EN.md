# Production Module — Standard Operating Procedure (Non-Technical)

**Audience:** Factory supervisor, production planner, warehouse staff, sales support  
**System:** Craveva ERP — Production module  
**Version:** 2026-05-27  
**Purpose:** End-to-end guide to plan manufacturing, reserve materials, run batches, update stock, then sell or ship.

---

## Status overview

| Status          | Meaning                                                                                             |
| --------------- | --------------------------------------------------------------------------------------------------- |
| **Draft**       | Planning only. The order can be edited. Raw materials are **not** reserved yet.                     |
| **Released**    | Production is committed. The system **reserves** raw materials in the raw material warehouse.       |
| **In progress** | Raw materials have been **deducted** from warehouse (at least one production batch posted).         |
| **Completed**   | Finished goods have been **received** into the manufactured product warehouse (all batches posted). |
| **Cancelled**   | Order stopped (rules in section 12).                                                                |

---

## 1. Create finished product

**Go to:** `Operations → Products`

**Steps:**

- Add the **finished good** (what you manufacture).
- Set **unit** (Pcs, Box, Kg, etc.) and **SKU** if needed.
- Use the correct **product type** so the item can be selected as bill of materials output (finished goods).
- Save.

**Examples:** Oldtown White Coffee · finished chocolate bar.

---

## 2. Create raw materials

**Go to:** `Operations → Products`

**Steps:**

- Add each ingredient and packaging item (coffee powder, sugar, box, chocolate, etc.).
- Mark as **inventory / purchasable** where applicable.
- Set units consistently with how you buy and consume (g, kg, pcs).
- Save.

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

**Steps:**

- Select the **finished product** (output).
- Add each **raw material** line with quantity per unit of output.
- Add **waste %** on lines if needed (included in total material calculation).
- Save the bill of materials.

**Example — 1 box of coffee:**

| Material      | Quantity |
| ------------- | -------- |
| Coffee powder | 10 g     |
| Sugar         | 5 g      |
| Packaging box | 1 pc     |

**Rule:** Without a bill of materials, the production order cannot calculate or consume materials correctly.

---

## 5. Create production order

**Go to:** `Production → Production Orders` → **New production order**

**Steps:**

- Select **finished product** and **bill of materials**.
- Enter **planned quantity**.
- Select **raw material warehouse** (where ingredients are taken from).
- Select **manufactured product warehouse** (where completed product is received).
- Optionally link a **Sales Order**.
- Save as **Draft** first.

**Purpose:** This is the manufacturing work order. Draft = planning; quantity, bill of materials, and warehouses can still be changed.

**Optional:** From a **Sales Order**, use **Create production order** to prefill sales link and quantities.

---

## 6. Check material availability

### A) On the production order (detail page)

- Open the order and review the **total raw materials** table.
- Compare **required** vs **available** in the raw material warehouse.
- If insufficient, a shortage warning is shown.

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

- Saves a **bill of materials snapshot** for this order.
- Checks **available** = on-hand minus already **reserved** (delivery orders, other production orders).
- If not enough → **Release is blocked**.
- If enough → **reserves** raw materials in the raw material warehouse (hold only — not deducted yet).

**Business meaning:** “We commit to run this job.”

**Recommended:** Only supervisor / planner with permission releases orders.

**Note:** **Draft** orders do **not** reserve production materials.

---

## 8. Production batch — shop floor workflow

**Go to:** Production order → **Batches** → create or open a batch

Complete steps **in order**:

| Step | User action                                                                                    | Stock effect                                                                  |
| ---- | ---------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| 1    | Open **production batch** (created at release; planned RM lines auto-filled from BOM snapshot) | Inserts `production_batch_consumptions` only — no stock move                  |
| 2    | **Assign warehouse batch/lot** per raw material line                                           | No extra reserve (reserve was at Release)                                     |
| 3    | **Deduct raw materials**                                                                       | **Raw material quantity decreases**                                           |
| 4    | Enter **finished goods output** (qty, manufactured product batch number)                       | —                                                                             |
| 5    | **Variance approval** (if company policy requires)                                             | Manager approves before manufactured product posting                          |
| 6    | **Post finished goods receipt**                                                                | **Manufactured product quantity increases** in manufactured product warehouse |

_Restore manual “Generate planned raw materials” button / checklist step: `FUNC_LOGIC/PRODUCTION_BATCH_STEP1_RESTORE_VI.md`._

**Order status after posting:**

- **In progress** — after raw materials are deducted on all required batches.
- **Completed** — after all finished goods are posted.

**Important:** Clicking **Completed** alone does **not** move stock. Stock moves at **Deduct raw materials** and **Post finished goods**.

---

## 9. Traceability (optional)

**Go to:** Batch screen → **Trace**

- Links between production batch and warehouse batches (raw materials → production → manufactured product).
- Use for audits, recalls, or customer questions.

---

## 10. Finished goods → sales & delivery

After **Post finished goods**, stock is in the **manufactured product warehouse**. It can be used for:

- Sales orders
- Delivery orders (confirm / ship — may reserve or deduct manufactured product stock per company settings)
- Invoices (financial; usually does not change stock again)

**Optional:** Shipping may be blocked until the linked production order is **Completed** (quality lock — if enabled).

---

## 11. Cancel an order

| Current status                                                    | Can cancel?                                    |
| ----------------------------------------------------------------- | ---------------------------------------------- |
| **Draft**                                                         | Yes                                            |
| **Released** (no raw materials / manufactured product posted yet) | Yes — system **releases** material reservation |
| **In progress** or **Completed**                                  | **No** — stock already posted                  |

---

## 12. Recommended roles

| Action                                                   | Typical role           |
| -------------------------------------------------------- | ---------------------- |
| Create bill of materials / draft order                   | Planner                |
| Release order                                            | Supervisor             |
| Batch — deduct raw materials / post manufactured product | Production / warehouse |
| Variance approval                                        | Manager                |
| Material shortage / purchase order                       | Planner / buyer        |

---

## 13. Common mistakes

1. Releasing without enough stock — Release fails; fix stock first.
2. Skipping batch steps — No stock movement.
3. Wrong raw material or manufactured product warehouse on the order.
4. Bill of materials line unit not mapped to product base unit — Set up unit conversions.
5. Expecting **Draft** to reserve stock — Only **Released** reserves.

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

- [`FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_EN.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_EN.md)
- [`PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd`](./PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd)
