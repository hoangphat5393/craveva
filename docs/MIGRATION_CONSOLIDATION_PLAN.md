# Migration Consolidation Plan

> **Historical / superseded on 2026-07-04.** The consolidated `2000_*`
> migrations described below were removed from the active migration directory
> and the historical 408-file set was restored. Do not follow the execution
> instructions in this document. See
> `MIGRATION_AUDIT_AND_GROUPS_2026-07-04.md` for current status and risks.

## Mục tiêu

Tạo một bộ migration baseline dành riêng cho database mới, trong đó mỗi bảng có
một migration chứa schema cuối cùng sau khi đã áp dụng toàn bộ migration core và
module hiện tại.

Bộ migration lịch sử đã được thay bằng baseline sau khi kiểm thử. Source local
hiện tại dùng baseline làm migration mặc định và chỉ hỗ trợ cài database mới;
lịch sử cũ vẫn có thể tra cứu trong Git.

## Phạm vi đã audit

- Core migrations: 406 file.
- Module migrations: 292 file thuộc 25 module.
- Tổng số migration hiện có trên source: 698 file.
- Migration được load theo module profile hiện tại: 679 file, không có migration pending.
- Database Staging có 503 bảng ứng dụng, không tính bảng `migrations`.
- Replay module profile hiện tại trên database trống tạo 495 bảng ứng dụng.
- Hai module đang tắt là `ServerManager` và `Subdomain`; các module này có tổng
  cộng 19 migration và tạo thêm 8 bảng.
- Có migration chỉ sửa schema, migration vừa sửa schema vừa sửa dữ liệu, và
  migration chỉ tạo/cập nhật dữ liệu mặc định.

## Kết quả cần tạo

```text
database/migration-build/full_YYYYMMDD/
├── 2000_01_01_000000_prepare_fresh_schema.php
├── 2000_01_01_000001_create_<table>_baseline.php
├── ...
├── 2000_01_01_999999_finalize_fresh_schema.php
├── _manifest.json
└── _README.md

database/seeders/data/full_YYYYMMDD/
├── 0001_<table>.json
├── ...
├── _manifest.json
└── _README.md
```

Sau khi kiểm thử baseline độc lập, bộ migration được cut over như sau:

```text
database/migrations/                 # 505 migration active
database/seeders/data/full_20260701/ # reference/default data active
```

## Quy tắc gộp

1. `create table`, `add column`, `modify column`, `rename column`, `drop column`,
   index và unique key của cùng một bảng được kết hợp thành schema cuối cùng.
2. Mỗi bảng ứng dụng có đúng một migration tạo bảng trong baseline.
3. Không mang giá trị `AUTO_INCREMENT` của môi trường nguồn sang database mới.
4. Foreign key phải được bảo toàn. Do schema có quan hệ vòng, quá trình dựng
   baseline phải xử lý thứ tự hoặc tạm tắt kiểm tra foreign key.
5. Không đưa dữ liệu giao dịch hoặc dữ liệu người dùng vào baseline.
6. Permission, module, role, setting và reference data cần cho hệ thống mới phải
   được xuất thành seed riêng và kiểm tra checksum.
7. Không xuất `users`, `user_auths` hoặc giá trị password, token, secret hay credential.
8. Super Admin phải được tạo bằng lệnh cài đặt nhận mật khẩu qua prompt hoặc
   biến môi trường, không hard-code trong migration/seed.

## Module profile

Baseline `full` phải bao gồm migration của tất cả module có trong source, kể cả
`ServerManager` và `Subdomain`. Trạng thái bật/tắt module khi chạy ứng dụng là
cấu hình triển khai, không được làm mất schema khỏi bộ baseline đầy đủ.

## Các giai đoạn

| Giai đoạn | Công việc | Trạng thái |
| --- | --- | --- |
| 1 | Audit migration, module, schema drift và dữ liệu trong migration | Completed |
| 2 | Replay toàn bộ 698 migration trên database cô lập | Completed |
| 3 | Sinh migration baseline, mỗi bảng một file | Completed |
| 4 | Tách reference/default data an toàn | Completed |
| 5 | Dựng database kiểm thử thứ hai từ baseline | Completed |
| 6 | So sánh table, column, index, foreign key, check constraint và row count | Completed |
| 7 | Viết hướng dẫn dùng baseline cho dự án khách mới | Completed |

## Tiêu chí nghiệm thu

