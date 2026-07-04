# Time Log Weekly Report

URL: /account/time-log-weekly-report
Route name: time-log-weekly-report.index
Roles: admin (typical); employee/client per permission
Permissions: view_time-log-weekly-report (replace `time-log-weekly-report` with actual permission in Settings → Roles)
Modules: time-log-weekly-report
Related routes:

- List: `time-log-weekly-report.index` → `/account/time-log-weekly-report`
- Create/Edit/Show: route `time-log-weekly-report.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Time Log Weekly Report** in the current company.

## Who uses it / access

- Requires module **`time-log-weekly-report`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `time-log-weekly-report`).

## Steps

1. Open the list: `/account/time-log-weekly-report`.
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

