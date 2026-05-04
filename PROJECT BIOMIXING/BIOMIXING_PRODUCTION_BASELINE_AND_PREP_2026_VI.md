# Nền tảng Craveva (2026) & chuẩn bị triển khai Module Production — Biomixing

**Phiên bản:** 2026-04 (baseline sau khi **SO · PO · Sales DO · Invoice · Warehouse** đã hoàn thiện nhiều).  
**Đối tượng:** PM, BA, dev trước khi kickoff `Modules/Production`.  
**Không thay thế:** `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` (roadmap chi tiết) — file này là **bối cảnh nền + điểm tích hợp + thứ tự đọc**.

---

## 1. Tại sao cần file này?

Bản gốc `BIOMIXING_GAP_ANALYSIS.md` (2026-02) và nhiều dòng trong `BIOMIXING_FLOW_CRACEVA_GAP.md` từng mô tả **Warehouse “Partial”**, batch/expiry như **chưa có** — **lệch** so với Hub hiện tại (đa kho, `warehouse_product_batches`, Sales DO batch identity, reservation/ship, inbound canonical PO/GRN). **2026-04:** hai file đó đã **được chỉnh một phần** (bảng Extension/Critical, dòng #28, §“Đã có”, notice) — vẫn nên đọc **kèm** §2–3 file này và `FUNC_LOGIC` để không nhầm các mục khác trong bản draft cũ.

**Production** vẫn **thiếu** (BOM, lệnh SX, tiêu RM/nhận FG, CCP, rework…). Ta cần tách rõ:

- Cái gì **đã có sẵn để Production bám vào**
- Cái gì **vẫn là gap riêng của Production / HACCP**

---

## 2. Trạng thái nền đã xác nhận (canonical — đọc từ FUNC_LOGIC)

| Chủ đề                              | Trạng thái tóm tắt                                                                   | Tài liệu nguồn sự thật                                                                   |
| ----------------------------------- | ------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------- |
| **Đa kho**                          | Có; tồn/movement theo `warehouse_id`                                                 | `WAREHOUSE_INDEX.md`, `multi_warehouse_audit_report.md`                                  |
| **Batch / HSD tồn**                 | Có bảng `warehouse_product_batches`; KPI inventory đồng bộ batch                     | `ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` §1, `WAREHOUSE_TOM_TAT_NOI_BO.md` |
| **Sales DO**                        | Reserve khi confirm; trừ tồn khi ship; batch + expiry trên dòng; idempotent outbound | `ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` §2–3, `SalesDoService` (code)     |
| **Invoice vs shipment**             | Mode `shipment`: invoice không post outbound lần hai                                 | Cùng file QA verification                                                                |
| **Purchase inbound**                | Một sự kiện canonical (PO delivered **hoặc** GRN received) tránh nhập đôi            | `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`                                             |
| **Luồng tổng PO·DO·SO·Invoice·Kho** | Một chỗ                                                                              | `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`                                             |
| **UAT / checklist**                 | Mua · Bán · Kho E2E                                                                  | `UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`                                                    |

**Cập nhật QA có timestamp:** `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` (**2026-04-23**).

---

## 3. Hiệu chỉnh đọc chung với `BIOMIXING_FLOW_CRACEVA_GAP.md`

Bảng flow xưởng **vẫn dùng** để map bước Biomixing. Chỉ **sửa ý nghĩ đọc** các nhóm sau:

| Chủ đề trong flow                    | Trước đây hay ghi      | Baseline 2026                                                                         |
| ------------------------------------ | ---------------------- | ------------------------------------------------------------------------------------- |
| Kho RM / FG, chuyển kho, tồn theo lô | “Partial / chưa batch” | **Đã có** nền batch + đa kho + Sales DO batch (xem §2)                                |
| Nhập từ PO                           | Một phần               | **Đủ** cho nhập RM; vẫn thiếu **Receiving QC / quarantine** (gap Production-adjacent) |
| QA trước ship / “Quality Lock”       | Một phần               | Có hướng theo task/lock DO — **chưa** thay CCP + COA đầy đủ (gap vẫn đúng trong plan) |
| Truy xuất RM → lệnh SX → FG → khách  | Một phần (chỉ kho)     | **Vẫn thiếu** domain **Production** — đây là lý do làm module mới                     |

---

## 4. Gap **chỉ còn lại** cho module Production (không lặp lại toàn bộ plan)

Tóm tắt khớp `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`:

1. **BOM / Recipe** (version, company vs custom) — `Product` chưa có cấu trúc BOM chuẩn.
2. **Production Order / Batch** + **tiêu thụ RM / nhận FG** qua `StockMovementService` (không nhét logic vào `WarehouseController`).
3. **Khép chuỗi**: RM batch → production batch → FG batch → dòng Sales DO (hiện đã có batch identity — Production phải **tạo** FG batch đúng).
4. **CCP / rework / receiving QC / sampling·COA** — Phase 2–4 trong plan.

---

## 5. Điểm tích hợp kỹ thuật (dev đọc trước khi tạo `Modules/Production`)

| Điểm neo      | Gợi ý                                                                                                                      |
| ------------- | -------------------------------------------------------------------------------------------------------------------------- |
| Xuất nhập kho | Gọi service/movement hiện có; tham chiếu test `PurchaseInboundStockFlowTest`, `WarehouseUpgradeP0Test`, lifecycle Sales DO |
| Sales DO      | `Modules/Purchase/Services/SalesDoService.php` — batch trên line item                                                      |
| Đơn bán       | `Order` / `OrderItems` — link `order_id` hoặc `project_id` từ lệnh SX                                                      |
| Sản phẩm      | `products` — thêm quan hệ BOM (migration mới)                                                                              |

Chi tiết schema & legacy: `FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md` (nếu cần).

---

## 6. Thứ tự đọc đề xuất (kickoff Production)

1. **File này** (baseline).
2. `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` (roadmap + phase).
3. `BIOMIXING_FLOW_CRACEVA_GAP.md` (bước xưởng — đọi kèm §3 baseline).
4. `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` + `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`.
5. `BIOMIXING_PRODUCTION_PROTOTYPE_PLAN_VI.md` nếu làm POC trước.

---

## 7. Liên hệ demo / dữ liệu khách

- Checklist file mẫu: `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md`
- Timeline sau khi nhận file: `BIOMIXING_DEMO_TIMELINE_FROM_DATA_HANDOFF_EN.md`

---

_Cập nhật khi có thay đổi lớn trên Hub (warehouse/sales) hoặc khi kickoff scope Production được chốt._
