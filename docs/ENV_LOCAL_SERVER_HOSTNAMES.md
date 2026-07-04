# Phân biệt môi trường: local (`.test`) vs server thật

**Mục đích:** Tránh nhầm lẫn khi đọc URL, log, hoặc mô tả bug — đặc biệt cho AI / người mới vào repo.

## Quy ước trong dự án này

| Gọi tên                                   | Ý nghĩa thực tế                                                                                                                                       |
| ----------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`*.test`** (vd. `craveva-staging.test`) | **Máy dev cục bộ** (thường Laravel Herd / Valet / tương đương). **Không** phải “staging server” chỉ vì tên thư mục hoặc chữ _staging_ trong hostname. |
| **Server / staging / hub / production**   | Host **thật** do team triển khai (domain/IP/SSH), **không** dùng TLD `.test` của Herd.                                                                |

## Vì sao dễ nhầm?

- Tên repo / folder **`craveva-staging`** và hostname **`craveva-staging.test`** gợi từ “staging” nhưng đó là **nhãn local**, không mô tả vị trí máy chủ.
- Bug “thiếu bảng trên DB” có thể xảy ra **chỉ trên một** bản MySQL (local) trong khi server khác đã migrate đủ.

## Khi gặp lỗi schema (vd. `sales_dos` doesn’t exist)

1. Xác định **đang nói tới DB nào**: file `.env` của **instance** đang chạy web (local) hay `.env` trên server.
2. Trên đúng DB đó: `php artisan migrate:status` và (nếu cần) kiểm tra `Schema::hasTable('sales_dos')`.
3. Nếu migration ghi **Ran** nhưng bảng **không có**: không xóa thủ công migration registry. DB mới phải dựng lại từ baseline; DB có dữ liệu phải khôi phục backup hoặc dùng forward repair migration đã review.

## Liên quan

- Ma trận SO/PO/GRN/Sales DO: [`SALES_FULFILLMENT_SCHEMA_MATRIX.md`](../FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md)
- Index kho: [`WAREHOUSE_INDEX.md`](../FUNC_LOGIC/WAREHOUSE_INDEX.md)