- Toàn bộ migration baseline chạy thành công trên database MySQL trống.
- Không chạy migration lịch sử cùng lúc với baseline.
- Số bảng và định nghĩa schema khớp database nguồn sạch đã replay toàn bộ module.
- Không thiếu column, index, foreign key hoặc check constraint.
- Seed chỉ chứa reference/default data đã cho phép.
- Không có password, token, secret hoặc credential trong file baseline/seed.
- Có thể tạo Super Admin riêng sau khi migrate và seed.
- `php -l` thành công với toàn bộ migration PHP mới.
- Không thay đổi hoặc xóa migration lịch sử.
- `database/migrations` chỉ chứa baseline active và module migration path cũ không còn file PHP.

## Kết quả kiểm thử

- Source database cô lập: `craveva_consolidated_source_20260701_205549`.
- Verify database cô lập: `craveva_consolidated_verify_20260701_210157`.
- Default-path verify database: `craveva_default_migrations_verify_20260701_211652`.
- Replay thành công 698 migration core/module.
- Baseline gồm 503 migration tạo bảng và 2 migration prepare/finalize.
- 505/505 file migration vượt qua `php -l`.
- Reference data gồm 121 bảng, 3.458 dòng; không có `users` và `user_auths`.
- Schema source/verify: 503/503 bảng, missing 0, extra 0, changed 0.
- Importer đã kiểm tra checksum và đối chiếu nội dung từng bảng sau import.
- Sau khi tạo Super Admin thử nghiệm, row count source/verify không chênh lệch.
- Sau cutover, lệnh `php artisan migrate` mặc định chạy đúng 505 migration;
  database có 503 bảng ứng dụng và 505 migration record.
- Password thử nghiệm được truyền bằng biến môi trường và không được ghi vào file.
- Schema SHA-256: `2fb76c03ca9a66f95bda68657253b8fd6209c63cfc3913847759441a2f1e7a53`.
- Data SHA-256: `533d91c8f04b0b440793b9b629e6be6378b7dd779e6e48f3a19c38f4d03e1f54`.

Database cô lập bị ngắt ở lần thử đầu tiên
`craveva_consolidated_source_20260701_205407` được giữ nguyên, không xóa tự động.
Database này không phải nguồn baseline.

## Cách sử dụng

Chỉ chạy trên database MySQL mới và trống:

```bash
php artisan migrate --force
php database/scripts/import_fresh_seed_data.php --input=database/seeders/data/full_20260701
php artisan fresh-install:create-superadmin your-admin@example.com
```

Lệnh tạo Super Admin sẽ hỏi password ẩn. Khi tự động hóa deployment, đặt password
trong biến môi trường `FRESH_ADMIN_PASSWORD`; không truyền password trực tiếp trên
command line.

Không chạy baseline trên database đang có dữ liệu. Không truyền thêm legacy hoặc
module migration path khi chạy migration mặc định.

Browser installer sẽ dùng schema dump chứa 506 migration record, nhưng
luồng seed của installer chưa import `database/seeders/data`. Cho đến khi
installer được tích hợp thêm bước này, cần dùng ba lệnh CLI ở trên để cài đầy đủ.

## Nhật ký

- 2026-07-01: Bắt đầu kế hoạch consolidation theo yêu cầu tạo dự án khách mới.
- 2026-07-01: Chốt nguyên tắc giữ migration lịch sử và tạo baseline riêng theo bảng.
- 2026-07-01: Replay đủ 698 migration, bao gồm `ServerManager` và `Subdomain`.
- 2026-07-01: Sinh 503 migration theo bảng và 121 file reference data.
- 2026-07-01: Verify schema/data trên database trống hoàn tất, không có chênh lệch.
- 2026-07-01: Cut over 505 baseline file vào `database/migrations`; archive 698
  migration cũ ngoài các đường dẫn Laravel auto-load.
- 2026-07-01: Kiểm thử lại default migration path, reference import và Super Admin;
  schema/data khớp nguồn, không có chênh lệch.
- 2026-07-02: Xóa các thư mục build/thử nghiệm `consolidated-*` và `fresh-*`;
  chuyển reference data cần thiết vào `database/seeders/data/full_20260701`.
- 2026-07-02: Xóa `database/migrations-legacy` sau khi baseline đã được kiểm thử;
  migration lịch sử chỉ còn được lưu trong Git history.
- 2026-07-02: Thêm corrective migration `000505` cho ba cột BOM snapshot Production,
  regenerate `database/schema/mysql-schema.dump`, và xác nhận `php artisan migrate --force`
  mặc định thành công trên database MySQL trống.
