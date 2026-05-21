# Flow: Modules, roles, and permissions

## URLs

- [settings](../pages/settings/settings.md) or settings index in [00-URL-INDEX.md](../00-URL-INDEX.md)
- `/account/settings/role-permissions`
- `/account/settings/module-settings`

## Steps

1. **Settings** → **Module Settings**: enable Purchase, Warehouse, etc. for `admin` / `employee`.
2. **Role & Permissions**: grant `view_*`, `add_*`, etc.
3. Re-login if sidebar cache is stale (`user_modules_` cache).

## Expected outcome

Correct sidebar; no 403 on allowed URLs.

## More detail

[01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md), [REFERENCE/ERP-SYSTEM-OVERVIEW.md](../REFERENCE/ERP-SYSTEM-OVERVIEW.md)
