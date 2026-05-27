# Production — Reserve nguyên liệu tại Release (kế hoạch triển khai)

_Cập nhật: **27/05/2026** · Liên quan: [`18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN_VI.md`](./18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN_VI.md), [`04_WH_RUNBOOK_UPGRADE_VI.md`](./04_WH_RUNBOOK_UPGRADE_VI.md), [`BIOMIXING_FLOW_CONCEPTS_VI.md`](./BIOMIXING_FLOW_CONCEPTS_VI.md)_

---

## 1. Quyết định PM (đã chốt)

| Đề xuất                          | Quyết định                                                                                                                                      |
| -------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| Reserve NVL khi tạo **Draft**    | **Không** — Draft còn sửa / hủy / bổ sung kế hoạch; tránh khóa tồn sớm.                                                                         |
| Reserve NVL khi **Release**      | **Có** — Release = cam kết sản xuất; lúc này mới “giữ chỗ” tồn RM.                                                                              |
| Reserve khi Assign lô trên batch | **Phase sau** (tùy chọn) — Phase 1 reserve theo **nhu cầu BOM × planned qty** ở mức **sản phẩm + kho RM**, phân bổ lô theo FEFO giống xuất kho. |

**Lý do so với Draft:**

- Hệ thống đã có **Cancel** order và chỉ cho **sửa order khi Draft** (`onlyDraftEditable`).
- Material shortage summary có thể tập trung **Draft** để lập kế hoạch mua; **Released + In progress** phản ánh nhu cầu đã cam kết (sau reserve).

---

## 2. Hiện trạng code (baseline)

| Hành vi                                     | Trạng thái                                                                                                                  |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| `ProductionPostingService::releaseOrder()`  | Chỉ đổi status + BOM snapshot; **không** reserve.                                                                           |
| `cancelOrder()` (Released, chưa post RM/FG) | Đổi status Cancelled; **không** `releaseByReference` reservation.                                                           |
| Assign lô RM trên batch                     | Chỉ gán `warehouse_product_batch_id`; **không** reserve.                                                                    |
| Post RM (nút vàng)                          | `recordOutbound` trừ `quantity`; **không** `consume` reservation Production.                                                |
| Đọc `reserved_quantity`                     | Có — `ProductionOrderMaterialRequirementsSummary` (available = on_hand − reserved); `ProductionPostingService` khi chọn lô. |
| Ghi `reserved_quantity` cho Production      | **Chưa có** — chỉ Sales DO qua `SalesShipmentStockService` → `StockReservationService`.                                     |

**Hạ tầng sẵn có:** `Modules\Warehouse\Services\StockReservationService` (`reserve`, `release`, `consume`, `releaseByReference`, `consumeByReference`), bảng `stock_reservations`, cột `warehouse_product_batches.reserved_quantity`.

---

## 3. Mục tiêu nghiệp vụ

Sau triển khai:

1. **Release** lệnh SX → tạo reservation RM theo BOM (draft: BOM hiện tại; sau release: snapshot) × `planned_quantity` tại `rm_warehouse_id`.
2. **Available** cho shortage / cảnh báo NVL **giảm** đúng phần đã reserve cho lệnh Released (cùng công thức với Sales DO).
3. **Cancel** lệnh Released (chưa post RM) → **release** toàn bộ reservation của order đó.
4. **Post RM** trên batch → trừ tồn thật; đồng thời **consume** reservation tương ứng (tránh “double lock”).
5. (Khuyến nghị Phase 1b) **Chặn Release** nếu không đủ available sau khi trừ reserve của module khác — khớp PM “đủ NVL mới sản xuất”.

---

## 4. Quy tắc nghiệp vụ chi tiết

### 4.1 Khi nào reserve?

| Sự kiện                                        | Hành động reservation                                                                                       |
| ---------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| **Release** (Draft → Released)                 | `syncReservationsForOrder()` — tạo mới hoặc thay thế toàn bộ dòng active cho `reference = ProductionOrder`. |
| **Sửa Draft** (planned qty, BOM, rm warehouse) | Không đụng reserve (chưa release).                                                                          |
| **Cancel** (Released, chưa post RM)            | `releaseByReference(ProductionOrder::class, id)`.                                                           |
| **Cancel** Draft                               | Không có reserve để release.                                                                                |
| **In progress / Completed**                    | Giữ reserve đến khi post RM hoặc cancel (nếu policy cho phép).                                              |
| **Post RM** (batch)                            | Outbound như hiện tại + `consumeByReference` theo **order** hoặc theo **consumption line** (xem §6).        |

### 4.2 Nguồn nhu cầu NVL

- Dùng chung logic với `ProductionOrderMaterialRequirementsSummary::demandRowsForOrder()` (base unit, waste%).
- **Thời điểm Release:** sau `syncBomSnapshotForReleasedOrder()` → demand từ **snapshot**.
- Mỗi component → một hoặc nhiều dòng `StockReservation` (nếu FEFO trải nhiều lô).

