# Phase 2 — Lập kế hoạch sản xuất (sau Sales Order)

_Cập nhật: **21/05/2026** · Phase 1: [`PHASE1_PM_STATUS_LIVE_VI.md`](./PHASE1_PM_STATUS_LIVE_VI.md) · Spec Gary: [`PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`](../PROJECT%20BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md) (Phần B)_

---

## 1. Phase 1 đã xong chưa?

### Kết luận: **Đủ để chốt Phase 1 (go-live báo giá gia công)**

| Tiêu chí PM (Phase 1)                       | Trạng thái          |
| ------------------------------------------- | ------------------- |
| BOM + công thức trên báo giá                | ✅                  |
| Gửi duyệt → Tổng giám đốc → Phó tổng giá    | ✅                  |
| Chặn chuyển đơn bán nếu chưa duyệt          | ✅                  |
| Bật/tắt theo công ty (Module Settings)      | ✅                  |
| % lãi tối thiểu (Finance settings)          | ✅                  |
| Tìm báo giá tương tự, thông báo, dịch quyền | ✅                  |
| Workspace OEM trên chi tiết báo giá         | ✅ (~75% — đủ dùng) |
| Full test suite                             | ✅ 321 pass         |

**Còn tùy chọn (không chặn đóng Phase 1):** PDF có BOM, email đẹp hơn, mở rộng Estimate Request.

**Mốc chuyển sang Phase 2:** Sales Order đã tạo từ báo giá **đã duyệt đủ** (`estimates_phase1_review` bật cho tenant gia công).

---

## 2. Phase 2 là gì? (một câu)

**Sau khi đã có đơn bán hàng**, xưởng cần biết: **làm bao nhiêu, dùng công thức nào, cần bao nhiêu đường/kem/cà phê, trừ/nhập kho thế nào.**

Phase 1 trả lời: _“Deal này có được bán không?”_  
Phase 2 trả lời: _“Xưởng chuẩn bị và chạy sản xuất thế nào?”_

```text
Báo giá (đã duyệt) → Đơn bán hàng (SO)  ← kết thúc Phase 1
        ↓
Lệnh sản xuất + BOM + tính nguyên liệu + batch + tồn kho  ← Phase 2
        ↓
Giao hàng + Hóa đơn  ← Phase 3 (đã có phần lớn trong ERP)
```

---

## 3. Đã có sẵn trong hệ thống (~MVP Production)

Module **`Production`** (`/account/production/...`) đã có nền (ước ~**70–80%** planning core):

| Hạng mục                        | Ghi chú ngắn                     |
| ------------------------------- | -------------------------------- |
| BOM master (CRUD)               | Production → Bill of Materials   |
| Lệnh sản xuất (draft → release) | Gắn SO, snapshot BOM khi release |
| Planned RM từ BOM               | Có test; chia batch cơ bản       |
| Batch, post RM / post FG        | Tiêu thụ NL, nhận TP             |
| Trace, FG policy, rework cơ bản | Phase 2 “mỏng” đã có trong code  |
| Copy BOM label, UOM hiển thị    | Đã chỉnh dần                     |

**Tài liệu kỹ thuật:** `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`, `FUNC_IMPROVE/PRODUCTION_MODULE_PROGRESS_REPORT_EN.md`  
**UI runbook:** `PROJECT BIOMIXING/UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION_VI.md`

---

## 4. Còn thiếu / cần làm rõ (theo PM Gary + Biomixing)

### Ưu tiên cao (P0 — Gary “ưu tiên cao”)

| #    | Việc                                | Ý với user                                                   | Gợi ý module / màn                                                                          |
| ---- | ----------------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------------------------------------- |
| P0-1 | **Lọc dropdown BOM**                | Chọn thành phẩm / NL / bao bì **không trộn** → ít chọn nhầm  | Production BOM form, estimate copy BOM — **✅ đã có** (`forBomOutput` / `forBomComponents`) |
| P0-2 | **Product type** nhất quán          | `raw_material`, `finished_goods`, `packaging`… dùng đúng chỗ | Products + validation BOM — **✅ đã có** (enum + test)                                      |
| P0-3 | **Tự tính NVL theo SL đơn**         | 3.000 gói → 150 kg đường… (BOM × SL SO)                      | Lệnh SX — **✅ 2026-05-20** bảng tổng NL trên chi tiết lệnh                                 |
| P0-4 | **Trừ NL + nhập TP** sau hoàn thành | Tồn kho khớp thực tế                                         | Batch post RM/FG — **✅ 2026-05-20** checklist 5 bước trên màn lô                           |
| P0-5 | **Màn lệnh SX dễ đọc**              | Không RM/FG/consumption; rõ đã trừ kho chưa                  | Labels VI cập nhật (batch workflow + NL/TP)                                                 |

### Ưu tiên trung bình (P1)

| #    | Việc                                                                                                        |
| ---- | ----------------------------------------------------------------------------------------------------------- |
| P1-1 | Liên kết SO → tạo lệnh SX **một luồng** (nút / wizard từ đơn bán) — **✅ 2026-05-20** (nút từ SO + prefill) |
| P1-2 | Hiển thị **thiếu tồn** / gợi ý mua hàng — **✅ 2026-05-20** (shortfall + link tạo PO nếu có quyền Purchase) |
| P1-3 | **Waste %** trên dòng BOM — **✅ 2026-05-20** (cột BOM + tổng NL × (1 + % hao hụt))                         |
| P1-4 | Prefill lệnh SX từ SO + báo giá liên kết — **✅ 2026-05-20** (TP, SL, BOM mặc định, gợi ý từ estimate)      |

