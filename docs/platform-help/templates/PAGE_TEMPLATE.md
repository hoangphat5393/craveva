# [Screen title]

URL: /account/...
Route name: resource.action
Roles: admin (typical) | employee | client
Permissions: view*\*, add*_, edit\__
Modules: module_key (required in user_modules)
Related routes: (list .index / .create / .edit / .show)

## Purpose

[What the user accomplishes on this screen.]

## Who uses it / access

- Typical role: ...
- Permission: `view_*` — `all`, `added`, `owned`, `both`, or `none` → 403 if insufficient.
- Module: enable in **Settings → Module Settings** ([01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md)).

## How to open the screen

1. Menu: [Group] → [Item]
2. Direct URL: `/account/...`
3. If **right modal**: from list → **Add** / **Edit** (full page navigation may not occur).

## Steps

1. ...
2. ...

## Important fields, buttons, and tabs

| Element | Meaning |
| ------- | ------- |
| ...     | ...     |

## Expected results

- After save: ...
- On list: ...

## Common errors

| Symptom                       | Cause                            | Fix                                                 |
| ----------------------------- | -------------------------------- | --------------------------------------------------- |
| 403 / no menu                 | Module off or missing permission | Module Settings + Role permissions                  |
| Validation toast + red border | Required fields                  | [UI-CONVENTIONS.md](../REFERENCE/UI-CONVENTIONS.md) |

## FAQ

**Q:** ...  
**A:** ...

## Related

- [flows/...](../flows/...)
- [REFERENCE/...](../REFERENCE/...)
