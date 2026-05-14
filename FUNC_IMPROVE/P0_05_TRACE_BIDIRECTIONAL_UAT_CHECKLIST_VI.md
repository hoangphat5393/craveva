# P0-05 — Trace hai chiều (Kho ↔ Sản xuất) — Checklist UAT (tiếng Việt)

**Bản song song:** `FUNC_IMPROVE/P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST_EN.md` (English).

**Điều kiện:** Module **Warehouse** + **Production** bật; user có `view_production_orders` và quyền xem kho/batch phù hợp; có ít nhất một lệnh/batch production liên quan lô kho thật hoặc dữ liệu demo.

---

## A) Production → Kho

| Bước | Thao tác                                                                       | Kỳ vọng                                                                                           |
| ---- | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| A1   | **Sales** → **Production** (hoặc menu Production) → mở **lệnh** → mở **batch** | Trang chi tiết batch hiển thị.                                                                    |
| A2   | Mở **Trace** (`production.batches.trace`)                                      | Có danh sách movement / link tới lô kho (khi có).                                                 |
| A3   | Bấm link tới **chi tiết lô kho** (`warehouse.product-batches.show`)            | URL dạng `/account/warehouse-product-batches/{id}`; thông tin product/kho/batch khớp ngữ cảnh SX. |

**Bằng chứng:** Screenshot hoặc copy URL (trace + trang lô đích).

---

## B) Kho → Production

| Bước | Thao tác                                                  | Kỳ vọng                                                             |
| ---- | --------------------------------------------------------- | ------------------------------------------------------------------- |
| B1   | **Warehouse** → danh sách **Product batches** → mở một lô | Trang chi tiết lô mở được.                                          |
| B2   | Nếu UI có link tới **Production batch / trace**, bấm theo | Mở đúng `production.batches.show` hoặc `trace` của batch tương ứng. |
| B3   | Đối chiếu vòng                                            | Mã batch / tham chiếu lệnh khớp kỳ vọng.                            |

**Bằng chứng:** Screenshot hoặc URL.

---

## Kết quả (điền khi xong)

| Trường           | Giá trị |
| ---------------- | ------- |
| Ngày             |         |
| Người test       |         |
| Môi trường / URL |         |
| Pass / Fail      |         |
| Issue (mức độ)   |         |

Sau khi **A và B đều Pass**, cập nhật dòng **P0-05** trong `FUNC_IMPROVE/P0_EXECUTION_LOG.md` (kèm link ảnh hoặc ticket).

**Tự động (Dev):** `php artisan test --compact tests/Feature/P0BiomixingAutomatedEvidenceTest.php` — kiểm tra Blade vẫn nối `warehouse.product-batches.show` ↔ `production.batches.trace` (không thay biên bản Pass/Fail ở trên).
