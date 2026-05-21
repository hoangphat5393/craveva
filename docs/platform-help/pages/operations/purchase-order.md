# Purchase Order

URL: /account/purchase-order
Route name: purchase-order.index
Roles: admin (typical); employee/client per permission
Permissions: view_purchase_order, add_purchase_order
Modules: purchase
Related routes:

- List: `purchase-order.index` → `/account/purchase-order`
- Create/Edit/Show: route `purchase-order.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Purchase orders sent to vendors.

## Who uses it / access

- Requires module **`purchase`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Operations → Purchase Order

## Steps

1. **Add** PO.
2. Select vendor, add lines with UOM.
3. Save → GRN / Bill (flow 10).

## Important fields and buttons

| (see on-screen form) | Per UI labels |

## Expected results

- On successful save: return to list or close modal; new/updated row on DataTable.
- AJAX form: toast success; validation errors → red border + toast (see [UI-CONVENTIONS.md](../REFERENCE/UI-CONVENTIONS.md)).

## Common errors

| 403 | Module/permission | Enable module; grant role permissions |

## FAQ

**Q:** Menu item missing?  
**A:** Check **Module Settings** và subscription package.

## Related

[10-po-to-grn-vendor-pay.md](../flows/10-po-to-grn-vendor-pay.md)

