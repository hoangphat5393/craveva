# Orders

URL: /account/orders
Route name: orders.index
Roles: admin (typical); employee/client per permission
Permissions: view_order, add_order, edit_order
Modules: orders
Related routes:

- List: `orders.index` → `/account/orders`
- Create/Edit/Show: route `orders.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Sales orders — line items, UOM, tier pricing.

## Who uses it / access

- Requires module **`orders`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Operations → Sale Orders (or Sales → Orders menu label may vary)

## Steps

1. **Add Order**.
2. Select **Client**, add product lines.
3. Pick **UOM** on the line when applicable.
4. Lưu → then create DO / Invoice (flow 20).

## Important fields and buttons

| (see on-screen form) | Per UI labels |

## Expected results

- On successful save: return to list or close modal; new/updated row on DataTable.
- AJAX form: toast success; validation errors → red border + toast (see [UI-CONVENTIONS.md](../../REFERENCE/UI-CONVENTIONS.md)).

## Common errors

| 403 | Module/permission | Enable module; grant role permissions |

## FAQ

**Q:** Menu item missing?  
**A:** Check **Module Settings** và subscription package.

## Related

[20-so-do-invoice-warehouse.md](../../flows/20-so-do-invoice-warehouse.md)

