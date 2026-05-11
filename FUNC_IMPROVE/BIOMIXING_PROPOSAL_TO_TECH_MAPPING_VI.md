# Biomixing Proposal -> Technical Mapping (Craveva)

Tài liệu này dùng để đối chiếu nhanh giữa đề xuất nghiệp vụ của khách và cách triển khai kỹ thuật trong hệ thống.

- Proposal gốc: `PROJECT BIOMIXING/2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`
- Playbook triển khai nội bộ: `FUNC_IMPROVE/BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md`
- **Phase 1 + 2 (chốt vận hành):** dùng **`planned_quantity`** từ BOM snapshot (định mức × số TP kế hoạch). Chi tiết trạng thái: `FUNC_TEST/01_BIOMIXING_PROPOSAL_TEST_CASE_MATRIX_VI.md`.

## 1) Nguyên tắc đọc tài liệu

- Proposal của khách thường mô tả ở mức nghiệp vụ.
- Các thuật ngữ như shadow-mode, `planned_quantity_shadow`, `yield_uom_shadow_enabled` là **công cụ triển khai nội bộ**, không phải mọi mục đều bắt buộc trong PDF.
- **Phân tích đầy đủ về shadow / Yield / UOM**, ví dụ rủi ro PO/SO và điều kiện bật: xem **`FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`**.  
  **Lưu ý:** không tự ý bật shadow/enforce mới khi chưa có xác nhận.

## 2) Bảng mapping: Nghiệp vụ -> Kỹ thuật

| Nhóm nghiệp vụ trong proposal | Ý nghĩa nghiệp vụ                          | Quyết định kỹ thuật triển khai tại Craveva                                                                                          |
| ----------------------------- | ------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------- |
| BOM/Recipe control            | Quản lý công thức và thành phần RM cho FG  | `production_boms`, `production_bom_items`, snapshot BOM khi release                                                                 |
| Production planning           | Lập kế hoạch RM/FG trước khi sản xuất      | `production_orders`, `production_batches`, planned consumption từ BOM snapshot                                                      |
| RM consumption                | Truy xuất và trừ RM theo lô                | Post outbound qua `StockMovementService`, có idempotency key                                                                        |
| FG receipt                    | Nhận FG về kho, có batch                   | Post inbound FG qua `StockMovementService`, cập nhật trace theo `reference_type`                                                    |
| Variance control              | Kiểm soát chênh lệch so với plan           | FG quantity policy (strict/controlled/flexible), variance reason + approval gate                                                    |
| Quality lock trước giao hàng  | Không giao khi sản xuất chưa đạt           | Chặn ship Sales DO nếu linked production chưa complete (feature flag)                                                               |
| Receiving QC / quarantine     | Tách nhập đạt/không đạt cho RM đầu vào     | QC status ở GRN line, chỉ inbound khi accepted (nếu enforcement bật)                                                                |
| Rework workflow               | Xử lý lô lỗi có phê duyệt                  | `production_rework_orders` với state machine requested/approved/rejected/completed                                                  |
| Yield/UOM conversion          | Chuẩn hóa tính toán theo đơn vị và hao hụt | **Tùy chọn / deferred:** xem `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`; mặc định `yield_uom_shadow_enabled` = false |

## 3) Trả lời nhanh: proposal có nhắc Yield/UOM không?

- Có ở mức **ý nghĩa nghiệp vụ** (tính đúng định mức theo đơn vị, hao hụt).
- Triển khai **shadow / cột `_shadow`** là quyết định kỹ thuật để rollout an toàn; **Phase 1–2 đã chốt không phụ thuộc** vào việc bật shadow.
