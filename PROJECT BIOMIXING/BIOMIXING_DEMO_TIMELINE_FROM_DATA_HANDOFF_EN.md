# Demo timeline — sample files → customer demo (English)

**What this covers:** Working **business days** from receipt of a **complete data package** to a **scripted Hub/staging demo** (import + mapping + rehearsal). **Not** Production/MES build — see `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` / `BIOMIXING_PRODUCTION_PROTOTYPE_PLAN_VI.md`.

**Hub behaviour while rehearsing (2026):** `BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md` §2–3 and `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` (SO / Sales DO / batch on lines).

**File list:** `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md` (**A1–A6** CORE; **B** / flowchart optional).

**Updated:** 2026-04 · Planning estimate only, not a contractual SLA.

---

## Clock start (T0)

**T0** = Craveva confirms **full CORE**: **A1–A5** + **A6 Story Pack** (S1–S7). Partial drops do **not** start the committed window.

**T1** = Demo slot offered (data imported/mapped + script rehearsed).

---

## Assumptions

~**1 dev** + **0.5 QA** spot-check; Hub/staging with Warehouse, Purchase, Orders, Projects; customer answers data questions in **~1 business day** when unclear.

---

## Scenarios (business days, T0 → T1)

| Scenario | Data quality                                                                                                                                                          | Typical days                                        |
| -------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------- |
| **A**    | Clean: columns match checklist, stable SKU/warehouse keys, no duplicate-SKU mess, Story Pack matches masters                                                          | **3–5**                                             |
| **B**    | Minor issues: typos, a few orphans, date format / spelling drift, **one** fix round with customer                                                                     | **5–9**                                             |
| **C**    | Messy: duplicate/conflicting SKUs, no join keys, inventory ≠ masters, batch/expiry mismatch, Story Pack contradicts A2/A3, **slow** replies or **multiple** resubmits | **10–18** (~**2–4** calendar weeks); can run longer |

**Band assignment:** we state **A / B / C** within **~1 business day** of receiving the full CORE package.

**If data never joins cleanly:** pause fixed dates; optional **synthetic** sample dataset for a predictable demo (different story).

---

## Faster prep (customer)

Send **A1–A6 together**; one **sku_code** per product; same codes across **all** files; one **fast** data contact.

---

## Related docs

`2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md` · `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` · `BIOMIXING_PRODUCTION_PROTOTYPE_PLAN_VI.md`

---

## At-a-glance summary (business days)

- **T0:** full **A1–A6** received and acknowledged.
- **Clean (A):** **3–5** · **Minor fixes (B):** **5–9** · **Messy (C):** **10–18+** (often **2–4** calendar weeks).
- **Not included:** **Production module** build — separate timeline.

---

## Committed headline (single number per band — **max** business days)

Use these as the **ceiling** we plan against after T0 (not a sum; pick **one** row that matches data quality).

| Band  | Meaning             | **Chốt — max working days** to demo slot     |
| ----- | ------------------- | -------------------------------------------- |
| **A** | Clean data          | **5**                                        |
| **B** | Minor fixes         | **9**                                        |
| **C** | Messy / many rounds | **18** (longer if customer resubmits slowly) |

**Typical (not commitment):** A often **~3–4**, B **~6–7**, C **~10–14** when the customer responds within ~1 business day.