### 4.3 Phân bổ lô khi reserve (chưa assign lô trên batch)

Giống `resolveWarehouseBatchAllocationsForConsumption`:

- Ưu tiên FEFO / expiry (policy hiện có trên batch).
- Mỗi lô đủ `quantity - reserved_quantity` → gọi `StockReservationService::reserve()` với `batch_id`, `reference_type` = `ProductionOrder::class`, `reference_id` = order id.
- Payload thêm metadata (đề xuất): `reference_line` hoặc lưu `component_product_id` qua convention — nếu cần drill-down sau này, cân nhắc cột mở rộng hoặc `reference_type` phụ (`ProductionOrderMaterialLine` — **không** làm ở Phase 1 nếu không cần).

**Lưu ý:** `StockReservationService::reserve()` yêu cầu đủ available trên **từng lô**; service mới `ProductionOrderMaterialReservationService` bọc vòng lặp FEFO.

### 4.4 Tương tác Sales DO

- Cùng cột `reserved_quantity` trên batch → công thức available thống nhất.
- Thứ tự: Release PO #1 reserve 100 kg → DO confirm reserve 30 kg → available còn 70 kg cho PO #2.
- Test bắt buộc: **2 Released orders + 1 DO** cùng RM cùng kho.

### 4.5 Material shortage summary

| Filter                                | Ý nghĩa sau reserve                                              |
| ------------------------------------- | ---------------------------------------------------------------- |
| **Draft**                             | Nhu cầu kế hoạch; **chưa** reserve (trừ reserve Sales khác).     |
| **Released + In progress** (mặc định) | Nhu cầu đã cam kết; available đã trừ reserve Production + Sales. |

Cập nhật note trên màn summary (lang) khi triển khai xong.

---

## 5. Kiến trúc kỹ thuật đề xuất

### 5.1 Service mới

`Modules\Production\Services\ProductionOrderMaterialReservationService`

| Method                                           | Mô tả                                                                    |
| ------------------------------------------------ | ------------------------------------------------------------------------ |
| `syncForOrder(ProductionOrder $order): void`     | Release: xóa/release cũ (idempotent) + reserve theo demand rows.         |
| `releaseForOrder(ProductionOrder $order): void`  | Wrapper `releaseByReference`.                                            |
| `consumeForOrder(ProductionOrder $order): void`  | Wrapper `consumeByReference` (sau post RM toàn bộ order hoặc từng phần). |
| `assertCanReserve(ProductionOrder $order): void` | Ném lỗi có message lang nếu không đủ available (dùng trước Release).     |

**Dependency:** `StockReservationService`, `ProductionOrderMaterialRequirementsSummary`, (tuỳ chọn) `WarehouseUnitConversionService`.

### 5.2 Điểm gắn hook

| File                                                                               | Thay đổi                                                                                       |
| ---------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| `ProductionPostingService::releaseOrder()`                                         | Trong transaction, sau snapshot: `syncForOrder($order)`; trước đó optional `assertCanReserve`. |
| `ProductionPostingService::cancelOrder()`                                          | Trước/sau set Cancelled: `releaseForOrder` nếu status was Released.                            |
| `ProductionPostingService::postSingleConsumption()` / `postConsumptionsForBatch()` | Sau outbound thành công: `consume` reservation khớp qty (Phase 1c — tránh reserve “treo”).     |

**Không** gọi reserve từ: `ProductionOrderController::store`, `assignConsumptionWarehouseBatch`.

### 5.3 Reference type

```php
reference_type: ProductionOrder::class
reference_id: (int) $order->id
```

Đồng bộ với pattern Sales DO (`get_class($shipment)`).

### 5.4 Flow policy

`StockReservationService` gọi `assertSellableOutboundWarehouse` — xác nhận **kho RM** (`rm_warehouse_id`) được phép outbound/reserve (giống post RM).

---

## 6. Consume reservation khi post RM (Phase 1c)

Hai lựa chọn (chốt khi dev):

| Phương án                         | Mô tả                                                  | Ưu / nhược                                                        |
| --------------------------------- | ------------------------------------------------------ | ----------------------------------------------------------------- |
| **A. consumeByReference(order)**  | Sau khi **tất cả** consumption lines của order đã post | Đơn giản; reserve giữ đến hết batch RM.                           |
| **B. consume theo qty từng dòng** | Mỗi `postSingleConsumption` consume đúng `qtyBase`     | Chính xác khi post từng batch một; phức tạp map reservation ↔ lô. |

**Khuyến nghị:** Phase 1 — reserve full order tại Release; Phase 1c — **consumeByReference** khi `posted_consumptions_at` set trên batch **và** mọi batch của order đã post RM (hoặc consume tỷ lệ theo qty posted — nếu partial batch).

