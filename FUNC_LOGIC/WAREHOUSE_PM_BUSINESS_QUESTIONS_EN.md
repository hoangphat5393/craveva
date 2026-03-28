# PM questionnaire — Warehouse & sales business rules (Craveva ↔ ERP / Miaolin)

**Purpose:** Engineering needs **clear business decisions** from PM so configuration and testing are correct. Warehouse, sales, and ERP sync are **easy to get wrong**; without alignment we risk **incorrect stock deductions** or **double-counting with another system**.

**How to use:** Copy the tables below to PM (email/Slack), or use a 20-minute working session to fill them in.

---

## A) System of record (“who owns” the data?)

| #   | Question                                                                                                                                                      | PM response (bullets or one sentence) |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------- |
| A1  | Where are **sales orders** **primarily created**: Craveva only, another ERP only (e.g. Dingxin), or both?                                                     |                                       |
| A2  | Where are **sales invoices** **created and finalized**: Craveva, another ERP, or both?                                                                        |                                       |
| A3  | For **authoritative stock** used to decide whether you can sell: which system is the **source of truth** — Craveva or the other ERP?                          |                                       |
| A4  | Should Craveva **deduct stock itself** when an invoice is saved **in Craveva**, or only **display stock synced from the other ERP** (which already deducted)? |                                       |

---

## B) When to deduct stock (if Craveva still posts movements)

| #   | Question                                                                                                                                    | PM choice / notes |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------- | ----------------- |
| B1  | Is deducting stock on **invoice save** (non-draft) correct, or must we wait until **paid** / **shipped** / **confirmed after picking**?     |                   |
| B2  | If we later add a separate **“confirm shipment / outbound”** step (after picking): is it **required** now, or **not needed** in this phase? |                   |

---

## C) Choosing the shipping warehouse

| #   | Question                                                                                                                                                                                             | PM response |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| C1  | Is **client default warehouse → company default warehouse** enough for outbound, or not?                                                                                                             |             |
| C2  | Is **per-line warehouse selection** on one invoice (one order, multiple warehouses) **mandatory**?                                                                                                   |             |
| C3  | When one warehouse **does not have enough stock**: the standard process is **split into multiple invoices** (as in Dingxin) — must Craveva **mandatorily** support the same on the UI in this phase? |             |

---

## D) Edits, voids, returns

| #   | Question                                                                                                                                   | PM response |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------ | ----------- |
| D1  | After an invoice is **finalized** (no longer editable): how do we fix mistakes — **void**, **return note**, or **only in the other ERP**?  |             |
| D2  | If goods are **already shipped**: only **sales return** applies — should that flow exist **only in the other ERP** or **also in Craveva**? |             |

---

## E) Sync with the other ERP (if applicable)

| #   | Question                                                                                                                     | PM response |
| --- | ---------------------------------------------------------------------------------------------------------------------------- | ----------- |
| E1  | Craveva vs other ERP: **one-way** (Craveva → ERP) or **two-way** (ERP also updates stock back into Craveva)?                 |             |
| E2  | Desired sync **frequency / trigger**: **real-time**, **batch**, **end of day**? (You may answer “TBD — needs IT / partner”.) |             |

---

## F) UAT sign-off scope (Miaolin)

| #   | Question                                                                                                                                                               | PM choice                                                                |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| F1  | For this sign-off: **operational warehouse only** (manual in/out, transfers, purchase receipts, etc.) or **must include** **sales-driven stock deduction in Craveva**? | ☐ Operational warehouse only &emsp; ☐ Must include sales stock deduction |
| F2  | Target date for UAT evidence on **staging**?                                                                                                                           |                                                                          |

---

## Short notes for PM (optional)

- Craveva already has a **v1** option to deduct stock on invoice save (feature-flag / config). If PM decides Craveva should **not** deduct and only **show synced stock from the other ERP**, engineering will align config and scope — **we cannot guess this for you**.
- If **both** Craveva and the other ERP deduct stock for the **same** physical movement without a written rule, we risk **negative or inconsistent stock**.

---

**Prepared by:** …  
**Date:** …

---

## Appendix — PM alignment (Miaolin / Dingxin flow, Scope B)

