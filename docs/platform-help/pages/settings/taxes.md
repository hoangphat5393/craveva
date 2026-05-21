# Taxes

URL: /account/settings/taxes
Route name: taxes.index
Roles: admin (typical); employee/client per permission
Permissions: view_taxes (replace `taxes` with actual permission in Settings → Roles)
Modules: taxes
Related routes:

- List: `taxes.index` → `/account/settings/taxes`
- Create/Edit/Show: route `taxes.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Taxes** in the current company.

## Who uses it / access

- Requires module **`taxes`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `taxes`).

## Steps

1. Open the list: `/account/settings/taxes`.
2. Use **Add** / **Edit** (usually right modal).
3. Fill the form → **Save**.
4. On list: filter, export, quick actions if available.

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

[01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md)

