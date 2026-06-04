# Kế hoạch đơn giản hóa form Product (theo Product Type)

**Cập nhật:** 2026-05-27  
**Trạng thái:** ✅ P1 đã triển khai (2026-05-27) — theo tick §9  
**Đối tượng:** PM, UX, Dev  
**Màn hình:** Inventory → Products → Add / Edit  
**Bối cảnh PM:** Form hiện có **quá nhiều field**, không thân thiện; cần ẩn field **không cần** cho từng loại: Raw Material, Semi Finished, Packaging, Service (và cân nhắc Manufactured Product).

**Doc liên quan**

| File                                                                                               | Vai trò                                       |
| -------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| [`21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md`](./21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md)     | Hiện trạng giá / Purchase Information / UOM   |
| [`20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md`](./20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md) | Cost FG từ BOM (manufactured) — tách checkbox |
| [`../FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_VI.md`](../FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_VI.md)     | Type & Production                             |

---

## 1. Tóm tắt 30 giây (cho PM)

| Câu hỏi                                    | Đề xuất                                                                                       |
| ------------------------------------------ | --------------------------------------------------------------------------------------------- |
| Có nên ẩn field theo type?                 | **Có** — đúng hướng; code đã ẩn một phần (Selling vs Cost, UOM, Service SKU)                  |
| Checkbox **Purchase Information** với NVL? | **Ẩn** — luôn coi NVL/BTP/Bao bì **có cost**; chỉ hiện **Cost price**                         |
| Wholesale / Box / Employee price?          | **Ẩn** với RM, Semi, Packaging, Service (chỉ giữ cho **goods** nếu B2B cần)                   |
| Section Tax / Inventory dài?               | **Thu gọn** — mặc định đóng (accordion) hoặc ẩn field ít dùng                                 |
| Làm ngay hay config?                       | **Phase 1:** ẩn cố định theo `ProductType` (~3–5 ngày). **Phase 2:** tenant config (tùy chọn) |

---

## 2. Hiện trạng form — 8 section (inventory)

Form gồm các block trong `product-form-fields.blade.php` + create/edit (Media, Custom fields).

| #   | Section (EN)                      | Field chính                                                                                  | Ai cần thật?                                |
| --- | --------------------------------- | -------------------------------------------------------------------------------------------- | ------------------------------------------- |
| 1   | **Identity**                      | Product type, Name, SKU                                                                      | Tất cả (Service: ẩn SKU)                    |
| 2   | **Classification**                | Unit type, Category, Subcategory                                                             | Hầu hết; subcategory có thể optional        |
| 3   | **Pricing**                       | Purchase Information ☑, Selling, **Cost**, Wholesale, Price/box, Employee                    | **Phụ thuộc type** — đây là chỗ PM thấy rối |
| 4   | **Units of measure**              | Bảng UOM + Cost price cột                                                                    | Chỉ **Raw Material**, **Semi Finished**     |
| 5   | **Tax & sales options**           | Tax multi, HSN/SAC, Client purchase ☑                                                        | Bán hàng / hóa đơn; NVL thường **không**    |
| 6   | **Inventory & shelf life**        | Track inventory ☑, Opening stock, Storage, Certification, Inventory type, Shelf life, Expiry | Kho; **Service** ẩn gần hết                 |
| 7   | **Description & attributes**      | Description, Specification, Source, Brand, Grade                                             | Master data — có thể **Advanced**           |
| 8   | **Media** (+ **Additional info**) | Images, Custom fields                                                                        | Tùy tenant                                  |

**Đã có trong code (không cần làm lại):**

- Raw Material / Semi Finished / Packaging → **ẩn Selling price**
- Service → **ẩn Cost** + Purchase Information + inventory
- Raw Material / Semi Finished → **section UOM** + cột **Cost price**

**Chưa có (PM muốn):**

- Ẩn **Wholesale / Price per box / Employee** cho cost-only types
- Bỏ checkbox **Purchase Information** cho NVL (luôn bật cost ngầm)
- Thu gọn Tax + Inventory metadata cho NVL/Bao bì
- Collapse “Advanced” (brand, grade, certification, …)

---

## 3. Ma trận đề xuất — field Pricing (mức P1)

