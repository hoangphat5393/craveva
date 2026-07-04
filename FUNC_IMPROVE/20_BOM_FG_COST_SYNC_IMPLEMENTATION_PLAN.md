# BOM -> FG cost sync — decision log & rollout

**Cập nhật:** 2026-06-17
**Trạng thái:** P1 code/test shipped; file đã compact từ implementation plan dài. Còn UAT business sign-off và bật tenant flag.
**Owner:** Dev + PM + Finance

---

## 1. Kết luận PM

P1 đã đổi Product cost theo hướng:

- Bỏ legacy `purchase_information`.
- Cost price hiện theo rule Product Type.
- Manufactured Product (`products.type = goods`) có checkbox **Custom** / `cost_from_bom`.
- Khi Custom ON, cost bị khóa trên Product form và được sync từ tổng BOM khi lưu BOM.
- SO / Invoice B2B **không** dùng cost BOM để tính giá bán; giá bán giữ nguyên.

**Dev regression 2026-06-16:** `php artisan test --compact tests\Feature\ProductionBomFgCostSyncTest.php tests\Feature\PurchaseProductFormUxTest.php tests\Unit\ProductionBomLineCostCalculatorTest.php tests\Unit\ProductTypePurchaseFormUxTest.php` -> **17 passed / 64 assertions**.

---

## 2. Quyết định nghiệp vụ cần giữ

| # | Quyết định | Lựa chọn |
| - | ---------- | -------- |
| D1 | FG sản xuất lấy cost từ đâu | BOM drives FG purchase cost khi Custom ON. |
| D2 | UI Product | Bỏ Purchase Information; dùng Custom + `cost_from_bom`; cost luôn hiện theo Product Type. |
| D3 | Khi nào sync | Khi lưu BOM. |
| D4 | FG mua sẵn / không Custom | Nhập cost tay. |
| D5 | FG Custom trước BOM | Cho lưu; `purchase_price` null đến khi lưu BOM hợp lệ. |
| D6 | Tenant chỉ B2B | Feature flag tắt, không ảnh hưởng giá bán. |
| D7 | Dữ liệu null | Không mass default `purchase_price = 0`; null nghĩa là chưa có cost hợp lệ. |
| D8 | `purchase_information` | Drop khỏi DB/code path; chỉ còn trong migration history nếu có. |

---

## 3. Product type pricing matrix

| Product type UI | `products.type` | Selling price | Cost price | UOM phụ | Ghi chú |
| --------------- | --------------- | ------------- | ---------- | ------- | ------- |
| Manufactured Product | `goods` | Có | Có | Không | Thành phẩm/output BOM; có thể bật Custom để cost lấy từ BOM. |
| Raw Material | `raw_material` | Không | Có, bắt buộc | Có | Nguyên liệu; cost-only, dùng BOM/PO. |
| Semi Finished | `semi_finished` | Không | Có, bắt buộc | Có | Bán thành phẩm; cost-only như nguyên liệu. |
| Packaging | `packaging` | Không | Có, bắt buộc | Không | Bao bì dùng trong BOM. |
| Service | `service` | Có | Không | Không | Không tồn kho, không dùng Production BOM. |

Quyết định UX:

- Raw Material / Semi Finished / Packaging là cost-only.
- Service là sell-only, không tồn kho và không cost.
- Manufactured Product giữ selling price cho B2B; cost nhập tay hoặc khóa theo BOM khi Custom ON.
- Field ít dùng nên collapse/ẩn theo type, tránh một Product form quá dài cho mọi loại.

---

## 4. Luồng nghiệp vụ mục tiêu

```text
RM/Semi/Packaging có purchase_price hợp lệ
  -> Tạo Manufactured Product + Custom ON
  -> Tạo/lưu BOM
  -> Hệ thống sync Product purchase_price = tổng BOM
  -> SO/Invoice vẫn dùng giá bán, không dùng BOM cost
```

Ví dụ Nasi Lemak:

| Bước | Số mục tiêu |
| ---- | ----------- |
| BOM UI | S$3.70 |
| FG `purchase_price` | S$3.70 sau sync |
| SO / Invoice | Giá bán không đổi |

---

## 5. Code / data source chính

| Mảng | Thành phần |
| ---- | ---------- |
| Cờ FG custom | `products.cost_from_bom` |
| Cost Product | `products.purchase_price` |
| BOM cost runtime | `ProductionBomLineCostCalculator` |
| Sync service | `ProductionBomFgCostSyncService` |
| Hook lưu BOM | `ProductionBomController` store/update |
| Feature flag | `production.cost_sync.bom_drives_fg_purchase_price` |
| Product form | Purchase product form partials + validation requests |

---

## 6. UAT còn lại

| ID | Case | Kỳ vọng |
| -- | ---- | ------- |
| BOM-COST-01 | RM thiếu cost -> lưu BOM | Không sync FG cost sai; có cảnh báo/skip đúng. |
| BOM-COST-02 | FG Custom ON trước khi có BOM | Lưu được FG; cost pending/null; form khóa cost. |
| BOM-COST-03 | Lưu BOM hợp lệ | FG `purchase_price` được sync bằng tổng BOM. |
| BOM-COST-04 | FG Custom OFF | Cost nhập tay không bị BOM ghi đè. |
| BOM-COST-05 | Tenant flag OFF | Không sync BOM dù Custom ON. |
| BOM-COST-06 | SO/Invoice | Giá bán không đổi sau khi sync cost. |

Business sign-off vẫn cần Finance/Production xác nhận trên tenant thật trước khi coi là vận hành chính thức.

---

## 7. Backlog sau P1

| ID | Việc | Ghi chú |
| -- | ---- | ------- |
| BOM-COST-P2 | Actual cost khi PO complete | Ngoài P1; cần rule Finance rõ. |
| BOM-COST-P3 | Job recalc FG khi NVL đổi cost | Cần quyết định có tự động đổi cost lịch sử hay chỉ BOM mới. |
| BOM-COST-P4 | Labor + overhead trên BOM | Chưa scope. |
| BOM-COST-P5 | Align Estimate Recipe BOM resolver với Production BOM | Chỉ làm khi Phase 1 quotation cần cùng cost source. |

---

## 8. Không làm trong scope hiện tại

- Không default `purchase_price = 0` toàn bảng.
- Không lấy BOM cost để tính giá bán SO/Invoice.
- Không bắt FG Custom phải có BOM trước mới được lưu.
- Không tự động recalc mọi FG khi cost nguyên liệu thay đổi.
- Không đụng tenant B2B nếu feature flag tắt.

---

## 9. Khi nào có thể retire file này

File có thể retire khi:

1. Finance/Production ký UAT P1.
2. Tenant flag được bật hoặc quyết định giữ off rõ ràng.
3. Backlog P2/P3/P4/P5 được chuyển sang planning riêng hoặc bỏ scope.

Doc đọc thay sau retire:

- `FUNC_LOGIC/PRODUCTION_BUSINESS.md`
- `FUNC_LOGIC/PRODUCT_BUSINESS.md`
- `FUNC_LOGIC/PRODUCTION_BUSINESS.md`
- `BIOMIXING_GAP_STATUS.md`
