# Kiểm tra tài liệu Biomixing (`FUNC_IMPROVE/` — file `BIOMIXING_*`) — mức độ lỗi thời & file thay thế (2026)

**Cập nhật:** 2026-05-21  
**Audit đầy đủ 3 thư mục:** [`DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](./DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md) (`PROJECT BIOMIXING` + `FUNC_LOGIC` + `FUNC_IMPROVE`)

**Mục đích:** Sau khi **SO / PO / Sales DO / Invoice / Warehouse** (đa kho, batch, reservation, inbound canonical) được hoàn thiện nhiều trên Hub/staging, một số file **`BIOMIXING_*` ở gốc `FUNC_IMPROVE/`** **mô tả trạng thái cũ**. Bảng dưới giúp **không đọc nhầm** khi chuẩn bị **module Production**.

**Living status (code):** [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)  
**Baseline mới (đọc trước):** `BIOMIXING_BASELINE_PREP_2026_VI.md`  
**Chỉ mục tiếng Anh:** `BIOMIXING_PREP_INDEX_EN.md`

---

## Bảng rà soát

| File                                              | Niên đại / ghi chú                     | Vì sao có thể lỗi thời                                                                                                                                                                  | Thay thế / bổ sung đọc                                                                                               |
| ------------------------------------------------- | -------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| `BIOMIXING_GAP_ANALYSIS.md`                       | 2026-02-13 + **chỉnh 2026-04**         | Bản gốc ghi **Warehouse: Partial** — đã **cập nhật** bảng Extension + dòng Critical **Batch Tracking** theo `warehouse_product_batches` + DO; notice đầu file vẫn nhắc đọc kèm baseline | `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`, `WAREHOUSE_INDEX.md`; giữ cho **gap Biomixing** (BOM, CCP, QC receiving…) |
| `BIOMIXING_FLOW_CRACEVA_GAP.md`                   | Trước nâng cấp kho + **chỉnh 2026-04** | Một số ô “**Một phần**” theo **HACCP/Production** vẫn đúng; **đã chỉnh** dòng #28 (trace batch), §“Đã có”, footer trỏ baseline                                                          | Đọc kèm `BIOMIXING_BASELINE_PREP_2026_VI.md` §3; flow xưởng **vẫn dùng được**                                        |
| `BIOMIXING_PROPOSAL_REVISED.md`                   | Proposal marketing                     | Không phải spec kỹ thuật; có thể nhắc AI/omni-channel                                                                                                                                   | Chỉ dùng sales narrative; kỹ thuật lấy từ FUNC_LOGIC + baseline mới                                                  |
| `BIOMIXING_DEV_PLAN.md`                           | Đã cập nhật 2026-04                    | Phần lớn **đúng**; vẫn cần chỉnh dần khi Production kickoff                                                                                                                             | Giữ làm roadmap; **bổ sung** đọc baseline 2026 trước Phase 0                                                         |
| `BIOMIXING_FLOW_CONCEPTS_VI.md`                   | 2026-05                                | Giải thích nghiệp vụ luồng & tồn (RM/FG, PO, DO); **không** thay FUNC_LOGIC                                                                                                             | Đọc trước playbook nếu team mới; cập nhật khi đổi rule DO/reserve                                                    |
| `BIOMIXING_DOMAIN_INTEGRATION.md`                 | Kiến trúc + **ref 2026-04**            | Bảng domain hợp lệ; footer đã trỏ baseline / audit / `WAREHOUSE_INDEX`                                                                                                                  | Đọc sau baseline                                                                                                     |
| `BIOMIXING_PRODUCTION_TIMELINE_*_EN.md`           | Ước lượng PM (đã dừng dùng)            | Trùng vai trò với plan chính; dễ lệch khi scope đổi                                                                                                                                     | **Đã xóa theo yêu cầu**; dùng `BIOMIXING_DEV_PLAN.md` + status file mới                                              |
| `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md`        | Demo + **ref baseline 2026-04**        | Bảng **After demo** trỏ `BIOMIXING_BASELINE_PREP_2026_VI.md` trước khi scope Production                                                                                                 | Giữ                                                                                                                  |
| `BIOMIXING_DEMO_TIMELINE_FROM_DATA_HANDOFF_EN.md` | Timeline demo (đã dừng dùng)           | Trùng timeline PM, không còn là nguồn sự thật trạng thái triển khai                                                                                                                     | **Đã xóa theo yêu cầu**; giữ `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md` và `BIOMIXING_DEMO_SCRIPT.md`                |
| `BIOMIXING_PROTOTYPE_PLAN_VI.md`                  | Prototype + **ref 2026-04**            | Đã **thêm** Tham chiếu → baseline + `WAREHOUSE_INDEX`                                                                                                                                   | Giữ                                                                                                                  |
| `BIOMIXING_DEMO_SCRIPT.md`                        | Kịch bản demo + **note 2026-04**       | Đã **thêm** rehearsal note (DO confirm/ship, batch/expiry)                                                                                                                              | Cập nhật thêm khi UI đổi                                                                                             |
| `info.md`                                         | Tùy                                    | Chỉ URL website khách; không chứa link ERP                                                                                                                                              | Thêm link nội bộ ở README/prep index nếu cần                                                                         |

---

## Kết luận

- **Không xóa** hết file cũ: **flow Biomixing** và **gap HACCP/BOM** vẫn có giá trị.
- **Bắt buộc** thêm lớp **baseline nền tảng 2026** trước khi lên kế hoạch code `Modules/Production`.
- Mọi nhận định “kho chỉ Partial / chưa batch” trong tài liệu **trước Q2 2026** cần **đối chiếu** `ERP_SO_PO_DO_INV_WH_QA_VI.md` (cập nhật 2026-04-23).
