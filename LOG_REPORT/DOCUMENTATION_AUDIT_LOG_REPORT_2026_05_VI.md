# LOG_REPORT — Audit & gọn bản trùng (2026-05-12)

## 1) Bối cảnh

Thư mục **`LOG_REPORT/`** (trước đây `LOC_REPORT/`) chứa **snapshot đếm dòng** backend PHP (không phải log ứng dụng runtime). README gốc đã ghi nhận nhiều cặp file **cùng dữ liệu**, khác phần header.

## 2) Đã xóa (bản trùng)

| File đã xóa | Lý do |
| ----------- | ----- |
| `backend_loc_per_file_full.txt` / `.csv` | Trùng dữ liệu với `backend_loc_per_file.*` (chỉ khác header). |
| `backend_loc_per_file_lp_by_filename.txt` / `.csv` | Alias backward-compat; cùng merge i18n như `backend_loc_per_file.*`. |
| `backend_loc_per_file_no_languagepack.txt` / `.csv` | Trùng hoàn toàn với `backend_loc_per_file_no_i18n.*` (cùng kích thước). |

## 3) Giữ lại (canonical trong thư mục)

| File | Mục đích |
| ---- | -------- |
| `README.md` | Phạm vi đếm, quy tắc merge locale, tổng số, rollup module, top files, hướng dẫn regenerate. |
| `INDEX.md` | Mục lục ngắn + link audit. |
| `backend_loc_per_file.txt` / `.csv` | **Khuyến nghị:** từng file PHP + i18n gộp theo `<locale>`. |
| `backend_loc_per_file_no_i18n.txt` / `.csv` | Xếp hạng file code thật (không file dịch). |
| `backend_loc_per_module.txt` / `.csv` | Rollup theo `app/`, `Modules/*`, v.v. |
| `_tinykeys.txt` | Phụ trợ (nếu có pipeline cũ tham chiếu). |

## 4) Regenerate

Khi codebase thay đổi lớn, chạy lại quy trình trong `README.md` § “How this was produced” và **ghi đè** các file `.txt`/`.csv` còn lại — không cần tái tạo các alias đã xóa.

## 5) Tham chiếu chéo

- `FUNC_INDEX.md` — mục `LOG_REPORT`
- `FUNC_LOGIC/AUDIT_LOGIC_2026_VI.md` — §9 (cập nhật nhẹ nếu có)
