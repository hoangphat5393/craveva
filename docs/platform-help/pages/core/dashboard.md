# Dashboard

URL: /account/affiliates-dashboard
Route name: dashboard.index
Roles: admin (typical); employee/client per permission
Permissions: view_overview_dashboard (tùy widget)
Modules: dashboard
Related routes:

- List: `dashboard.index` → `/account/affiliates-dashboard`
- Create/Edit/Show: route `dashboard.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Post-login overview — KPI widgets and shortcuts.

## Who uses it / access

- Requires module **`dashboard`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Home → Private Dashboard / Advanced Dashboard

## Steps

1. After login, redirect to `/account/dashboard`.\n2. Use widgets; click tiles to open modules.

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



