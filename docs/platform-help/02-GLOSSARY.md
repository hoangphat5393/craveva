# Glossary — ERP and Hub UI

Use these terms consistently across `REFERENCE/ERP-SYSTEM-OVERVIEW.md`.

## Documents and logistics

| Term                | Abbr. | Meaning                                    |
| ------------------- | ----- | ------------------------------------------ |
| Sales Order         | SO    | Sales order (`orders`)                     |
| Purchase Order      | PO    | Purchase order                             |
| Delivery Order      | DO    | Sales delivery / shipment                  |
| Goods Received Note | GRN   | Purchase receipt into stock                |
| Bill                | —     | Vendor bill (AP)                           |
| Invoice             | —     | Customer invoice (AR)                      |
| Credit Note         | —     | Sales return / credit                      |
| Vendor Credit       | —     | Purchase return / vendor credit            |
| UOM                 | —     | Unit of measure; alternate UOM on products |

## Warehouse and production

| Term                  | Meaning                                 |
| --------------------- | --------------------------------------- |
| Warehouse             | Stock location                          |
| Stock batch           | Lot/batch tracking                      |
| Stock movement        | Inventory transaction                   |
| Production order      | Manufacturing order                     |
| BOM                   | Bill of materials                       |
| Company flow settings | Per-company warehouse workflow defaults |

## Hub UI

| Term                 | Meaning                                                   |
| -------------------- | --------------------------------------------------------- |
| Right modal          | Slide-in panel from list Add/Edit; URL often unchanged    |
| select-picker        | Bootstrap-select; use `data-container="body"` in tables   |
| DataTable            | Filterable list with export and quick actions             |
| Two-layer validation | Client toast + `.is-invalid`; server `handleApiFormError` |

Details: [REFERENCE/UI-CONVENTIONS.md](REFERENCE/UI-CONVENTIONS.md).

## System

| Term             | Meaning                                                |
| ---------------- | ------------------------------------------------------ |
| Company / tenant | Data scoped by `company_id`                            |
| Module           | Feature package key (purchase, warehouse, …)           |
| Platform help    | This folder — closed English corpus                    |
| Closed corpus    | Agents must not depend on files outside this directory |

## Integrations

| Term          | Meaning                                                                                                                       |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| AI Order REST | `POST /api/integrations/orders` creates/updates SO — see [REFERENCE/ERP-SYSTEM-OVERVIEW.md](REFERENCE/ERP-SYSTEM-OVERVIEW.md) |
