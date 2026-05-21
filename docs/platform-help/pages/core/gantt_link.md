# Gantt Link

URL: /account/gantt_link
Route name: gantt_link.index
Roles: admin (typical); employee/client per permission
Permissions: view_gantt_link (replace `gantt_link` with actual permission in Settings → Roles)
Modules: gantt_link
Related routes:

- List: `gantt_link.index` → `/account/gantt_link`
- Create/Edit/Show: route `gantt_link.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Gantt Link** in the current company.

## Who uses it / access

- Requires module **`gantt_link`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `gantt_link`).

## Steps

1. Open the list: `/account/gantt_link`.
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