**Source:** PM / Miaolin — align Craveva Scope B with Dingxin (we do **not** copy all Dingxin logic in code, but we **map states** and **avoid double-counting**).

### Appendix B — Miaolin operating model (from internal team)

**Context:** Customer **Miaolin** uses **Dingxin** as the **primary** system for day-to-day sales (invoices, picking, confirmation, etc.). Each morning around **6:00**, data is **imported into Craveva**; the team then **exports from Craveva and imports into the customer’s Dingxin**.

**Does this alone answer all questions A–F?** **No** — but it **partially clarifies**:

| Topic                                           | After knowing “6am import + export → Dingxin”                                                                                                                                                                                                                                                                                                          |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **E1 (one-way / two-way)**                      | **Partial:** There is a **scheduled** Craveva → Dingxin path (export/import). The **6am import into Craveva** means data also flows **into** Craveva (please clarify **source**: Miaolin file, another warehouse system, consolidated feed, etc.). Integration may be **batch / scheduled**, not necessarily real-time.                                |
| **E2 (frequency)**                              | **Partial:** There is a **6am** import cadence; **how often** we export to Dingxin (daily? after approval?) is **not** specified — PM should confirm.                                                                                                                                                                                                  |
| **A1 (where orders live)**                      | **Partial:** There is **processing in Craveva** after import. Still unclear whether orders **originate** only in Craveva or are copied from elsewhere before import.                                                                                                                                                                                   |
| **A2 (where invoices live)**                    | **Open:** Export to Dingxin might be **order/lines** for them to invoice, or **already invoices** — **one sentence from PM** would close this.                                                                                                                                                                                                         |
| **A3 / A4 (master stock & deduct in Craveva?)** | **Still critical:** Dingxin is the **main** system → **likely** master for on-hand **after save/confirm** is Dingxin. Craveva risks **double-count** if we also `recordOutbound` for the same batch. **PM must confirm:** should Craveva only **reserve / display**, or also post **outbound movements** and reconcile with **reverse sync** (if any)? |

**One-line summary:** The **6am + export → Dingxin** pipeline explains **integration shape**, but it **does not replace** full answers for **A2–A4** and **E2**; please still complete the tables in sections **A** and **E** above.

### Appendix C — Craveva SO only (no stock deduct) + Dingxin invoice + morning import triggers deduct (how it works & questionnaire impact)

**Model from the team:** In **Craveva**, users only create **sales orders (SO)** — **no** inventory deduction. In **Dingxin**, **SO + sales invoice** follow the real operational flow. Each **morning**, a file from Dingxin is **imported into Craveva** — **at import time** the system **deducts stock** (or creates the invoice/movement equivalent). You need a **standard SO (and possibly PO) flow** so Miaolin has a **predictable import template**.

**Why “no deduct on SO” but “deduct on import” is valid**

- An **SO** is often only a **commitment / plan to sell** — not a physical issue. If saving an SO does **not** call `stock_movements`, **on-hand in Craveva stays unchanged**.
- **Dingxin** is where the **sale is finalized** (invoice, picking, confirmation, etc.). The export file carries **finalized lines** (e.g. invoice no., SKU, qty, warehouse, date, state eligible for outbound…).
- The **import job** reads the file → **matches** to the Craveva SO (by order/line keys) or creates invoice rows → **only then** runs stock logic (**once per line**, with **idempotency** so re-import does not double-deduct).

**What “standard SO/PO for import” means**  
Agreed **columns + keys** (SO no., line, SKU, warehouse, qty, Dingxin invoice ref…) so the morning file **maps** to Craveva documents. **PO** usually covers **purchasing / goods receipt** — similar file conventions if Miaolin imports purchases from another system.

**Risk PM must confirm in one sentence:** If **Dingxin already deducts** the **same physical stock** and Craveva import **also deducts** for the same shipment → **double-count**. Clarify: is Dingxin’s stock the **operational warehouse book** or only **financial**? If **one physical pool**, typically **only one system posts movements**; the other **mirrors** or stores **ending balances**.

