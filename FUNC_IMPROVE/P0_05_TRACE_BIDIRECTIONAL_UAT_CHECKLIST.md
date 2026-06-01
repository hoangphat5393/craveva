# P0-05 — Trace hai chiều (Warehouse ↔ Production) — UAT checklist

**Điều kiện:** Module Warehouse + Production bật; user có `view_production_orders` và quyền xem kho; có batch/lô kho liên quan SX.

**Tự động (Dev):** `php artisan test --compact tests/Feature/P0BiomixingAutomatedEvidenceTest.php` — không thay Pass/Fail biên bản.

---

## Tiếng Việt

### A) Production → Kho

| Bước | Thao tác                                                | Kỳ vọng                                                     |
| ---- | ------------------------------------------------------- | ----------------------------------------------------------- |
| A1   | Production → lệnh → batch                               | Trang batch hiển thị                                        |
| A2   | Mở **Trace** (`production.batches.trace`)               | Movement / link lô kho                                      |
| A3   | Link **chi tiết lô** (`warehouse.product-batches.show`) | URL `/account/warehouse-product-batches/{id}` khớp ngữ cảnh |

### B) Kho → Production

| Bước | Thao tác                                 | Kỳ vọng                                  |
| ---- | ---------------------------------------- | ---------------------------------------- |
| B1   | Warehouse → Product batches → mở lô      | Chi tiết lô                              |
| B2   | Link tới Production batch/trace (nếu có) | Đúng `production.batches.show` / `trace` |
| B3   | Đối chiếu vòng                           | Mã batch / lệnh khớp                     |

---

## English

### A) Production → Warehouse

| Step | Action                     | Expected                                                   |
| ---- | -------------------------- | ---------------------------------------------------------- |
| A1   | Production → order → batch | Batch detail loads                                         |
| A2   | Open **Trace**             | Movements / warehouse links                                |
| A3   | Click warehouse batch link | `warehouse.product-batches.show` with matching product/qty |

### B) Warehouse → Production

| Step | Action                                   | Expected                 |
| ---- | ---------------------------------------- | ------------------------ |
| B1   | Warehouse → Product batches → open batch | Detail loads             |
| B2   | Follow link to production batch/trace    | Correct production batch |
| B3   | Round-trip                               | References match         |

---

## Kết quả / Result

| Field       | Value |
| ----------- | ----- |
| Date / Ngày |       |
| Tester      |       |
| Environment |       |
| Pass / Fail |       |
| Issues      |       |

Sau Pass: cập nhật `P0_BIOMIXING_NEXT_STEPS_VI.md` (P0-05) + bằng chứng screenshot/URL.