Nếu chỉ post 1 batch trong order nhiều batch: cần rule PM — thường consume theo tỷ lệ planned hoặc theo từng component đã post.

---

## 7. UX / thông báo

| Vị trí                          | Nội dung                                                                                      |
| ------------------------------- | --------------------------------------------------------------------------------------------- |
| Release thất bại (không đủ tồn) | Message rõ: component + kho + available vs required (dùng lang `production::app.*`).          |
| Order show (Released)           | (Phase 2 UI) Badge / dòng “RM reserved” hoặc link xem `stock_reservations` filter theo order. |
| Batch show                      | Không đổi Phase 1 — assign vẫn chỉ chọn lô; reserve đã ở cấp order.                           |

---

## 8. Phân phase triển khai

### Phase 1a — Core reserve / release (bắt buộc)

- [x] `ProductionOrderMaterialReservationService`
- [x] Hook `releaseOrder` + `cancelOrder`
- [x] Lang EN/VI cho lỗi insufficient reserve
- [x] Feature tests: release reserves; cancel releases; không reserve draft
- [ ] Test conflict với `StockReservation` Sales (mock hoặc integration) — UAT **PR-07**

### Phase 1b — Chặn release khi thiếu (khuyến nghị PM)

- [x] `assertCanReserve` trước release
- [ ] UI order show: disable / cảnh báo Release khi shortfall (đã có material requirements partial)

### Phase 1c — Consume khi post RM

- [x] Hook `postConsumptionsForBatch` → `consumeForOrder` khi mọi batch đã post RM
- [x] Test: sau post, `reserved_quantity` giảm, `quantity` giảm, reservation `consumed`

**Test case UAT:** [`19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`](./19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md)

### Phase 2 — UI & vận hành

- [ ] Hiển thị reservation trên order show / trace
- [ ] (Tùy chọn) Reserve lại khi đổi planned qty — **không áp dụng** nếu chỉ sửa Draft; nếu sau này cho sửa Released → cần `syncForOrder` lại

### Phase 3 — Tinh chỉnh (backlog)

- [ ] Reserve theo lô đã assign trên consumption (thay FEFO order-level)
- [ ] Đồng bộ với material shortage filter chỉ Draft / chỉ Released
- [ ] Báo cáo reservation theo Production

---

## 9. Test cases (Pest)

| ID  | Kịch bản                                                                                          |
| --- | ------------------------------------------------------------------------------------------------- |
| T1  | Release order có BOM + tồn đủ → `stock_reservations` active, `reserved_quantity` tăng trên batch. |
| T2  | Release khi thiếu available (1b) → fail, status vẫn Draft, không reservation.                     |
| T3  | Cancel Released chưa post RM → reservation released, reserved về 0.                               |
| T4  | Hai order Released cùng RM — reserve tổng không vượt on-hand.                                     |
| T5  | Order Released + DO reserve cùng batch — thứ hai fail hoặc available đúng.                        |
| T6  | Post RM → consume (1c) + outbound; available không âm ảo.                                         |
| T7  | Material shortage: Draft không gồm reserve Production; Released scope có trừ reserve.             |

---

## 10. Rủi ro & giảm thiểu

| Rủi ro                                  | Giảm thiểu                                                                                |
| --------------------------------------- | ----------------------------------------------------------------------------------------- |
| Reserve FEFO khác lô user assign sau    | Document: assign là xác nhận lô; reserve FEFO là giữ tồn tổng; post RM ưu tiên lô assign. |
| Release OK nhưng post RM fail (lô khác) | Giữ consume theo outbound thực tế; release phần reserve thừa (sync lại) — backlog.        |
| Order In progress không cancel được     | Reserve giữ đến complete/cancel policy — đúng nghiệp vụ.                                  |
| Performance nhiều dòng BOM              | Transaction một order; index `stock_reservations(reference_type, reference_id)`.          |

---

## 11. Tài liệu cần cập nhật khi code xong

- [ ] `FUNC_IMPROVE/18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN_VI.md` — §13.4 tham chiếu reserve.
- [ ] `FUNC_IMPROVE/BIOMIXING_FLOW_CONCEPTS_VI.md` — dòng Production Order.
- [ ] `FUNC_IMPROVE/PRODUCTION_MODULE_PROGRESS_REPORT_EN.md` — trạng thái WIP.
- [ ] `FUNC_LOGIC/` (nếu có file Production inventory) — 1 đoạn lifecycle reserve.

---

## 12. Tóm tắt một dòng cho dev

**Release = snapshot BOM + reserve RM (FEFO, `StockReservationService`, ref `ProductionOrder`); Cancel Released = release reservation; Post RM = outbound + consume reservation; Draft = không reserve.**

---

_Code Phase 1a–1c triển khai 27/05/2026. Phase 2 UI backlog._
