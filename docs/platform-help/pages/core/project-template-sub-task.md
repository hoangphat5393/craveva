# Project Template Sub Task

URL: /account/projects/project-template-sub-task
Route name: project-template-sub-task.index
Roles: admin (typical); employee/client per permission
Permissions: view_project-template-sub-task (replace `project-template-sub-task` with actual permission in Settings → Roles)
Modules: project-template-sub-task
Related routes:

- List: `project-template-sub-task.index` → `/account/projects/project-template-sub-task`
- Create/Edit/Show: route `project-template-sub-task.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Project Template Sub Task** in the current company.

## Who uses it / access

- Requires module **`project-template-sub-task`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `project-template-sub-task`).

## Steps

1. Open the list: `/account/projects/project-template-sub-task`.
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

