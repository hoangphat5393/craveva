# Estimates

URL: /account/estimates
Route name: estimates.index
Roles: admin (typical); employee/client per permission
Permissions: view_estimates, add_estimates
Modules: estimates
Related routes:

- List: `estimates.index` → `/account/estimates`
- Create/Edit/Show: route `estimates.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Estimates — can convert to order/invoice.

## Who uses it / access

- Requires module **`estimates`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Sales → Quotation / Estimates

## Steps

1. Open the list: `/account/estimates`.
2. Use **Add** / **Edit** (usually right modal).
3. Fill the form → **Save**.
4. On list: filter, export, quick actions if available.

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

