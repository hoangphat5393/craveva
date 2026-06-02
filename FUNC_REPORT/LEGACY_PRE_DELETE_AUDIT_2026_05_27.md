# Pre-delete audit — tài liệu & artifact (2026-05-27)

**Mục đích:** Rà lại **sau pass 1–8** trước khi xóa thêm. Chỉ xóa mục **Tier 0** trong bảng dưới; mục Tier 1–2 cần review PM/dev hoặc gộp nội dung trước.

**Liên quan:** [`DOCUMENTATION_CLEANUP_AUDIT_2026_05_27.md`](DOCUMENTATION_CLEANUP_AUDIT_2026_05_27.md) · [`LEGACY_PHP_AND_ASSET_CANDIDATES_2026_05_27.md`](LEGACY_PHP_AND_ASSET_CANDIDATES_2026_05_27.md)

---

## 1) Kết quả rà pass 1–8 (xác nhận)

| Kiểm tra                                                              | Kết quả                                                                                             |
| --------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| Link markdown tới `FUNC_LOGIC/AUDIT_*_VI.md` đã xóa                   | **Không** còn link living (chỉ `LEGACY_ARCHIVE` / `git log`)                                        |
| Link tới `PHASE2_PM_PLAN`, `BIOMIXING_DEV_PLAN`, `08_CLIENT_IMPORT_*` | **Không** còn link file; có `git log -- path` hợp lệ                                                |
| Living SSOT (Production, Biomixing hub, REGISTRY, master guides)      | **Giữ** — không đề xuất xóa                                                                         |
| Đếm file (ước lượng)                                                  | `FUNC_IMPROVE` ~31 · `FUNC_LOGIC` ~59 · `FUNC_BUG` ~16 · `FUNC_IMPORT` ~3 · `PROJECT BIOMIXING` ~32 |

---

## 2) Tier 0 — xóa ngay (không ảnh hưởng web / nghiệp vụ)

| File                                                            | Lý do                                                                        | Thay thế                                                  | Risk     |
| --------------------------------------------------------------- | ---------------------------------------------------------------------------- | --------------------------------------------------------- | -------- |
| `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI copy.md`                    | Trùng byte với `DESIGN_BACKEND_UI_UX_VI.md` (hash giống); không có link repo | Canonical: `DESIGN_BACKEND_UI_UX_VI.md`                   | **Thấp** |
| `public/js/custom copy.js`                                      | Không trong `webpack.mix.js`; không reference trong code                     | `public/js/custom.js` (build từ `resources/js/custom.js`) | **Thấp** |
| `resources/views/sections/menu.blade.backup-20260116.php`       | Backup view; không include                                                   | `menu.blade.php` hiện tại                                 | **Thấp** |
| `public/css/custom-css/theme-custom.backup-20260330-075832.css` | Backup CSS; không enqueue                                                    | `theme-custom.css` (nếu có)                               | **Thấp** |

**Pass 9 thực hiện:** xóa các file Tier 0 ở trên (2026-05-27).

---

## 3) Tier 1 — gộp rồi xóa (doc) — **Pass 10 xong (2026-05-27)**

| File                                                                      | Đã gộp vào                                      |
| ------------------------------------------------------------------------- | ----------------------------------------------- |
| `FUNC_IMPROVE/P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md`                | `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md` §8 |
| `FUNC_BUG/ENG_TO_EN_STANDARDIZATION.md`                                   | `FUNC_BUG/REGISTRY.md` — Phụ lục I18N-ENG-001   |
| `FUNC_IMPROVE/purchase_lang_audit_report.csv`                             | `scripts/audit_purchase_lang.php` + git history |
| `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM_TABLE.html`              | `PHASE1_QUOTATION_FLOW_DIAGRAM.mmd` / `.html`   |
| `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd` + `.html` | `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`   |

---

## 4) Tier 2 — **không xóa** cho đến khi feature / PM đóng

| File                                                                           | Lý do giữ                                                                            |
| ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------ |
| `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md`                                     | WUP backlog + evidence                                                               |
| `FUNC_IMPROVE/07_PRICING_MODULE_DEV_TASKS.md`                                  | Pricing chưa xong                                                                    |
| ~~`09_ORDER_HISTORY_IMPROVE_PLAN.md`~~                                         | **Đã gộp** → `FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md` §7 (backlog parse-once vẫn mở) |
| `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`                      | Governance shadow UOM                                                                |
| `FUNC_IMPROVE/14_CLIENT_LISTING_TABLE_UX_PLAN_VI.md`                           | UX-006 mở                                                                            |
| `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md`                                  | Phase 4–5 refactor tracker                                                           |
| `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`                                   | **Rút gọn pass 11** — spike/ERD ngắn; hub link bản slim                              |
| `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`                                      | SSOT trạng thái code vs PM                                                           |
| Toàn bộ `UI_RUNBOOK_*`, `PRODUCTION_MODULE_SOP_*`, `PM_YEU_CAU_TONG_HOP_VI.md` | QA / PM vận hành                                                                     |
| `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` và master guides                 | Vận hành production / kho / sales                                                    |

---

## 5) Tier 3 — chỉ khi team chốt policy diagram

| Nhóm                                    | Ghi chú                                                                                     |
| --------------------------------------- | ------------------------------------------------------------------------------------------- |
| `PROJECT BIOMIXING/*.html` (cặp `.mmd`) | Giữ `.mmd` làm source; xóa `.html` chỉ khi có lệnh regen (Pandoc/Mermaid CLI) trong runbook |
| ~~`DIAGRAM/pis_e2e_current_copy.*`~~    | **Đã xóa pass 12** — `pis_e2e_current.mmd` / `.html`                                        |

---

## 6) Pass 11–12 (2026-05-27) — **xong**

- Rút gọn `BIOMIXING_PLAYBOOK_P0P1_VI.md`.
- Gộp `09_ORDER_HISTORY_IMPROVE_PLAN.md` → `IMPORT_POLL_TRACKERS_VI.md` §7.
- Xóa `DIAGRAM/pis_e2e_current_copy.*`.
- Xóa meta audit `SPECIFICATION/DOCUMENTATION_AUDIT_*`, `LOG_REPORT/DOCUMENTATION_AUDIT_*`.

**Pass 13 (tùy chọn):** rút gọn `PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md` → trỏ `IMPORT_CHUNK_AND_BULK_INSERT.md` khi Product import ổn định.

---

## 7) Ma trận quyết định (dùng cho mọi đợt sau)

| Risk     | Điều kiện xóa                                                                                   |
| -------- | ----------------------------------------------------------------------------------------------- |
| **Thấp** | Không route/view/mix reference; nội dung đã ở living doc hoặc git; duplicate/backup             |
| **TB**   | Doc plan đã done nhưng cần 1 dòng trong LEGACY_ARCHIVE + link thay thế                          |
| **Cao**  | Backlog mở, config/feature flag, test matrix, hoặc PHP có thể load động (module, policy, event) |

**Không xóa PHP application code trong pass doc** — xem [`LEGACY_PHP_AND_ASSET_CANDIDATES_2026_05_27.md`](LEGACY_PHP_AND_ASSET_CANDIDATES_2026_05_27.md).
