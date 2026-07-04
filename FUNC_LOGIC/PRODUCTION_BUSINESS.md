# Production Business

**Mục tiêu:** nguồn nghiệp vụ chính cho module Production: loại sản phẩm, BOM, release, reserve, post nguyên liệu, nhập thành phẩm và kiểm thử vận hành.

**Đọc cùng:** SOP khách hàng [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP.md`](<../PROJECT BIOMIXING/PRODUCTION_MODULE_SOP.md>), test matrix [`FUNC_TEST/01_BIOMIXING_TEST_MATRIX.md`](../FUNC_TEST/01_BIOMIXING_TEST_MATRIX.md).

---

## 1. Loại Sản Phẩm Trước BOM

Craveva lọc dropdown BOM/lệnh SX theo `products.type`. Chọn sai type thì sản phẩm không xuất hiện hoặc không trừ/nhập tồn đúng.

| Vai trò nghiệp vụ | `products.type` | Dùng trong Production |
| --- | --- | --- |
| Thành phẩm / Manufactured product | `goods` | Đầu ra BOM, thành phẩm trên lệnh SX, nhập kho sau khi post FG |
| Nguyên liệu / Raw Material | `raw_material` | Component BOM, trừ tồn khi post RM |
| Bao bì / Packaging | `packaging` | Component BOM nếu theo dõi SKU bao bì |
| Bán thành phẩm / Semi Finished | `semi_finished` | Component BOM hoặc bước trung gian nếu quy trình cần |
| Service | `service` | Không dùng BOM/Production, không stock |

**Quy tắc dropdown:**

- BOM output / manufactured product: chỉ `goods` (`Product::forBomOutput()`).
- BOM component: `raw_material`, `semi_finished`, `packaging` (`Product::forBomComponents()`).
- `service` không được chọn trên BOM.

**Thứ tự master data khuyến nghị:**

1. Tạo nguyên liệu (`raw_material`), bao bì (`packaging`) và bán thành phẩm (`semi_finished`) nếu có.
2. Tạo thành phẩm (`goods`).
3. Nhập tồn đúng kho cho nguyên liệu/bao bì.
4. Tạo BOM: chọn thành phẩm đầu ra, thêm component và định mức.
5. Tạo lệnh SX từ BOM.

**Lỗi thường gặp:**

| Triệu chứng | Nguyên nhân | Cách sửa |
| --- | --- | --- |
| Không thấy SP trong dropdown đầu ra BOM | Type không phải `goods` | Đổi sang Manufactured product |
| Không thấy NVL trong dropdown component | Type là `goods` hoặc `service` | Tạo/chỉnh thành Raw Material / Packaging / Semi Finished |
| BOM lỗi component must differ from output | Cùng một product vừa là output vừa là component | Tách master thành phẩm và nguyên liệu riêng |
| Release / post RM báo thiếu tồn | Chưa nhập tồn kho nguyên liệu | Add Inventory đúng kho |

**Thuật ngữ UI/tài liệu:**

- Không dùng viết tắt `FG`/`RM` trên UI, SOP khách, email training hoặc message lỗi.
- Dùng: **Manufactured product / thành phẩm**, **Raw materials / nguyên liệu**, **add finished goods to stock / nhập thành phẩm**, **deduct raw materials / trừ nguyên liệu**.
- Viết tắt kỹ thuật vẫn hợp lệ trong code/database, ví dụ `fg_warehouse_id`, `rm_warehouse_id`, `postFinishedGoodsReceipt`.

---

## 2. Lifecycle Lệnh Sản Xuất

Luồng chuẩn:

```text
Draft -> Released -> In progress -> Completed
```

Cancel cho phép khi:

- `Draft`.
- `Released` nhưng chưa post RM và chưa post FG.

Không cho cancel khi:

- `In progress`.
- `Completed`.

Ý nghĩa nhanh:

| Status | Nghĩa nghiệp vụ |
| --- | --- |
| `Draft` | Lên kế hoạch, chưa reserve Production |
| `Released` | Đã cam kết sản xuất, đã reserve RM |
| `In progress` | Đã bắt đầu trừ RM / chạy batch |
| `Completed` | Đã post FG xong |

---

## 3. Reserve Và Tồn Kho

### Khi Release

- Hệ thống chụp BOM snapshot theo `planned_quantity`.
- Kiểm tra tồn khả dụng: `available = on_hand - reserved`.
- `reserved` đã gồm Sales DO và lệnh SX khác.
- Nếu thiếu tồn -> chặn release (`insufficientRmToReserve`).
- Nếu đủ -> tạo reservation RM qua `StockReservationService`, `reference_type = ProductionOrder`.
- Phân bổ lô RM theo **FEFO** nếu dữ liệu lô/HSD có sẵn.

| Sự kiện | Reserve? |
| --- | --- |
| Draft | Không |
| Release | Có |
| Gán lô RM trên batch | Không, chỉ chọn lô để post |
| Post RM | Trừ `quantity` thật, không tạo thêm reserve |

### Khi Cancel Released

- Hệ thống release toàn bộ reservation active của order.

### Khi Post RM

- Hệ thống xuất kho nguyên liệu (`quantity` giảm).
- Trước allocation/outbound, `ProductionPostingService::postSingleConsumption` quy đổi planned qty sang base unit (`convertToBase`).
- Bug UOM đã fixed 2026-05-20: [`FUNC_BUG/BUG_PRODUCTION_UOM.md`](../FUNC_BUG/BUG_PRODUCTION_UOM.md).
- Khi tất cả batch của order đã post RM -> reservation của order được consume.
- Sau post RM, order chuyển `In progress` nếu trước đó là `Released`.

### Khi Post FG

- Hệ thống nhập kho thành phẩm (`warehouse_product_batches` + trace).
- Đồng thời ghi ledger Purchase Inventory để thành phẩm hiện trên màn Inventory.
- Backfill lịch sử: `php artisan production:backfill-fg-inventory-ledger`.
- Khi không còn output unposted -> order chuyển `Completed`.

---

## 4. Batch Và Planned RM

- UI mặc định không còn bước/nút riêng _Create planned raw material lines from BOM snapshot_.
- Khi Release hoặc khi mở batch chưa có dòng RM, hệ thống tự ghi `production_batch_consumptions` từ BOM snapshot.
- Checklist batch hiện là 4 bước: gán lô RM -> deduct -> FG -> post FG.

| Sự kiện | Hệ thống |
| --- | --- |
| Release batch đầu | `ProductionBatchPlannedLinesApplicator::applySnapshotToBatch()` |
| Mở batch chưa có RM | Auto-apply planned lines |
| DB | `production_batch_consumptions`; `warehouse_product_batch_id` null đến bước gán lô |

Config `production.ui`:

| Key | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `auto_apply_bom_snapshot_on_batch` | `true` | Tự insert planned lines |
| `show_batch_workflow_step_planned_lines` | `false` | Hiện step planned lines |
| `show_apply_planned_from_snapshot_button` | `false` | Hiện nút apply thủ công |

---

## 5. Material Shortage Summary

Mục đích: tổng hợp thiếu nguyên liệu theo `raw material + warehouse` trên nhiều lệnh, không cần mở từng lệnh để cộng tay.

- Công thức: `shortage = max(0, tổng_required - available)`.
- `available = on_hand - reserved` theo base UOM.
- Status mặc định `active` = `Released + In progress`.
- `Draft` chỉ phục vụ lập kế hoạch/mua sớm, không reserve Production.
- `Completed` / `Cancelled` không tính.

---

## 6. Test / UAT Tối Thiểu

| ID | Luồng cần nhớ | Kỳ vọng |
| --- | --- | --- |
| PR-01 | Draft có BOM/tồn đủ | Không reserve, không có `stock_reservations` active |
| PR-02 | Release khi đủ tồn | Status `Released`; reserved batch tăng đúng; có reservation ref = ProductionOrder |
| PR-03 | Release khi thiếu tồn | Bị chặn `insufficientRmToReserve`; vẫn Draft; không tạo reservation |
| PR-04 | Cancel Released chưa post RM | Trả reserved về 0; reservation status released |
| PR-05 | Post RM đủ | `quantity` giảm; `reserved_quantity` về 0 sau khi tất cả batch đã post; reservation consumed |
| PR-06 | Hai lệnh cùng RM | Tổng reserved không vượt available; lệnh sau fail nếu thiếu |
| PR-07 | Sales DO + Production cùng kho | Production reserve chỉ dùng phần available còn lại sau Sales DO reserve |
| PR-08 | Cancel Draft | Không lỗi reservation |
| PR-09 | Cancel In progress đã post RM | Bị chặn `cannotCancelRmPosted` |
| PR-10 | Material shortage Draft | Draft không trừ reserve Production |
| PR-11 | Material shortage Released/In progress | Available phản ánh Production reserve + Sales reserve |
| PR-12 | Nhiều batch | Reservation chỉ consumed sau batch cuối cùng post RM |

Regression tối thiểu:

```bash
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php --filter=reserve
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php --filter="posts RM consumption"
php artisan test --compact tests/Feature/ProductionMaterialShortageSummaryTest.php
php artisan test --compact tests/Feature/ProductionBomAndOrderTenantFlowTest.php --filter="shows reserved raw material quantity"
```

---

## 7. Dev / QA Notes

File/code quan trọng:

- `App\Enums\ProductType`
- `Product::forBomOutput()`, `Product::forBomComponents()`
- `ProductionBomFirstPolicy`
- `ProductionBatchWorkflowSteps`
- `ProductionPlannedConsumptionFromSnapshotService`
- `ProductionBatchPlannedLinesPolicy`
- `Modules/Production/Config/config.php`

Lưu ý khi đổi code:

1. Preview form = BOM master; dòng batch = snapshot trên lệnh lúc release.
2. Planned qty chia đều theo số batch.
3. Material shortage scopes nằm ở `ProductionMaterialSummaryService::statusesForScope()`.
4. Không trừ reserve thêm khi chỉ gán lô RM trên batch.

---

## 8. Tài Liệu Liên Quan

- SOP khách hàng: [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP.md`](<../PROJECT BIOMIXING/PRODUCTION_MODULE_SOP.md>)
- Flow Biomixing live: [`FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md`](../FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md)
- Gap/status: [`FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md`](../FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md)
- Bug UOM: [`FUNC_BUG/BUG_PRODUCTION_UOM.md`](../FUNC_BUG/BUG_PRODUCTION_UOM.md)
