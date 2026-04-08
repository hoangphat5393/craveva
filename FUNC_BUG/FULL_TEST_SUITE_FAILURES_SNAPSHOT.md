# Snapshot: full test suite failures (để sửa sau)

**Ngày ghi nhận:** 2026-04-08  
**Lệnh:** `php artisan test --compact`  
**Kết quả:** 120 passed, **15 failed**, 2 skipped (~108s)

> Mục đích: lưu trạng thái khi chạy toàn bộ suite; có thể phát sinh thêm test/fail khi tiếp tục phát triển. Khi sửa xong từng mục, cập nhật file này hoặc xóa mục tương ứng.

---

## 1. `Tests\Unit\DeliveryOrderObserverGuardTest`

- **Triệu chứng:** `WarehouseBusinessException` — message kiểu inbound canonical conflict.
- **Stack:** `WarehouseFlowPolicyService.php:62` → `DeliveryOrderObserver.php:27`.
- **Hướng xử lý:** Test cần stub/cấu hình flow warehouse (PO inbound vs DO inbound) hoặc mock `WarehouseFlowPolicyService` để observer không ném khi scenario test là “skip inbound”.

---

## 2. `Tests\Unit\ImportBatchQueueConfigTest`

- **Triệu chứng:** Assert `import_batch_connection === 'database'` nhưng `config/queue.php` hiện tại là **`redis`**.
- **Hướng xử lý:** Hoặc đổi test theo env/repo thực tế (đọc `config()` thay vì hardcode từ file), hoặc document rằng project dùng redis và cập nhật expected value / tách test theo `config('queue.import_batch_connection')`.

---

## 3. `Tests\Feature\CompanyRegisterGateTest`

- **Case:** “rejects public company signup when enable_register is off”.
- **Triệu chứng:** Expected **403**, received **302** (redirect).
- **Hướng xử lý:** Thống nhất UX (403 vs redirect login/home) và cập nhật test hoặc controller/middleware cho đúng spec sản phẩm.

---

## 4. `Tests\Feature\ExampleTest` và `Tests\Feature\PestExampleTest`

- **Triệu chứng:** `GET /` expected **200**, received **302** (thường redirect login hoặc DisableFrontend).
- **Hướng xử lý:** Cập nhật test theo hành vi thật của route `/`, hoặc dùng route health/public cố định; tránh giả định homepage 200 trong app đã bảo vệ middleware.

---

## 5. `Tests\Feature\GrnServicePersistenceTest` (2 tests)

- **Triệu chứng:** SQLite `no such table: grns`.
- **Hướng xử lý:** Bổ sung migration/schema cho sqlite trong test (hoặc `RefreshDatabase` + migrate), hoặc `@group integration` và chạy trên DB đầy đủ.

---

## 6. `Tests\Feature\SalesDoServiceLifecycleTest` (3 tests)

- **Triệu chứng:** Mockery — `SalesShipmentStockService::ensureReservationsForShipment()` được gọi nhưng **không có expectation**.
- **Hướng xử lý:** Thêm `shouldReceive('ensureReservationsForShipment')` (và các method liên quan) trên mock, hoặc dùng partial mock/spy.

---

## 7. `Tests\Feature\SalesDoServicePersistenceTest` (2 tests)

- **Triệu chứng:** SQLite `no such table: sales_dos`.
- **Hướng xử lý:** Giống mục 5 — migrate tối thiểu cho bảng `sales_dos` (và FK cần thiết) trong môi trường test.

---

## 8. `Tests\Feature\SalesShipmentOptionBTest` (3 tests)

- **Triệu chứng:**
    - Hai test: `no such table: sales_dos`.
    - Một test: `InvoiceWarehouseStockService::__construct()` — **1 argument passed, 2 expected** (thiếu `WarehouseFlowPolicyService` hoặc tương đương).
- **Hướng xử lý:** Đồng bộ test với constructor hiện tại (`StockMovementService` + `WarehouseFlowPolicyService`); bổ sung schema sqlite cho `sales_dos` nếu test persistence.

---

## Checklist khi sửa xong

- [ ] Chạy lại: `php artisan test --compact`
- [ ] Ghi rõ commit/PR nào đóng từng mục (optional)
- [ ] Cập nhật hoặc archive file này nếu toàn bộ 15 fail đã được xử lý
