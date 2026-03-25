# Module DB Layer Backfill Runbook (Staging / Hub)

## 1) Muc dich

Dong bo DB layer cho cac module Nwidart con thieu trong he thong:

- `modules` table
- `module_settings` table (admin/employee per company)

Khong cap package entitlement tu dong, khong thay doi business data.

---

## 2) Migration da them

- `database/migrations/2026_03_25_130000_backfill_missing_nwidart_modules_in_db_layer.php`

### Nguyen tac an toan

- Idempotent (`firstOrCreate`, check ton tai truoc khi insert)
- Khong xoa du lieu
- Khong sua cac bang nghiep vu
- Khong auto them module vao `module_in_package`

---

## 3) Ket qua local da verify

Sau migrate local, cac module truoc day thieu DB layer da co:

- `cybersecurity`
- `developertools`
- `einvoice`
- `languagepack`
- `lineintegration`
- `onboarding`
- `projectroadmap`

Trang thai sau backfill:

- Co record trong `modules`
- Co `module_settings` cho `admin`/`employee`
- `is_allowed` mac dinh = 0 neu package chua khai bao module do

---

## 4) Cach cap nhat tren staging/hub

### Buoc 1: backup

- Backup DB day du (schema + data)

### Buoc 2: deploy code

- Deploy branch co migration moi

### Buoc 3: chay migration

```bash
php artisan migrate --path="database/migrations/2026_03_25_130000_backfill_missing_nwidart_modules_in_db_layer.php" --force
```

### Buoc 4: clear cache

```bash
php artisan optimize:clear
```

### Buoc 5: verify nhanh

Kiem tra cac module da co trong `modules` va `module_settings`:

```bash
php artisan tinker --execute="dump([
  'modules' => \App\Models\Module::withoutGlobalScopes()->whereIn('module_name', ['cybersecurity','developertools','einvoice','languagepack','lineintegration','onboarding','projectroadmap'])->pluck('module_name')->values(),
  'module_settings_count' => \App\Models\ModuleSetting::withoutGlobalScopes()->whereIn('module_name', ['cybersecurity','developertools','einvoice','languagepack','lineintegration','onboarding','projectroadmap'])->selectRaw('module_name, count(*) as c')->groupBy('module_name')->pluck('c','module_name')
]);"
```

---

## 5) Luu y van hanh

- Migration nay chi backfill DB layer de tranh mismatch module.
- Neu muon cong ty duoc dung module, can cap nhat `module_in_package` theo chinh sach goi (khong tu dong trong migration nay).
- `custom_domain` (map tu `Subdomain`) duoc giu nguyen co che cu, khong ep tao `module_settings`.
