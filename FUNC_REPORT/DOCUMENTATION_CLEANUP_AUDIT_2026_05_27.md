# Documentation Cleanup Audit — 2026-05-27

## Trạng thái kết thúc (báo PM / team)

| Hạng mục                                                           | Trạng thái                                                                 |
| ------------------------------------------------------------------ | -------------------------------------------------------------------------- |
| **Pass 1** — plan Production đã triển khai (shortage, reserve)     | **Xong**                                                                   |
| **Pass 2** — dọn `FUNC_IMPROVE` + `FUNC_REPORT` (17 file)          | **Xong**                                                                   |
| Living doc + index canonical                                       | **Xong**                                                                   |
| Link chéo tới file đã xóa (ngoài changelog lịch sử)                | **Xong** — không còn link `[...](PHASE2_PM_PLAN...)`                       |
| Ma trận bảo tồn nghiệp vụ                                          | **Xong** — `FUNC_IMPROVE/LEGACY_ARCHIVE.md`                                |
| **Pass 3** — `FUNC_LOGIC/LEGACY_ARCHIVE.md`, INDEX audit snapshots | **Xong**                                                                   |
| **Doc ↔ code** — material shortage status scopes                   | **Xong** — UI filter + service; mặc định `active` (Released + In progress) |
| **Pass 4–5** — gộp UX/import/Biomixing hub/staging quick ref       | **Xong** (2026-05-27)                                                      |
| **Pass 6** — xóa legacy đã hoàn thiện (audit, gap, archive)        | **Xong** (2026-05-27)                                                      |
| **Pass 7** — gộp/xóa backlog archive (UX, import, AI prompts)      | **Xong** (2026-05-27)                                                      |
| **Pass 8** — rút gọn `05_SO_DO_PO` + dọn `PROJECT BIOMIXING`       | **Xong** (2026-05-27)                                                      |

**Kết luận:** Pass 1–8 hoàn tất. Chi tiết: `FUNC_IMPROVE/LEGACY_ARCHIVE.md` · `PROJECT BIOMIXING/LEGACY_ARCHIVE.md`.

---

Scope reviewed:

- `FUNC_BUG`
- `FUNC_IMPORT`
- `FUNC_IMPROVE`
- `FUNC_LOGIC`
- `FUNC_REPORT`
- `FUNC_TEST`
- `PROJECT BIOMIXING`
- `PROJECT MAOLIN`

---

## 1) Documentation Audit

Current state (doc files only):

- `FUNC_BUG`: 19
- `FUNC_IMPORT`: 5
- `FUNC_IMPROVE`: 63 (before cleanup)
- `FUNC_LOGIC`: 64
- `FUNC_REPORT`: 4
- `FUNC_TEST`: 2
- `PROJECT BIOMIXING`: 30
- `PROJECT MAOLIN`: 1

Observation:

- Trùng mục đích giữa `plan`/`audit`/`living` ở `FUNC_IMPROVE`.
- Một số tài liệu lịch sử/backup quá dài không còn phục vụ vận hành hiện tại.

---

## 2) Documentation Sync

Đã đồng bộ hướng đọc về “living operational docs”:

- Thêm canonical Production live doc: `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`
- Cập nhật index để ưu tiên đọc doc vận hành thay vì doc triển khai cũ:
    - `FUNC_LOGIC/INDEX.md`
    - `FUNC_IMPROVE/INDEX.md`
    - `FUNC_IMPROVE/PRODUCTION_MODULE_PROGRESS_REPORT_EN.md`
- Cập nhật test-case reference:
    - `FUNC_IMPROVE/19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`
- Cập nhật flow khái niệm Biomixing theo trạng thái code hiện tại:
    - `FUNC_IMPROVE/BIOMIXING_FLOW_CONCEPTS_VI.md`

---

## 3) Documentation Refactoring

Refactor đã thực hiện:

- Chuyển tri thức Production từ dạng implementation plan sang dạng vận hành:
    - New: `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`
- Giữ flow vận hành trong `PROJECT BIOMIXING` (mmd/html) để PM/QA chạy test mà không rà code.

---

## 4) Living Documentation (Canonical)

### Production vận hành

