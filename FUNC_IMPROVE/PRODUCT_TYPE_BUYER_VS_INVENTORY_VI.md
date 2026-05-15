# Loại sản phẩm — Người mua vs Tồn kho (Craveva / Biomixing)

**Mục đích:** Tách hai câu hỏi thường bị nhầm: _“có người mua không?”_ và _“có cần đưa vào tồn / công thức kho không?”_  
**Cập nhật:** 2026-05  
**Code tham chiếu:** `App\Enums\ProductType`, cột `products.type`, module Production / Warehouse.

---

## Bảng tóm tắt

| Loại (UI)               | Giá trị `products.type` | Người mua (B2B / SO)                                               | Cần trong tồn / công thức kho hiện tại?                  |
| ----------------------- | ----------------------- | ------------------------------------------------------------------ | -------------------------------------------------------- |
| **Finished Goods**      | `goods`                 | **Có** — khách đặt SO, giao DO                                     | **Có** — nhận SX (cộng FG), ship DO (trừ FG)             |
| **Raw Material**        | `raw_material`          | **Có** — mua từ NCC (PO / GRN)                                     | **Có** — nhập kho, tiêu hao lệnh SX (BOM component)      |
| **Packaging**           | `packaging`             | **Mua PO (NCC)** — không “bán” cho khách ăn/uống                   | **Có** (nếu theo dõi bao bì) — nhập kho, tiêu hao SX/BOM |
| **Semi Finished (BTP)** | `semi_finished`         | **Thường không** — tự SX rồi dùng tiếp; chỉ khi có HĐ B2B bán bulk | **Chỉ khi** dùng BTP trong quy trình / BOM               |
| **Service**             | `service`               | Có thể bán dịch vụ                                                 | **Không tồn** — hệ thống bỏ qua stock                    |

---

## Ghi chú nhanh

### “Có người mua” ≠ “Có tồn kho”

- **Packaging:** khách lẻ không mua túi/hộp như mua cà phê, nhưng xưởng vẫn **mua NCC** và **trừ tồn** khi đóng gói nếu quản lý SKU bao bì.
- **Semi Finished (BTP):** thường **không** bán SO cho người tiêu dùng; vẫn **cần tồn** nếu có giai đoạn cất BTP giữa hai bước SX hoặc BTP nằm trong BOM.

### BTP (bán thành phẩm)

- Cùng nghĩa với **Semi Finished** / **intermediate mix**.
- Ví dụ: bột 3-in-1 đã pha trộn, chưa đóng gói 150g.

### Trong Craveva (triển khai hiện tại)

| Vai trò                  | Các type được phép                           |
| ------------------------ | -------------------------------------------- |
| Đầu ra BOM / FG lệnh SX  | Chỉ `goods`                                  |
| Component BOM (tiêu hao) | `raw_material`, `semi_finished`, `packaging` |
| Bỏ qua stock             | Chỉ `service`                                |

**UI BOM (`production.boms.create` / `edit`):** dropdown FG chỉ **Finished Goods**; component nhóm theo **Raw Material / Semi Finished / Packaging** (optgroup); validation `StoreProductionBomRequest` / `UpdateProductionBomRequest` khớp filter trên.

Pilot **một bước** (RM → FG, không BTP): có thể **không dùng** `semi_finished` trong master/BOM; vẫn nên cân nhắc **Packaging** nếu PM theo dõi túi/hộp theo SKU.

---

## Đọc thêm

- `FUNC_IMPROVE/BIOMIXING_FLOW_CONCEPTS_VI.md` — RM/FG, PO, DO, Production
- `FUNC_IMPROVE/01_PROD_BOM_FG_POLICY_VI.md` — BOM & lệnh SX
