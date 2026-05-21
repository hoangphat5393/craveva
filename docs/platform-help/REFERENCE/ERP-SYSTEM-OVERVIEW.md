# Craveva ERP — System overview (agent corpus)

Self-contained summary for RAG/agents. Authenticated UI lives under **`/account/...`**.

## Architecture

- **Stack:** Laravel 11, PHP 8.3, modular packages (`Modules/*` via nwidart).
- **Tenant:** Each **company** has isolated data (`company_id` scope).
- **Auth:** Session login; Fortify; roles per company.

## URL layout

| Prefix                 | Purpose                                                 |
| ---------------------- | ------------------------------------------------------- |
| `/account/`            | Main ERP (CRM, sales, purchase, warehouse, HR, finance) |
| `/account/settings/`   | Company settings, roles, modules, integrations          |
| `/account/production/` | Production orders, BOM, batches                         |
| `/account/pricing/`    | Pricing tiers, client tier assignment                   |
| `/api/`                | Integrations (e.g. AI order REST) — not browser UI docs |

Post-login home: `/account/dashboard` (or role-specific dashboard).

## Major modules (sidebar)

| Area       | Module key                                     | Examples                     |
| ---------- | ---------------------------------------------- | ---------------------------- |
| Work       | projects, tasks, contracts                     | Projects, tasks, timesheet   |
| Sales      | clients, leads, deals, orders, estimates       | CRM + sales documents        |
| Operations | purchase, warehouse, production, pricing       | Products, PO, GRN, DO, stock |
| Finance    | invoices, payments, expenses                   | AR/AP                        |
| People     | employees, leave, attendance, payroll, recruit | HR                           |
| Admin      | settings, webhooks, tickets                    | Configuration                |

Menu items appear only if **module is enabled** and **route exists** — see [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## Core business chains

1. **Procure:** Vendor → Product → PO → GRN → Bill → Vendor payment — [flows/10-po-to-grn-vendor-pay.md](../flows/10-po-to-grn-vendor-pay.md)
2. **Sell:** Client → SO → DO → stock issue → Invoice → Payment — [flows/20-so-do-invoice-warehouse.md](../flows/20-so-do-invoice-warehouse.md)
3. **Product master:** SKU, base unit, selling price, alternate UOM — [flows/30-product-and-uom.md](../flows/30-product-and-uom.md)

## Sales / purchase document map

| Doc           | Meaning                         |
| ------------- | ------------------------------- |
| SO            | Sales order (`orders`)          |
| PO            | Purchase order                  |
| DO            | Delivery order / sales shipment |
| GRN           | Goods received (purchase)       |
| Invoice       | Customer invoice                |
| Bill          | Vendor bill                     |
| Credit note   | Sales return / credit           |
| Vendor credit | Purchase return                 |

## Multi-warehouse (when enabled)

- PO/GRN/SO/DO lines can specify **warehouse**.
- Stock movements and batches tracked per warehouse module.
- Company-level flow settings configure defaults.

## AI order integration

- **Endpoint:** `POST /api/integrations/orders` (REST, not legacy webhook).
- Creates or updates **sales order** from external AI/channel.
- Requires API credentials configured in company integration settings.

## How to use this corpus

1. Resolve user URL via [00-URL-INDEX.md](../00-URL-INDEX.md).
2. Read matching `pages/**/*.md` for UI steps.
3. For end-to-end questions, read `flows/*.md`.
4. Terms: [02-GLOSSARY.md](../02-GLOSSARY.md). Access: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

Do **not** rely on files outside `REFERENCE/ERP-SYSTEM-OVERVIEW.md` when answering end users.
