# Message File

URL: /account/message-file
Route name: message-file.index
Roles: admin (typical); employee/client per permission
Permissions: view_message-file (replace `message-file` with actual permission in Settings → Roles)
Modules: message-file
Related routes:

- List: `message-file.index` → `/account/message-file`
- Create/Edit/Show: route `message-file.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Message File** in the current company.

## Who uses it / access

- Requires module **`message-file`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `message-file`).

## Steps

1. Open the list: `/account/message-file`.
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

