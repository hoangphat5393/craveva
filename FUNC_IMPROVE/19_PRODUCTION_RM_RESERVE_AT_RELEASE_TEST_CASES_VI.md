# Test cases — Reserve nguyên liệu Production tại Release

_Cập nhật: **27/05/2026** · Tham chiếu: [`19_PRODUCTION_RM_RESERVE_AT_RELEASE_PLAN_VI.md`](./19_PRODUCTION_RM_RESERVE_AT_RELEASE_PLAN_VI.md)_

**Phạm vi:** Reserve RM qua `StockReservationService` khi **Release** lệnh sản xuất; **không** reserve ở Draft; release reservation khi **Cancel** (Released, chưa post RM); **consume** reservation khi **tất cả** batch đã post RM.

**Automated (Pest):** `tests/Feature/ProductionPostingServiceTest.php` — các test có từ `reserve` / `reservation` trong tên.

---

## Bảng test case UAT / QA

| ID        | Mô tả                                | Tiền điều kiện                                  | Bước thực hiện                                                                               | Kết quả mong đợi                                                                          | Auto    |
| --------- | ------------------------------------ | ----------------------------------------------- | -------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- | ------- |
| **PR-01** | Draft không reserve                  | Lệnh Draft, BOM + kho RM, tồn đủ                | Tạo / mở lệnh Draft; kiểm tra `warehouse_product_batches.reserved_quantity` và màn Warehouse | Không tăng reserved; không có `stock_reservations` active cho `ProductionOrder`           | PR-01 ✓ |
| **PR-02** | Release reserve đúng số lượng        | Draft, RM tồn 1000, BOM 1 RM × 1/FG, planned 10 | Release                                                                                      | Status Released; reserved batch += 10; 1 dòng `stock_reservations` active, ref = order id | PR-02 ✓ |
| **PR-03** | Chặn Release khi thiếu tồn           | Tồn khả dụng 5, planned cần 10                  | Release                                                                                      | Lỗi `insufficientRmToReserve`; vẫn Draft; không reservation                               | PR-03 ✓ |
| **PR-04** | Cancel Released trả reserve          | Released, chưa post RM, có batch                | Cancel order                                                                                 | Cancelled; reserved về 0; reservation status released                                     | PR-04 ✓ |
| **PR-05** | Post RM consume reservation          | Released, reserved 10, 1 batch, consumption 10  | Deduct raw materials                                                                         | `quantity` giảm 10; `reserved_quantity` về 0; reservation consumed                        | PR-05 ✓ |
| **PR-06** | Hai lệnh Released cùng RM            | Tồn 15, order A planned 10, order B planned 10  | Release A → Release B                                                                        | A OK; B fail (PR-03) hoặc chỉ reserve tổng ≤ available                                    | Manual  |
| **PR-07** | Release + Sales DO cùng kho          | Tồn 100; DO confirm reserve 60; PO planned 50   | Release PO                                                                                   | Reserve PO chỉ còn tối đa 40 available; hoặc fail nếu thiếu                               | Manual  |
| **PR-08** | Cancel Draft                         | Draft                                           | Cancel                                                                                       | Không lỗi reservation; không có active reservation                                        | Manual  |
| **PR-09** | Không Cancel In progress đã post RM  | In progress, đã deduct RM                       | Cancel                                                                                       | Lỗi `cannotCancelRmPosted` (giữ như cũ)                                                   | Manual  |
| **PR-10** | Material shortage — Draft            | 1 Draft thiếu, 1 Released đủ reserve            | Mở summary, filter Draft                                                                     | Chỉ thấy nhu cầu Draft; available không trừ reserve của lệnh Draft                        | Manual  |
| **PR-11** | Material shortage — Released         | 1 Released đã reserve                           | Filter Released + In progress                                                                | Available đã trừ reserve Production (+ Sales nếu có)                                      | Manual  |
| **PR-12** | Nhiều batch — consume sau batch cuối | 2 batch, mỗi batch post một phần RM             | Post batch 1 → Post batch 2                                                                  | Sau batch 1 vẫn còn active reserve; sau batch 2 hết active (consume toàn order)           | Manual  |

---

## Ghi chú kiểm tra dữ liệu

```sql
-- Reservation theo lệnh SX
SELECT * FROM stock_reservations
WHERE reference_type LIKE '%ProductionOrder%'
  AND reference_id = :order_id;

-- Reserved trên lô
SELECT id, product_id, quantity, reserved_quantity
FROM warehouse_product_batches
WHERE warehouse_id = :rm_warehouse_id;
```

---

## Regression tối thiểu (dev)

```bash
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php --filter=reserve
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php --filter="posts RM consumption"
php artisan test --compact tests/Feature/Feature/ProductionMaterialShortageSummaryTest.php
```

---

## Trạng thái triển khai

| Phase | Nội dung                                   | Trạng thái |
| ----- | ------------------------------------------ | ---------- |
| 1a    | Service + release/cancel hooks             | Done       |
| 1b    | `assertCanReserve` chặn Release            | Done       |
| 1c    | `consumeForOrder` sau tất cả batch post RM | Done       |
| 2     | UI hiển thị reserved trên order show       | Chưa làm   |
