# Production — Viết tắt FG/RM: code vs giao diện người dùng

**Cập nhật:** 2026-05-27  
**Mục đích:** Tránh nhầm — dev dùng `fg_`/`rm_` trong code; **khách / SOP / label UI** dùng từ đầy đủ.

Quy tắc PM (đã có): [`PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`](../PROJECT%20BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md) — _Tránh viết tắt `RM`, `FG` trên label người dùng._

---

## 1. Viết tắt là gì?

| Viết tắt (chỉ dùng nội bộ / doc kỹ thuật) | Tiếng Anh đầy đủ                      | Tiếng Việt khách hiểu                   |
| ----------------------------------------- | ------------------------------------- | --------------------------------------- |
| **FG**                                    | Finished goods / manufactured product | **Thành phẩm** (sản phẩm sản xuất ra)   |
| **RM**                                    | Raw materials                         | **Nguyên liệu**                         |
| **BOM**                                   | Bill of materials                     | **Định mức** / định mức nguyên vật liệu |
| **BTP**                                   | Semi-finished                         | **Bán thành phẩm**                      |

**Không** dùng FG/RM trong: SOP gửi khách, email training, tooltip, banner lỗi.

---

## 2. Trong code — chỗ nào có `fg` / `rm`?

Đây là **tên kỹ thuật** (cột DB, key lang, class). User **không** thấy chữ `fg`/`rm` trừ khi ai đó ghi nhầm vào **giá trị** dịch.

### Database / model

| Kỹ thuật                            | Ý nghĩa                               |
| ----------------------------------- | ------------------------------------- |
| `production_orders.fg_warehouse_id` | Kho **thành phẩm**                    |
| `production_orders.rm_warehouse_id` | Kho **nguyên liệu**                   |
| `output_product_id`                 | Sản phẩm **thành phẩm**               |
| `production_batch_consumptions`     | Dòng **nguyên liệu** tiêu hao trên lô |

### PHP (ví dụ)

| File / symbol                       | Ghi chú                            |
| ----------------------------------- | ---------------------------------- |
| `ProductionFgQuantityPolicyService` | Chính sách **số lượng thành phẩm** |
| `postConsumptionsForBatch`          | Trừ **nguyên liệu**                |
| `postFinishedGoodsReceipt`          | Nhập **thành phẩm**                |
| `Product::scopeForBomOutput()`      | Lọc SP type `goods`                |
| `Product::scopeForBomComponents()`  | Lọc NVL / bao bì / BTP             |

### Lang keys (tên key — **không** phải text hiển thị)

Prefix `fg` / `rm` / `batchRm` trong `Modules/Production/Resources/lang/*/app.php`, ví dụ:

- `fgWarehouse` → giá trị EN: **"Finished goods warehouse"**
- `rmWarehouse` → **"Raw material warehouse"**
- `fgQty` → **"Quantity"** (trên form output)
- `postFgReceipt` → **"Add finished goods to stock"**

**SSOT nhãn UI:** `Modules/Production/Resources/lang/{en,vi}/app.php` (load trước LanguagePack).

### Một chỗ EN còn sót viết tắt (đã sửa 2026-05-27)

- Key `fgOutputSavedButReceiptFailed` — trước đây có chữ "Post FG receipt" → đổi thành câu đầy đủ.

---

## 3. Tài liệu — chỗ thường lỡ FG/RM

| Loại file      | Ví dụ                                          | Quy tắc                                                                   |
| -------------- | ---------------------------------------------- | ------------------------------------------------------------------------- |
| SOP khách      | `PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_*.md` | **Không** FG/RM                                                           |
| Hướng dẫn SP   | `FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_*.md`     | **Không** FG/RM (kể cả sơ đồ)                                             |
| Doc dev nội bộ | `PRODUCTION_OPERATIONS_LIVE_*.md`, audit       | Có thể giữ `fg_warehouse_id` trong bảng kỹ thuật; câu mô tả nên từ đầy đủ |
| Tên file epic  | `16_PRODUCTION_FG_INVENTORY_...`               | Tên file — OK cho dev                                                     |

---

## 4. Cách tìm nhanh trong repo

```bash
# Chỉ tài liệu SOP / FUNC_LOGIC Production (viết tắt trong câu)
rg "\bFG\b|\bRM\b" "PROJECT BIOMIXING/PRODUCTION_MODULE_SOP" FUNC_LOGIC/PRODUCTION_

# Text hiển thị user (giá trị lang) — hy vọng rỗng hoặc rất ít
rg "FG|RM" Modules/Production/Resources/lang/*/app.php
```

---

## 5. Thay thế khi sửa doc

| Tránh               | Dùng (EN)                             | Dùng (VI)               |
| ------------------- | ------------------------------------- | ----------------------- |
| FG                  | finished goods / manufactured product | thành phẩm              |
| RM                  | raw materials                         | nguyên liệu             |
| Post FG             | add finished goods to stock           | nhập thành phẩm vào kho |
| Post RM / Deduct RM | deduct raw materials                  | trừ nguyên liệu         |
| RM warehouse        | raw material warehouse                | kho nguyên liệu         |
| FG warehouse        | finished goods warehouse              | kho thành phẩm          |
