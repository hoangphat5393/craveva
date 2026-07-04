# Employee Hourly Rate Settings

URL: /account/payroll-settings/employee-hourly-rate-settings
Route name: employee-hourly-rate-settings.index
Roles: admin (typical); employee/client per permission
Permissions: view_employee-hourly-rate-settings (check Settings → Roles for exact key)
Modules: payroll
Related routes:

- List: `employee-hourly-rate-settings.index` → `/account/payroll-settings/employee-hourly-rate-settings`
- Create/Edit/Show: `employee-hourly-rate-settings.create`, `.edit`, `.show` (often AJAX modal; list URL unchanged)

## Purpose

Manage **Employee Hourly Rate Settings** in the current company.

## Who uses it / access

- Requires module **`payroll`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Sidebar group for module `payroll` (module must be enabled).

## Steps

1. Open list: `/account/payroll-settings/employee-hourly-rate-settings`.
2. **Add** / **Edit** (usually right modal).
3. Fill form → **Save**.
4. Use list filters, export, quick actions if shown.

## Important fields and buttons

| (on-screen labels) | See form on screen |

## Expected results

- After save: return to list or close modal; row appears/updates in DataTable.
- AJAX forms: success toast; validation errors show red borders + toast ([UI-CONVENTIONS.md](../../REFERENCE/UI-CONVENTIONS.md)).

## Common errors

| 403 | Module/permission | Enable module; grant role permissions |

## FAQ

**Q:** Menu item missing?  
**A:** Check **Module Settings** and subscription package.

## Related

[01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md)
