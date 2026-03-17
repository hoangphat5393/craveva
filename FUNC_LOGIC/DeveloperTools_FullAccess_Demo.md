# DeveloperTools – Full DB Access Demo (AI)

> WARNING: This is **only for short‑term demo on staging**.  
> Do **NOT** use this setup on production. Prefer API‑based writes instead.

---

## 1. Goal

Allow the AI user (created by DeveloperTools module) to **INSERT / UPDATE / DELETE** via the gateway DB for demo purposes, instead of read‑only.

---

## 2. Where permissions are granted

File: `Modules/DeveloperTools/Http/Controllers/DeveloperToolsController.php`

In `store()` (after creating the MySQL user), current code grants **read‑only**:

- `GRANT SELECT ON \`$gatewayDbSafe\`.\* TO {userQuoted}@'%'`

This is the only place that controls the DB privileges for the generated AI user.

---

## 3. How to temporarily give full DML rights (demo only)

1. **Edit grant statement for the gateway DB**

    In `DeveloperToolsController::store()` replace:
    - `GRANT SELECT ON \`$gatewayDbSafe\`.\* TO {userQuoted}@'%'`

    with:
    - `GRANT SELECT, INSERT, UPDATE, DELETE ON \`$gatewayDbSafe\`.\* TO {userQuoted}@'%'`

    Keep `FLUSH PRIVILEGES` as‑is.

2. **Regenerate credential**
    - In Developer Tools UI, click **Revoke** on existing credential.
    - Generate a new credential (same modules: core, pricing, warehouse, …).
    - New DB user will now have SELECT/INSERT/UPDATE/DELETE on the gateway DB.

3. **Important limitations**
    - Only **simple views** with `SELECT ... FROM main.table WHERE company_id = X` (WITH CHECK OPTION) are updatable.
    - Complex `join_views` are **not** updatable by MySQL; AI can still read them but cannot INSERT/UPDATE through those views.
    - Writes always go via the **gateway DB** (e.g. `api_gateway_20.products` view); main DB tables are never directly exposed.

---

## 4. Usage notes for AI prompts

When this demo mode is enabled, the AI should:

- Use **SELECT** queries as today to read products, stock, customers, orders.
- For writes, send `INSERT/UPDATE/DELETE` against **gateway views**, e.g.:
    - `INSERT INTO products (...) VALUES (...)`
    - `UPDATE orders SET ... WHERE id = ...`
- Never touch system tables (migrations, jobs, oauth\_\*, etc.) – they are not exposed in gateway config anyway.

Recommend adding to the AI system prompt:

- \"You are allowed to perform INSERT/UPDATE/DELETE on the gateway database views for demo purposes. Prefer updating orders and carts, not master data like pricing rules, unless explicitly requested.\"

---

## 5. Safer alternatives (for later)

After the demo, revert the grant back to **SELECT only** and implement one of:

- **API layer (recommended):** ERP HTTP endpoints (`/api/ai/orders`, `/api/ai/customers`, …) that validate and log all changes.
- **Command / queue layer:** AI writes to a queue/table (e.g. `ai_pending_actions`) and a Laravel job reviews + applies them.

These approaches keep production data safer while still letting AI create/update orders for customers over WhatsApp / LINE.
