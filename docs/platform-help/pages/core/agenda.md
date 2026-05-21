# Agenda

URL: /account/agenda
Route name: agenda.index
Roles: admin (typical); employee/client per permission
Permissions: view_agenda (replace `agenda` with actual permission in Settings → Roles)
Modules: agenda
Related routes:

- List: `agenda.index` → `/account/agenda`
- Create/Edit/Show: route `agenda.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Agenda** in the current company.

## Who uses it / access

- Requires module **`agenda`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `agenda`).

## Steps

1. Open the list: `/account/agenda`.
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

