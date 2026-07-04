# Purchase Products



URL: /account/purchase-products

Route name: purchase-products.index

Roles: admin (typical); employee/client per permission

Permissions: view_product, add_product, edit_product

Modules: purchase

Related routes:

- List: `purchase-products.index` → `/account/purchase-products`
- Create/Edit/Show: route `purchase-products.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)



## Purpose



Manage buy/sell products (SKU, price, alternate UOM, inventory).



## Who uses it / access



- Requires module **`purchase`** in `user_modules()` and matching permission.

- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).



## How to open the screen



Operations → Products



## Steps



1. Open **Products**.
2. **Add Product** (right modal) or **Edit** on a row.
3. Set **Classification** (Unit Type) and **Pricing** (Selling Price > 0) before **+ Add alternate UOM**.
4. **Save**.



## Important fields and buttons



| Unit Type | Base unit (Classification) |
| Selling Price | Giá bán gốc — required before adding UOM rows |
| + Add alternate UOM | Alternate units (case, pack, etc.) |



## Expected results



- On successful save: return to list or close modal; new/updated row on DataTable.

- AJAX form: toast success; validation errors → red border + toast (see [UI-CONVENTIONS.md](../../REFERENCE/UI-CONVENTIONS.md)).



## Common errors



| Add UOM button disabled | Unit or price missing/zero | Set Classification + Pricing |
| UOM dropdown clipped | `table-responsive` | Dropdown uses `body` (fixed) |



## FAQ



**Q:** Menu item missing?

**A:** Check **Module Settings** và subscription package.



## Related



[30-product-and-uom.md](../../flows/30-product-and-uom.md), [PRODUCT_BUSINESS.md](../../REFERENCE/BUSINESS-FLOWS-SUMMARY.md)
