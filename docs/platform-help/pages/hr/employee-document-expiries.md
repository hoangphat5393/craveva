# Employee Document Expiries

URL: /account/employee-document-expiries
Route name: employee-document-expiries.index
Roles: admin (typical); employee/client per permission
Permissions: view_employee-document-expiries (replace `employee-document-expiries` with actual permission in Settings → Roles)
Modules: employee-document-expiries
Related routes:

- List: `employee-document-expiries.index` → `/account/employee-document-expiries`
- Create/Edit/Show: route `employee-document-expiries.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Employee Document Expiries** in the current company.

## Who uses it / access

- Requires module **`employee-document-expiries`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `employee-document-expiries`).

## Steps

1. Open the list: `/account/employee-document-expiries`.
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

[01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md)

