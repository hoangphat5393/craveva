# Bi-Directional Data Sync — What HUB Need

**Goal:** Keep **HUB system** and **Miaolin** in sync **both ways**, so AI can read complete and correct ERP data.

---

## How sync works (2 directions)

**Our system:** Craveva ERP (HUB)  
**Customer system:** e.g. **DigiWin** (Miaolin) — or another ERP they use; DigiWin is the usual case.

```
DigiWin (customer)  ──────────►  Craveva ERP (HUB)
DigiWin (customer)  ◄──────────  Craveva ERP (HUB)
```

Both sides must use the **same keys** for clients, products, and warehouses so records match.

---

## What data we sync (both directions)

| Data                        | Why it matters                                      |
| --------------------------- | --------------------------------------------------- |
| **Clients**                 | AI and orders must resolve the right client         |
| **Products**                | AI must use correct SKU and product name            |
| **Warehouses**              | Stock and shipping use the right location           |
| **Inventory**               | AI and sales need accurate stock                    |
| **Pricing**                 | AI must use the agreed selling price                |
| **Orders**                  | New/updated orders stay aligned on both sides       |
| **Order history** (if used) | Reporting and reconciliation match DigiWin/your ERP |

---

## What we need from MIAOLIN to start

### 1) People

- Business contact (confirm process)
- Data contact (confirm files and IDs)
- Technical contact (API / testing)

### 2) Data (initial load + ongoing sync)

- Client list — unique **Client code** (`client_code`)
- Product list — unique **SKU**
- Warehouse list — **warehouse code** + name
- Inventory by warehouse (and batch if you use batches)
- Pricing (standard + Clients/tier price if applicable)
- **5–10 sample orders** for testing

### 3) Simple rules (please confirm)

| Rule         | Your side                           | ERP side |
| ------------ | ----------------------------------- | -------- |
| Client ID    | Same `client_code` everywhere       | Same     |
| Product ID   | Same `sku` everywhere               | Same     |
| Warehouse ID | Same `warehouse_code` everywhere    | Same     |
| Dates        | One agreed format (e.g. YYYY-MM-DD) | Same     |

Also agree:

- **How often** we sync (e.g. every 15 min, hourly, once per day)
- **If data conflicts:** **Miaolin’s system (e.g. DigiWin) takes priority** — their value wins for product, price, stock, and order unless both teams agree a different rule for a specific case

---

## Go-live checklist (short)

- [ ] Master data sent (Clients, products, warehouses, inventory, pricing)
- [ ] Sample orders sent for test
- [ ] IDs agreed: customer code, SKU, warehouse code
- [ ] Sync schedule agreed
- [ ] Conflict rule agreed (**Miaolin / DigiWin priority**)
- [ ] Contacts agreed (business + data + technical)
- [ ] Test passed: same customer/product/stock/price on **both systems**

---

## Done = both systems match

We consider sync ready when:

1. Client, product, and warehouse lookups match on both sides.
2. Stock and price match what business expects.
3. Test orders can be checked on **your system** and **ERP** with the same result.

---
