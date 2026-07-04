# Key Results Metrics

URL: /account/key-results-metric
Route name: key-results-metrics.index
Roles: admin (typical); employee/client per permission
Permissions: view_key-results-metrics (replace `key-results-metrics` with actual permission in Settings → Roles)
Modules: key-results-metrics
Related routes:

- List: `key-results-metrics.index` → `/account/key-results-metric`
- Create/Edit/Show: route `key-results-metrics.create`, `.edit`, `.show` (usually AJAX modal; list URL unchanged)

## Purpose

Manage **Key Results Metrics** in the current company.

## Who uses it / access

- Requires module **`key-results-metrics`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../../01-ROLES-AND-ACCESS.md).

## How to open the screen

Use sidebar group (requires module `key-results-metrics`).

## Steps

1. Open the list: `/account/key-results-metric`.
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

