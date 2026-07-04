# Ticket Agents

URL: /account/settings/ticket-agents
Route name: ticket-agents.index
Roles: admin (typical); employee/client per permission
Permissions: view_ticket-agents (replace `ticket-agents` with actual permission in Settings → Roles)
Modules: ticket-agents
Related routes:

- List: `ticket-agents.index` → `/account/settings/ticket-agents`
- Create/Edit/Show: route `ticket-agents.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Ticket Agents** in the current company.

## Who uses it / access

- Requires module **`ticket-agents`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `ticket-agents`).

## Steps

1. Open the list: `/account/settings/ticket-agents`.
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