**Does this fully answer sections A–F?**  
**Much closer** for **A1–A2, A4 (SO does not deduct; deduct on import), B1 (deduct timing = import batch), E (batch Dingxin → Craveva for finalized sales)**. **Still need** PM sign-off on **A3** (source of truth for sellable stock), **avoiding double deduct with Dingxin**, **C1–C3**, **D1–D2**, **F1–F2**, and **file layout + schedule** (E2).

### Mapping to the questionnaire above

| Topic               | Answered?                      | PM / alignment summary                                                                                                                                                                                                                                                                                       |
| ------------------- | ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **A1–A4**           | **Largely** (+ **Appendix C**) | **A1:** SO mainly in **Craveva** (no deduct). **A2:** Invoices primarily **Dingxin**; Craveva receives via **import**. **A4:** Craveva **does not** deduct on SO save; deduct when **import** applies. **A3** (stock master): **still need** explicit answer — see double-count risk in **Appendix C**.      |
| **A4** specifically | **Directional** (Appendix C)   | If “deduct on import” is correct: **disable** stock on SO; import creates/updates invoice or shipment then posts movement. **Still** confirm with PM whether Dingxin deducts **the same pool** — if yes, define **single deductor** or **mirror** rules.                                                     |
| **B1–B2**           | **Partial / two scenarios**    | **Import scenario (C):** deduct in Craveva = **when the import job** applies Dingxin lines (batch), **not** on SO save. **Full Dingxin lifecycle:** Saved vs Confirmed — if the **file carries states**, map to reserve vs outbound. **B2:** still ask PM if Craveva needs a separate confirm-outbound step. |
| **C1**              | **Open / refine**              | PM: warehouse chosen **manually by sales** at order→invoice; default **warehouse closest to customer**. Differs from current v1 rule (client default → company default). Need **UI/rule**: **user-selected warehouse first** + optional “closest” fallback if defined in Craveva.                            |
| **C2**              | **Indirect yes**               | Not mandatory **one invoice, many warehouses**; **split invoices** by warehouse/batch when stock is insufficient.                                                                                                                                                                                            |
| **C3**              | **Yes**                        | Standard Dingxin flow: **split invoices** — Craveva should **support** (at least multiple invoices / lines with clear warehouse); PM has not locked “mandatory UI in this phase”.                                                                                                                            |
| **D1**              | **Partial**                    | Saved **not** Confirmed → **unsave** / adjust reservation. **Confirmed** → **no edit**; void vs return — **Miaolin not 100% final**; confirm per Dingxin standard.                                                                                                                                           |
| **D2**              | **Yes**                        | After shipped → **Sales Return**; implementation must **avoid double adjustment** on return/void — follow **Dingxin state changes**.                                                                                                                                                                         |
| **E1–E2**           | **Partial → clearer with C**   | **E1:** **Dingxin → Craveva** file (morning) to **finalize deduct in Craveva**; optional **Craveva → Dingxin** for SO/template — PM should sketch **one-way vs two-way**. **E2:** Morning cadence known; still need **file format**, **run time**, **error / re-import** handling.                           |
| **F1–F2**           | **Not yet**                    | Not in the short PM note — keep UAT checklist / staging target date as separate items.                                                                                                                                                                                                                       |

### Gap vs current Craveva Scope B v1 code

- **v1 today:** Single step — non-draft invoice → **outbound** (`recordOutbound`) i.e. **on-hand reduced immediately**; **no** split between **reservation** and **confirm/shipped**.
- **Per PM target:** Two layers: (1) **save** → **reserve/available** only; (2) **after picking, confirm** → **true outbound** (shipped movement).  
  → **Next engineering step:** invoice (or shipment) **states** in Craveva mapped to Dingxin **Saved / Confirmed**, plus **StockReservationService** (or equivalent) for (1), and **`recordOutbound` only** at (2); reversals for unsave vs return accordingly.

### Short follow-up questions for PM

1. For the **same physical shipment**: does **Dingxin already deduct stock**? If **yes**, should Craveva import **only mirror** or **also post movements** — how do we **avoid double deduct**?
2. Does the morning file contain **one row = ready for outbound**, or also **reserved-only** rows — how many **steps** should Craveva map?

---

_Update this appendix when Miaolin finalizes void vs return (D1), full file/sync spec (E), and master stock / Dingxin ↔ Craveva double-count rules (A3 + Appendix C)._
