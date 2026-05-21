# Roles and access (Craveva ERP)

Applies to all docs in `pages/`. Base URL after login: **`/account/...`**.

## Roles per company

| Role       | Display name      | Notes                                                   |
| ---------- | ----------------- | ------------------------------------------------------- |
| `admin`    | App Administrator | Full company setup; menu `module_settings.type = admin` |
| `employee` | Employee          | Assigned work; menu `type = employee`                   |
| `client`   | Client            | Own orders/invoices/projects; menu `type = client`      |

Users may have multiple roles (`user_roles()`).

## Superadmin (Craveva platform)

- `user()->is_superadmin === true` — tenant `user_modules()` not applied.
- Entry: [pages/super-admin/](pages/super-admin/) and `/account/super-admin-dashboard`.

## Modules (feature flags)

- Table `module_settings`: `module_name`, `type`, `status`, `is_allowed`.
- `user_modules()`: active allowed modules for current user.
- Controllers: `abort_403(! in_array('orders', $this->user->modules))`.
- Sidebar hides items when module is off or route missing — see [REFERENCE/ERP-SYSTEM-OVERVIEW.md](REFERENCE/ERP-SYSTEM-OVERVIEW.md).

Subscription **package** controls default `is_allowed` on company creation.

## Permissions

- `user()->permission('view_order')` — values: `all`, `added`, `owned`, `both`, `none`.
- Naming: `view_*`, `add_*`, `edit_*`, `delete_*`.

## Page metadata

```markdown
Roles: admin (typical)
Permissions: view_order, add_order
Modules: orders
```

## Settings

- Prefix `/account/settings/...` — see [pages/settings/](pages/settings/) and [flows/60-settings-roles-modules.md](flows/60-settings-roles-modules.md).

## Client vs admin on same URL

Example `/account/orders`: clients see only their orders; admins see full filters. Each page doc should state restrictions under **Who uses it / access**.

## Excluded from user RAG corpus

Technical audits and bug notes outside this folder — see [RAG_SOURCES.md](RAG_SOURCES.md).
