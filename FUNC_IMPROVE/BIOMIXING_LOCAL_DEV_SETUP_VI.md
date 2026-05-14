# Biomixing / Production — triển khai trên môi trường **local**

**Mục đích:** Chuẩn hóa các bước tối thiểu để chạy pilot **trên máy dev** (không nhắm server production). URL kiểu `https://*.test` là **local** (Herd/Valet) trừ khi team tự map DNS.

**Liên quan:** `BIOMIXING_UAT_AND_TEST_GUIDE_VI.md` (lệnh test), `P0_NEXT_ACTION_BIOMIXING_VI.md` (thứ tự P0).

---

## 1) Chuẩn bị repo

- PHP **8.3**, Composer, Node/pnpm theo README repo.
- Sao chép `.env` từ `.env.example`, cấu hình `APP_URL` trùng hostname local (ví dụ `https://craveva-staging.test`).
- `php artisan key:generate` nếu cần.

---

## 2) Cơ sở dữ liệu

```bash
php artisan migrate
```

Nếu dùng tenant/plugin đa công ty: bật đúng company pilot và gói có module **Production** + **Warehouse** (theo `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md`).

---

## 3) Frontend (root app dùng Laravel Mix)

```bash
pnpm install
pnpm run production
```

Khi sửa `resources/js` / CSS trong luồng demo: `pnpm run dev` hoặc `pnpm run watch`.

---

## 4) Module Production / Warehouse

Theo convention repo (module plugin + cache): đảm bảo tenant pilot có quyền menu/route Production và Warehouse; bật flag module trong cấu hình gói nếu UI không hiện.

---

## 5) Smoke tự động (không cần UI)

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php
```

Gói rộng hơn: xem mục 1 trong `BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`.

---

## 6) Sau khi local ổn

Mới xếp **deploy server / staging tập trung**; log P0 vẫn dùng bằng chứng UAT + screenshot theo checklist hai chiều và mini UAT hub.