| Field                  |      Raw Material      | Semi Finished |  Packaging  |   Service   |            Manufactured (`goods`)            |
| ---------------------- | :--------------------: | :-----------: | :---------: | :---------: | :------------------------------------------: |
| Purchase Information ☑ | **Ẩn** (cost luôn bật) |    **Ẩn**     |   **Ẩn**    |    Đã ẩn    | Giữ hoặc thay bằng “Cost from BOM” (plan 20) |
| **Cost price**         |      **Hiện** \*       |  **Hiện** \*  | **Hiện** \* |     Ẩn      |          Hiện \* (đến khi BOM sync)          |
| Selling price          |           Ẩn           |      Ẩn       |     Ẩn      | **Hiện** \* |                 **Hiện** \*                  |
| Wholesale price        |         **Ẩn**         |    **Ẩn**     |   **Ẩn**    |   **Ẩn**    |         **Giữ** (B2B) hoặc collapse          |
| Price per box          |         **Ẩn**         |    **Ẩn**     |   **Ẩn**    |   **Ẩn**    |                    Tùy PM                    |
| Employee price         |         **Ẩn**         |    **Ẩn**     |   **Ẩn**    |   **Ẩn**    |                    Tùy PM                    |
| Section UOM            |        **Hiện**        |   **Hiện**    |     Ẩn      |     Ẩn      |                      Ẩn                      |

\* Bắt buộc khi lưu (validation server).

**Lý do:** NVL/BTP/Bao bì **không bán** trên catalog B2B như FG; BOM/PO chỉ cần **cost + đơn vị**.

---

## 4. Ma trận đề xuất — Classification, Tax, Inventory (mức P1)

| Field / Section                          |        Raw Material        |   Semi Finished    |     Packaging      |                  Service                   |
| ---------------------------------------- | :------------------------: | :----------------: | :----------------: | :----------------------------------------: |
| Category / Subcategory                   |        Giữ (1 cột)         |        Giữ         |        Giữ         |             Giữ (optional sub)             |
| **Tax & sales** (whole section)          | **Ẩn** hoặc accordion đóng | **Ẩn** / accordion | **Ẩn** / accordion | Giữ Tax; ẩn Client purchase nếu không dùng |
| Track inventory                          |            Giữ             |        Giữ         |        Giữ         |                     Ẩn                     |
| Opening stock                            |     Giữ nếu tick track     |        Giữ         |        Giữ         |                     Ẩn                     |
| Storage / Certification / Inventory type |         **Ẩn** P1          |     **Ẩn** P1      |     **Ẩn** P1      |                     Ẩn                     |
| Shelf life / Expiry                      |        Collapse P2         |    Collapse P2     |    Collapse P2     |                     Ẩn                     |
| Description block                        |    1 field Description     |         同         |         同         |                    Giữ                     |
| Spec / Source / Brand / Grade            |   **Ẩn** hoặc “Advanced”   |         同         |         同         |                    Tùy                     |
| Images                                   |      Optional / ẩn P1      |         同         |         同         |                    Tùy                     |
| Custom fields                            |        Giữ (tenant)        |        Giữ         |        Giữ         |                    Giữ                     |

---

## 5. Wireframe UX sau P1 (ví dụ Raw Material)

```
[ Identity ]
  Type: Raw Material *
  Name *
  SKU (auto)

[ Classification ]
  Unit type *
  Category

[ Pricing — chỉ cost ]
  Cost price (USD) *     ← không còn checkbox Purchase Information
  (không Wholesale / Box / Employee)

[ Units of measure ]
  Bảng UOM | Factor | Cost price

[ Inventory ]  ← accordion, mặc định đóng
  Track inventory
  Opening stock

[ Save ] [ Save & add more ] [ Cancel ]
```

**Packaging** — giống Raw Material nhưng **không** section UOM.

**Service** — chỉ Identity + Selling + (Tax) + Description; không Inventory.

---

## 6. Ba phương án triển khai

| Phương án                              | Mô tả                                                                                                     | Effort       | PM                        |
| -------------------------------------- | --------------------------------------------------------------------------------------------------------- | ------------ | ------------------------- |
| **A — Cố định theo type (đề xuất P1)** | Thêm `ProductType::purchaseFormFieldVisibility()` → Blade `d-none` + JS `togglePurchaseProductTypeFields` | **3–5 ngày** | Chốt bảng mục 3–4         |
| **B — Accordion “Advanced”**           | Field ít dùng gom 1 section mở/đóng                                                                       | +1–2 ngày    | Giảm scroll, vẫn lưu được |
| **C — Config tenant**                  | `product_form.show_wholesale_price` …                                                                     | **1–2 tuần** | Khi nhiều khách khác nhau |