### Ưu tiên thấp / Phase 2+ (P2)

| #    | Việc                                                                                                                                                             |
| ---- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P2-1 | **UOM + UOM price** — **✅ code 2026-05-20/21** (A→B→C); **UAT** còn lại. Chi tiết: [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md) |
| P2-2 | Phiên bản BOM V1/V2, lưu trữ                                                                                                                                     |
| P2-3 | Multi-batch planning nâng cao                                                                                                                                    |
| P2-4 | Receiving QC GRN, CCP cứng, AI validate certs (proposal Phase 3)                                                                                                 |

---

## 5. Kế hoạch triển khai đề xuất (thứ tự)

### Sprint A — Gary UX + đúng số (2–3 tuần dev ước lượng)

1. **P0-1** Lọc dropdown BOM (FG vs component)
2. **P0-2** Rà soát product type trên catalog + BOM
3. **P0-3** Tính planned materials từ `SL đơn × định mức BOM` (hiển thị bảng tổng NL)
4. **P0-5** Label tiếng Việt trên Production (khớp PM_REQUEST)
5. Test: `.\scripts\test.ps1` + test Production hiện có

### Sprint B — Kho & luồng SO (2 tuần)

1. **P0-4** Hardening post RM/FG + message rõ trên batch
2. **P1-1** Nút “Tạo lệnh sản xuất” từ Sales Order (đã có estimate_id)
3. **P1-2** Cảnh báo thiếu tồn (đọc warehouse)
4. UAT với PM: Oldtown 3.000 gói end-to-end

### Sprint C — Polish (tùy PM)

1. P1-3 waste %
2. P2-\* theo feedback pilot

### Sprint D — P2-UOM (sau khi PM duyệt epic; ~3 sprint)

**Không chặn đóng Phase 2 MVP** — làm khi user/PM cần màn Sản phẩm như KiotViet.

| Giai đoạn | Nội dung ngắn                                                                                            |
| --------- | -------------------------------------------------------------------------------------------------------- |
| **A**     | UI Product (gate «+ Thêm đơn vị» sau giá gốc; UOM price; chọn `unit_types`) → `product_unit_conversions` |
| **B**     | SO/PO/GRN + strict conversion + seed pilot Oldtown                                                       |
| **C**     | BOM báo giá / tổng NL lệnh SX + (tùy) bật shadow UOM                                                     |

Chi tiết: [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md).

---

## 6. Điều kiện hoàn thành Phase 2 (Definition of Done)

- [x] Từ **SO đã chốt** (từ báo giá Phase 1), tạo được **lệnh sản xuất** gắn BOM đúng thành phẩm.
- [x] Hệ thống **hiển thị tổng nguyên liệu** cần cho SL đơn (ví dụ 3.000 gói → kg đường/kem/cà phê).
- [x] Dropdown BOM **không** trộn NL với thành phẩm.
- [x] Hoàn thành batch → **trừ NL, cộng TP** đúng trên tồn (có checklist UX trên màn lô).
- [ ] PM/UAT ký trên staging với 1 case Oldtown (hoặc case thật).
- [ ] Regression: `.\scripts\test.ps1` (full) pass; test Production liên quan pass.

**Chưa bắt buộc trong Phase 2:** Giao hàng, hóa đơn, AI, Project tasks in MS sense.

---

## 7. Phase 3 (nhắc ranh giới — không làm trong Phase 2)

- Giao hàng (DO), hóa đơn, thanh toán — ERP đã có luồng B2B.
- QC sàn xưởng (cân, trộn, đóng gói) — việc người; ERP chỉ ghi số/lô.
- Chi tiết: `PROJECT BIOMIXING/BIOMIXING_PHASES_1_4_SUMMARY_VI.md`

---

## 8. Cấu hình & demo (sau Phase 1)

| Bước | Việc                                                  |
| ---- | ----------------------------------------------------- |
| 1    | Phase 1: bật **Duyệt báo giá gia công** + quyền duyệt |
| 2    | Tạo SO từ báo giá đã duyệt                            |
| 3    | **Production** → BOM master → Lệnh SX từ SO           |
| 4    | Release → batch → post RM/FG                          |

**Test:** `.\scripts\test.ps1 phase1` (Phase 1) · test Production riêng khi sửa module Production.

---

## 9. Tài liệu tham chiếu

| File                                                   | Dùng khi                           |
| ------------------------------------------------------ | ---------------------------------- |
| `PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`          | Phần B — Gary                      |
| `PROJECT BIOMIXING/PHASE1_2_BUSINESS_FLOW_PM_VI.md`    | Luồng PM 1+2                       |
| `PROJECT BIOMIXING/BIOMIXING_PHASES_1_4_SUMMARY_VI.md` | Bản đồ 4 phase                     |
| `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`           | Chi tiết dev Production            |
| `FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`      | Epic đa đơn vị SP + kho (KiotViet) |
| `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`             | UAT matrix                         |

---

_Mở Phase 2 khi Phase 1 đã UAT trên tenant Biomixing (báo giá → duyệt → SO). Không cần chờ polish PDF/email Phase 1._
