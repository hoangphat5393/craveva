# Payroll Reports

URL: /account/payroll-reports
Route name: payroll-reports.index
Roles: admin (typical); employee/client per permission
Permissions: view_payroll-reports (replace `payroll-reports` with actual permission in Settings → Roles)
Modules: payroll
Related routes:

- List: `payroll-reports.index` → `/account/payroll-reports`
- Create/Edit/Show: route `payroll-reports.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Payroll Reports** in the current company.

## Who uses it / access

- Requires module **`payroll`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `payroll`).

## Steps

1. Open the list: `/account/payroll-reports`.
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

