# Product Import — Master (gộp vấn đề mapping + custom field + hiệu năng)

## 1) Mục tiêu

- Gộp các phân tích rời về Product import: cột unmatched/custom field và import chậm.
- Trỏ về hướng xử lý hiện tại để tránh hiểu sai “tài liệu cũ”.

**Source of truth (ưu tiên đọc):**
- `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`
- `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`

---

## 2) Nhóm vấn đề A — Unmatched columns & Custom Fields

**Hiện tượng:**
- Một số cột trong file Excel hiển thị “Unmatched”, không map được vào dropdown.

**Nguyên nhân gốc:**
- Mapping cột chỉ dựa trên danh sách `fields()` của import class (ví dụ `ProductImport::fields()`).
- Header so khớp theo chuỗi chính xác; `heading_row.formatter` (vd `slug`) có thể làm header ≠ field id.
- Product có Custom Fields trong hệ thống, nhưng import không merge custom fields vào danh sách cột nên không thể map.

**Tham chiếu chi tiết:**
- `FUNC_BUG/PRODUCT_IMPORT_CUSTOM_FIELDS_AND_SLOW_ANALYSIS.md` (mục 1)

---

## 3) Nhóm vấn đề B — Import chậm / “gần như không chạy”

**Hiện tượng:**
- Import chạy rất lâu hoặc UI không thấy progress cập nhật.

**Nguyên nhân gốc thường gặp:**
- Poll progress chạy `queue:work --max-jobs=50` ngay trong request HTTP → dễ timeout (PHP/nginx/proxy).
- Số job quá lớn (1 dòng = 1 job) → overhead queue và poll nhiều lần.

**Hai hướng xử lý (tài liệu đã ghi nhận):**
- Chunk/batch import để giảm số job.
- Tối ưu lookup/cache trong chunk để tránh query lặp (unit/category/subcategory).

**Tham chiếu chi tiết:**
- `FUNC_BUG/PRODUCT_IMPORT_CUSTOM_FIELDS_AND_SLOW_ANALYSIS.md` (mục 2)
- `FUNC_BUG/PRODUCT_IMPORT_1000_SLOW_CAUSES_AND_SOLUTIONS.md`

---

## 4) Trạng thái hiện tại / ghi chú

- Tài liệu `FUNC_BUG/PRODUCT_IMPORT_1000_SLOW_CAUSES_AND_SOLUTIONS.md` mô tả một đợt optimize theo hướng chunk + cache lookup.
- Nếu trong code đã chuyển hoàn toàn sang chunk job, tài liệu này là “implementation note” để truy vết lý do/tại sao.

---

## 5) Gợi ý “canonical” để tránh trùng lặp tài liệu

- Nếu cần “1 file duy nhất” cho Product import:
  - Giữ file này (`PRODUCT_IMPORT_MASTER.md`) làm index/tóm tắt.
  - Giữ 2 file chi tiết làm tham chiếu (không lặp lại toàn bộ nội dung).

