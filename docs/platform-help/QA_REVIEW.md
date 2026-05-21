# QA — Platform Help

Checklist from [REFERENCE/UI-CONVENTIONS.md](REFERENCE/UI-CONVENTIONS.md) (DataTable + modal screens).

## Reviewed screens (expanded copy + spot-check)

| Resource            | Doc                                                                            | Notes                   |
| ------------------- | ------------------------------------------------------------------------------ | ----------------------- |
| `dashboard`         | [pages/core/dashboard.md](pages/core/dashboard.md)                             | reviewed                |
| `orders`            | [pages/sales/orders.md](pages/sales/orders.md)                                 | reviewed — flow 20      |
| `purchase-products` | [pages/operations/purchase-products.md](pages/operations/purchase-products.md) | reviewed — UOM, flow 30 |
| `purchase-order`    | [pages/operations/purchase-order.md](pages/operations/purchase-order.md)       | reviewed                |
| `warehouse`         | [pages/operations/warehouse.md](pages/operations/warehouse.md)                 | reviewed                |

Update **Status** in [00-URL-INDEX.md](00-URL-INDEX.md): `draft` → `reviewed` for these routes.

## Spot-check (local `*.test`)

- `/account/purchase-products/{id}/edit` — Unit + selling price → Add alternate UOM; dropdown not clipped.
- `/account/dashboard` — loads after admin login.

## Remaining

~285 pages remain `draft` (generated boilerplate). Improve from user/RAG feedback.

## Regenerate

```bash
php docs/platform-help/scripts/convert-to-english.php --regenerate
```

Use `--force` on `generate-pages.php` only when intentionally overwriting hand-edited pages.