- `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` (SSOT nghiệp vụ vận hành Production hiện tại)
- `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`
- `PROJECT BIOMIXING/PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd`
- `FUNC_IMPROVE/19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`

### Biomixing test & UAT

- `FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`
- `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`

---

## 5) Spec Reconciliation

Đã reconcile theo trạng thái code hiện tại:

- Reserve RM tại Release: **đã triển khai core**
- Draft: **không reserve**
- Cancel Released: **release reservation**
- Post RM: **consume reservation khi toàn bộ batch RM đã post**
- Material shortage:
    - Filter status: `active` (Released + In progress, mặc định), `draft`, `all`, `released`, `in_progress`
    - Filter kho NL + NVL + chỉ hiện dòng thiếu
    - Completed/Cancelled: không tính

---

## 6) Doc-to-Code Validation

Đối chiếu đã cập nhật trong doc vận hành:

- `releaseOrder()` gọi reserve + assert availability
- `cancelOrder()` release reservation nếu order ở Released
- `postConsumptionsForBatch()` consume reservation khi không còn batch pending

Source of truth for operations:

- `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`

---

## 7) Knowledge Base Cleanup

Đã xóa legacy rõ ràng:

- `FUNC_IMPROVE/18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN_VI.md`
- `FUNC_IMPROVE/19_PRODUCTION_RM_RESERVE_AT_RELEASE_PLAN_VI.md`
- `FUNC_IMPROVE/CURSOR_AND_GIT_ACTIVITY_REPORT_2026-04-01_TO_2026-05-14.md`
- `FUNC_IMPROVE/CURSOR_AND_GIT_ACTIVITY_REPORT_2026-04-01_TO_2026-05-14 - bk.md`
- `scripts/translate_cursor_activity_report_to_en.py`
- `scripts/translate_cursor_activity_report_pass2.py`

---

## 8) Technical Debt Cleanup (Documentation)

Debt reduced:

- Giảm phụ thuộc vào doc triển khai đã hoàn tất.
- Tạo 1 điểm đọc vận hành Production cho PM/QA/dev.
- Loại bỏ tài liệu backup/history không còn giá trị vận hành.

## Pass 2 (2026-05-27) — đã thực hiện

**Đã xóa 17 file** (xem bảng đầy đủ trong `FUNC_IMPROVE/LEGACY_ARCHIVE.md`):

- FUNC*IMPROVE: plan/audit/prototype superseded (gồm `BIOMIXING_DEV_PLAN.md`, `PHASE2_PM_PLAN_VI.md`, `15*\*\_FIX_PLAN`, …)
- FUNC_REPORT: `PM report 1.md`, `PM report 2.md`

**Đã tạo / cập nhật:**

- `FUNC_IMPROVE/LEGACY_ARCHIVE.md` — manifest retire + link thay thế
- `FUNC_IMPROVE/INDEX.md` — canonical reading order
- Liên kết chéo trong ~25 file living (GAP_STATUS, PREP_INDEX, PROJECT BIOMIXING, UAT, …)

**Số file FUNC_IMPROVE sau pass 2:** ~43 markdown (từ ~60).

## Pass 3 (2026-05-27) — đã thực hiện

- `FUNC_LOGIC/LEGACY_ARCHIVE.md` — chỉ mục snapshot audit + one-time analysis
- `FUNC_LOGIC/INDEX.md` — link tới LEGACY_ARCHIVE
- **Code:** `ProductionMaterialSummaryService` scopes (`active` default), UI filter status + warehouse trên material shortage summary
- **Test:** `ProductionMaterialShortageSummaryTest.php` cập nhật
- **Doc:** `PRODUCTION_OPERATIONS_LIVE_VI.md` §3 khớp code

---

## 9) Rule áp dụng từ hôm nay

Khi một chức năng đã hoàn tất:

1. Giữ lại:
    - doc vận hành (how module runs),
    - flow UAT,
    - lưu ý vận hành/rủi ro.
2. Loại bỏ:
    - doc plan/implementation trung gian đã hết vòng đời,
    - file backup/history không dùng cho vận hành.
3. Cập nhật index canonical ngay trong cùng batch.