**Khuyến nghị:** **A + B** cho pilot Biomixing; **C** sau nếu cần.

---

## 7. Thay đổi kỹ thuật (dev checklist)

| #   | Việc                                                                | File gợi ý                                |
| --- | ------------------------------------------------------------------- | ----------------------------------------- |
| 1   | Enum: `visiblePricingFields(type)`, `visibleSections(type)`         | `app/Enums/ProductType.php`               |
| 2   | Blade: class `product-field--{key}` + `@if` / `d-none`              | `product-form-fields.blade.php`           |
| 3   | JS: mở rộng `togglePurchaseProductTypeFields`                       | `product-type-dependent-fields.blade.php` |
| 4   | NVL: `purchase_information=1` mặc định, ẩn checkbox; validation giữ | Request + Controller                      |
| 5   | Ẩn wholesale/box/employee columns                                   | `product-form-fields.blade.php`           |
| 6   | Test Pest: create RM form HTML không chứa `wholesale_price`         | Feature test                              |
| 7   | Lang: help text ngắn dưới Cost price                                | `purchase::app.*`                         |

**Không đổi DB** — chỉ UI/validation hiển thị; dữ liệu cũ vẫn đọc được khi edit.

---

## 8. Rủi ro & lưu ý

| Rủi ro                             | Giảm thiểu                                 |
| ---------------------------------- | ------------------------------------------ |
| Tenant đang dùng Wholesale cho NVL | PM khảo sát trước khi ẩn; hoặc config B    |
| Custom field gắn section bị ẩn     | Section Additional info **luôn hiện**      |
| User không tìm thấy Tax/HSN        | Accordion “Tax (optional)” thay vì xóa hẳn |
| Edit SP cũ có `price` null         | Không đổi — chỉ form create/edit           |

---

## 9. Quyết định cần PM chốt (checklist)

Đánh dấu **Giữ / Ẩn / Accordion** cho từng nhóm:

- [ok] **Raw Material** — bỏ checkbox Purchase Information? (đề xuất: **Ẩn, cost luôn bật**)
- [ok] **Semi Finished** — giống Raw Material?
- [ok] **Packaging** — giống Raw Material (không UOM)?
- [ok] **Service** — Wholesale/Employee **Ẩn**; **Ẩn** Unit type (lưu `unit_id` null); **Ẩn luôn** Inventory & shelf life, Specification/Source/Brand/Grade, Images (giữ Description)
- [ok] **Manufactured product** — có ẩn Wholesale/Box/Employee trong pilot OEM? (đề xuất: **collapse**, không xóa)
- [accordion] **Tax section** — ẩn hẳn vs accordion với NVL/Bao bì?
- [ ] **Inventory metadata** (storage, certification, inventory type) — ẩn P1?
- [ẩn luôn] **Images** — bắt buộc cho NVL không?
- [ ] Có cần **config theo tenant** ngay không hay P1 cố định?

---

## 10. Lộ trình gợi ý

| Sprint                     | Deliverable                                                                                  |
| -------------------------- | -------------------------------------------------------------------------------------------- |
| **Sprint UX-1**            | P1 matrix Raw Material + Semi + Packaging + Service (ẩn pricing thừa + Purchase Information) |
| **Sprint UX-2**            | Accordion Advanced + Manufactured product profile                                            |
| **Sprint UX-3** (optional) | Tenant flags + doc training                                                                  |

Song song: [`20_BOM_FG_COST_SYNC`](./20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md) — field “Cost from BOM” **không** dùng lại checkbox Purchase Information.

---

## 11. So sánh trước / sau (số field ước lượng — Raw Material)

|                        | Hiện tại (ước lượng)                   | Sau P1 (ước lượng)  |
| ---------------------- | -------------------------------------- | ------------------- |
| Field nhập chính       | ~25+                                   | **~10–12**          |
| Checkbox gây hiểu nhầm | Purchase Information + Track inventory | Chỉ Track inventory |
| Giá                    | Cost + 3 cột B2B ẩn                    | **Chỉ Cost** + UOM  |

---

_PM đã tick mục 9 → Dev đã triển khai phương án A (`ProductType` helpers, `product-form-fields.blade.php`, JS toggle, Store/Update validation)._
