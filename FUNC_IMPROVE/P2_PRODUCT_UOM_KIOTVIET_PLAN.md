# P2-UOM — đa đơn vị + UOM price

**Cập nhật:** 2026-06-17
**Trạng thái:** Code A/B/C **Done**; file đã compact từ plan triển khai dài. Còn UAT Oldtown/Luồng D và governance shadow UOM.
**Đọc cùng:** `BIOMIXING_GAP_STATUS.md`, `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS.md`, `FUNC_LOGIC/PRODUCTION_BUSINESS.md` §3.

---

## 1. Kết luận PM

Hệ thống đã có đa đơn vị theo hướng ERP:

- Tồn kho luôn quy về **đơn vị gốc**.
- Sản phẩm có thể có đơn vị phụ trong `product_unit_conversions`.
- Đơn vị phụ có `factor_to_base`, `selling_price`, `for_sale`, `sort_order`.
- SO / Estimate / Invoice / PO dùng resolver để kéo giá theo đơn vị.
- Production post RM đã quy đổi về base unit, tránh lỗi 100 g thành 100 kg.

**Quyết định thiết kế:** UX giống KiotViet về luồng thao tác, nhưng đơn vị phải chọn từ danh mục `unit_types`, không gõ text tự do trên từng dòng. Đây là lựa chọn đúng cho ERP multi-tenant vì Warehouse/Production cần `unit_id` ổn định để quy đổi.

---

## 2. Quyết định nghiệp vụ cần giữ

| Chủ đề | Quyết định |
| ------ | ---------- |
| Đơn vị gốc | Lưu ở `products.unit_id` + giá gốc `products.price`; không lưu dòng gốc trong `product_unit_conversions`. |
| Đơn vị phụ | Lưu ở `product_unit_conversions`; mỗi dòng có `unit_id`, `factor_to_base`, `selling_price`, `for_sale`, `sort_order`. |
| UOM price | `selling_price` null thì derive từ `products.price × factor_to_base`; có giá riêng thì dùng override. |
| Gate thêm đơn vị phụ | Chỉ bật khi đã chọn đơn vị gốc và giá bán gốc > 0. |
| Free text như KiotViet | Không làm trong scope này; nếu PM muốn auto tạo `unit_types` từ text thì mở backlog riêng. |
| Warehouse | Tồn luôn theo base unit; chứng từ dùng unit khác phải có map quy đổi nếu bật strict conversion. |
| Production | Post RM dùng `convertToBase`; shadow yield/UOM chỉ bật sau sign-off. |

---

## 3. Code / data source chính

| Mảng | Thành phần |
| ---- | ---------- |
| Quy đổi | `product_unit_conversions`, `WarehouseUnitConversionService` |
| Giá theo đơn vị | `ProductUnitPriceResolver`, `DocumentLineUnitPricing` |
| Product form | `PurchaseProductController`, `product-unit-conversions*` partials |
| SO/Estimate/Invoice/PO | route `orders/product-unit-price`, line item partials |
| Production | `ProductionPostingService`, `ProductionOrderMaterialRequirementsSummary` |

---

## 4. Trạng thái triển khai

| Phase | Trạng thái | Ghi chú |
| ----- | ---------- | ------- |
| A — Product + UOM price | Done | Product form có đơn vị phụ, factor, price, for_sale; lưu/đọc lại được. |
| B — Document line pricing | Done | SO, Estimate, Invoice, PO cùng pattern đổi unit -> đúng unit price. |
| C — Biomixing / Production | Done code | BOM hiển thị quy đổi; tổng NL và post RM quy base. |
| UAT | Pending | Cần PM/QA ký Oldtown case gói/kg + Luồng D. |
| Shadow UOM | Gated | `yield_uom_shadow_enabled` không bật nếu chưa có governance sign-off. |

**Regression dev 2026-06-16:** `WarehouseUnitConversionFlowTest`, `ProductUnitConversionSyncTest`, `OrderProductUnitPriceTest`, `ProductUnitPriceResolverCostOnlyTest`, `ProductionPostingServiceTest --filter="posts RM consumption in product base unit"` pass.

---

## 5. UAT còn lại

| ID | Case | Kỳ vọng |
| -- | ---- | ------- |
| UOM-UAT-01 | Product có base unit + đơn vị phụ gói/box | Lưu được factor, price, for_sale; reload không mất dữ liệu. |
| UOM-UAT-02 | SO chọn đơn vị phụ | Đơn giá tự đổi theo UOM price, tổng tiền đúng. |
| UOM-UAT-03 | PO/GRN hoặc chứng từ mua dùng unit phụ nếu scope bật | Tồn kho quy về base đúng, không lệch hệ số. |
| UOM-UAT-04 | Production post RM với BOM g/kg | Trừ tồn theo base, không lỗi nhân 100/1000 lần. |
| UOM-UAT-05 | Strict conversion với sản phẩm thiếu map | Có cảnh báo/chặn đúng rule tenant, không âm thầm sai tồn. |

---

## 6. Không làm trong scope hiện tại

- Không dùng ô text tự do cho tên đơn vị trên dòng sản phẩm.
- Không thêm `price_per_box` mới thay cho UOM map.
- Không bật shadow yield/UOM mặc định.
- Không ép tenant B2B dùng strict conversion nếu họ không dùng đa đơn vị.
- Không gộp P2-UOM vào Phase 2 Production MVP; đây là epic nền tảng riêng.

---

## 7. Khi nào có thể retire file này

File có thể retire khi:

1. UAT Oldtown/gói/kg và Luồng D được ký.
2. Shadow UOM được quyết định rõ: bỏ scope hoặc chuyển sang `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS.md`.
3. `PRODUCT_BUSINESS.md` và `PRODUCTION_BUSINESS.md` đã đủ phần vận hành đa đơn vị.

Doc đọc thay sau retire:

- `FUNC_LOGIC/PRODUCT_BUSINESS.md`
- `FUNC_LOGIC/PRODUCTION_BUSINESS.md` §3
- `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS.md`
