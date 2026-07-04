# Biomixing / 神農生技 — Hồ sơ công ty (tóm tắt nội bộ)

_Tài liệu gộp thông tin từ deck marketing, báo giá NetSuite và ngữ cảnh pilot Craveva ERP. Dùng khi onboard dev/BA/PM — **không** thay hợp đồng hay proposal pháp lý._

**Cập nhật:** 2026-05-27  
**Nguồn chính trong thư mục này:**

| File                                                                                                                                 | Nội dung                              |
| ------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------- |
| [`Pioneering Sustainable Farming Solutions.pdf`](./Pioneering%20Sustainable%20Farming%20Solutions.pdf)                               | Deck giới thiệu công ty (11/2025)     |
| [`Shennong Biotechnology_NetSuite Import Quotation Sheet.pdf`](./Shennong%20Biotechnology_NetSuite%20Import%20Quotation%20Sheet.pdf) | Báo giá triển khai NetSuite (12/2025) |
| [`BIOMIXING_PROPOSAL_REVISED.md`](./BIOMIXING_PROPOSAL_REVISED.md)                                                                   | Proposal Craveva ERP                  |
| [`PM_YEU_CAU_TONG_HOP.md`](./PM_YEU_CAU_TONG_HOP.md)                                                                           | Yêu cầu PM luồng báo giá gia công     |

**Luồng vận hành trên Hub:** [`FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md`](../FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md)

---

## 1. Biomixing là công ty gì? (một câu)

**Công ty công nghệ sinh học / nông nghiệp bền vững** (Đài Loan, khu vực Pingtung), phát triển và bán **probiotic, phụ gia thức ăn, giải pháp sức khỏe động vật & thủy sản** — đồng thời có **nhà máy sản xuất** (BOM, lệnh sản xuất, kho lô) và bán **B2B** qua đại lý / đối tác. Một phần đơn hàng là **gia công theo công thức khách** (luồng báo giá + duyệt nội bộ trên Craveva).

**Không** chỉ là xưởng thuê gia công thuần túy, **cũng không** chỉ bán hàng không sản xuất.

---

## 2. Tên, pháp nhân và thương hiệu

| Hạng mục                     | Chi tiết                                                                |
| ---------------------------- | ----------------------------------------------------------------------- |
| Thương hiệu / marketing      | **Biomixing** — slogan deck: _Pioneering Sustainable Farming Solutions_ |
| Pháp nhân (báo giá NetSuite) | **神農生技股份有限公司** (Shennong Biotechnology Co., Ltd.)             |
| Mã số thuế (TW)              | 27685048                                                                |
| Địa chỉ (báo giá)            | 長治鄉神農路8號 (huyện Changzhi, **Pingtung**, Đài Loan)                |
| Email liên hệ (báo giá)      | tina@biomixin.com → **BiomiXin / Biomixing cùng nhóm**                  |
| Pháp nhân vận hành           | **2 công ty:** Đài Loan + **Singapore** (theo báo giá NetSuite 2026)    |
| Địa điểm triển khai ERP      | **屏東縣** (Pingtung)                                                   |

---

## 3. Lĩnh vực & sản phẩm (từ deck 11/2025)

### 3.1 Định vị

- ~**20 năm** R&D probiotic / biotech trong nông nghiệp & chăn nuôi.
- **Sứ mệnh:** Giá trị bền vững qua giải pháp đổi mới trong **sức khỏe động vật và dinh dưỡng**.
- **Tầm nhìn:** Góp phần giải quyết **khủng hoảng lương thực toàn cầu** bằng đổi mới khoa học.

### 3.2 Ví dụ sản phẩm / bằng chứng đã nhấn mạnh

| Chủ đề                | Mô tả ngắn                                                                             |
| --------------------- | -------------------------------------------------------------------------------------- |
| **EHPurge**           | Giải pháp bệnh **EHP** trên tôm (_Enterocytozoon hepatopenaei_); trial Tainan, Hualien |
| **PRRS**              | Giảm tỷ lệ / tác động **PRRSV** trên đàn heo                                           |
| **Methane**           | Giảm phát thải methane ở bò                                                            |
| **Tảo / nông nghiệp** | Đổi mới tảo, cải thiện năng suất cây (trà ô long, sầu riêng, cà phê)                   |
| Nền tảng khoa học     | Module công nghệ (ví dụ _Clostridium butyricum_, _Bacillus amyloliquefaciens_…)        |

### 3.3 Kênh bán & thị trường mục tiêu (deck)

- Tìm **đối tác chiến lược**: nhà phân phối, integrator nuôi trồng quy mô lớn.
- Ngành: **chăn nuôi, thủy sản, nông nghiệp, thức ăn thú cưng**.
- Khu vực ưu tiên: **Đông Nam Á, Trung Đông, Châu Âu, Châu Phi**.

---

## 4. Hai kiểu kinh doanh trên ERP

Cùng một hệ thống, **hai luồng** (xem [`BIOMIXING_MULTITENANT_RISKS.md`](../FUNC_IMPROVE/BIOMIXING_MULTITENANT_RISKS.md)):

| Kiểu                            | Mô tả                                                                                                                      | Ví dụ                                                                                          |
| ------------------------------- | -------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| **B2B — hàng có sẵn**           | Báo giá / đơn bán dòng sản phẩm catalogue → giao hàng → hóa đơn                                                            | Đại lý đặt **500 kg EHPurge**                                                                  |
| **Gia công / đơn có công thức** | Báo giá có **định mức nguyên liệu** + dòng bán → duyệt Tổng giám đốc + Phó tổng giám đốc giá → đơn bán → **lệnh sản xuất** | Oldtown: cà phê 3-in-1 custom; FreshTea: BioMix Detox 350ml; Siam Shrimp: custom probiotic mix |

**Trên form báo giá (khi bật duyệt gia công):**

- **Khối trên:** công thức + dòng nguyên liệu cho **1 đơn vị thành phẩm** (chi phí nội bộ, margin).
- **Khối dưới:** dòng bán B2B — khách đặt **bao nhiêu × giá bán** (chuyển sang đơn bán).

Hai bảng **không thay nhau** — cả hai cần cho đơn gia công.

---

## 5. Quy trình end-to-end (tóm tắt Phase 1→4)

Theo [`BIOMIXING_PHASES_1_4_SUMMARY.md`](./BIOMIXING_PHASES_1_4_SUMMARY.md):

```text
Phase 1 — Báo giá & duyệt giá/công thức → Đơn bán (Sales Order)
Phase 2 — Lệnh sản xuất, định mức, kho NL/TP, release
Phase 3 — Chạy lô, tiêu hao NL, nhập thành phẩm, QC, COA (vận hành + chứng từ)
Phase 4 — Giao hàng, hóa đơn, thanh toán
```

**Lưu ý:** Khách **không** đặt lệnh sản xuất trực tiếp — khách chốt **đơn bán**; nội bộ mới lập lệnh sản xuất.

---

## 6. Thuật ngữ thường gặp

| Viết tắt                            | Nghĩa đầy đủ                             | Trong ngữ cảnh Biomixing                        |
| ----------------------------------- | ---------------------------------------- | ----------------------------------------------- |
| **QC**                              | Kiểm soát chất lượng                     | Kiểm lô sau sản xuất/đóng gói — đạt / không đạt |
| **COA**                             | Certificate of Analysis (giấy phân tích) | Chứng nhận kết quả kiểm tra kèm hàng giao khách |
| **BOM**                             | Bill of materials (định mức nguyên liệu) | NL cần cho 1 đơn vị thành phẩm                  |
| **Gia công** (gọi OEM trong doc PM) | Sản xuất theo spec/công thức khách       | Khác với chỉ bán hàng có sẵn trong kho          |

Trên Hub pilot: QC/COA đầy đủ là **roadmap**; phần số lô / lệnh SX / kho đã có trong module Production.

---

## 7. Nhu cầu ERP (từ báo giá NetSuite 12/2025)

Dự án **2026\_神農\_NS導入案** — phạm vi tham chiếu khi thiết kế Craveva:

| Module          | Nội dung chính                                                     |
| --------------- | ------------------------------------------------------------------ |
| Bán hàng B2B    | Khách/đại lý, đơn bán, phiếu giao, trả hàng, bảng giá              |
| Mua hàng        | Nhà cung cấp, sản phẩm (NL/TP/BTP), PO, nhận hàng                  |
| Kho             | Kho, kiểm kê, điều chuyển, **quản lý lô/serial**                   |
| Tài chính       | AP/AR, sổ nhật ký, ngân sách, đa công ty TW + SG                   |
| Tài sản cố định | Mua, khấu hao, IFRS16 thuê tài chính                               |
| **Sản xuất**    | Lệnh SX, lĩnh NL, nhập TP, **BOM + phiên bản BOM**, giờ công chuẩn |

**Craveva pilot** bổ sung so với NetSuite “chuẩn”: **báo giá có định mức trên estimate**, duyệt 2 cấp, chặn chuyển đơn bán, copy định mức từ Production.

---

## 8. Ví dụ nghiệp vụ trong tài liệu dự án

### 8.1 Oldtown (PM gốc)

Chi tiết từng bước + bảng BOM/dòng bán: [`BIOMIXING_QUOTATION_EXAMPLES.md`](./BIOMIXING_QUOTATION_EXAMPLES.md)

|                   |                                  |
| ----------------- | -------------------------------- |
| Khách             | Oldtown White Coffee             |
| Thành phẩm        | Cà phê 3-in-1 150g/gói (custom)  |
| Công thức / 1 gói | Đường 50g + Kem 30g + Cà phê 70g |
| Đơn hàng          | 3.000 gói                        |

### 8.2 FreshTea (bối cảnh duyệt)

Chi tiết từng bước + duyệt 2 cấp + COA/nhãn lô: [`BIOMIXING_QUOTATION_EXAMPLES.md`](./BIOMIXING_QUOTATION_EXAMPLES.md)

|                  |                                                                                |
| ---------------- | ------------------------------------------------------------------------------ |
| Khách            | Chuỗi đồ uống FreshTea (ví dụ giả định trong doc)                              |
| Nhu cầu          | 2.000 chai BioMix Detox 350ml, giao 10 ngày                                    |
| Yêu cầu đặc biệt | COA, nhãn lô truy xuất                                                         |
| Duyệt            | Tổng giám đốc: có nhận deal/rush không; Phó tổng giám đốc giá: margin đủ không |

### 8.3 Demo script Craveva

|          |                                              |
| -------- | -------------------------------------------- |
| Khách    | Siam Shrimp Distributors                     |
| Sản phẩm | Custom Probiotic Mix / EHPurge 500 kg        |
| Luồng    | Estimate → duyệt → đơn bán → sản xuất → giao |

---

## 9. Liên hệ Craveva vs NetSuite

|                                   | NetSuite (báo giá FY Technology)                         | Craveva (pilot repo)                                                              |
| --------------------------------- | -------------------------------------------------------- | --------------------------------------------------------------------------------- |
| Trạng thái                        | Báo giá triển khai 2026, ~NT$3.06M (chưa xác nhận đã ký) | Đang pilot trên Hub staging                                                       |
| Điểm mạnh Craveva cho profile này | —                                                        | Báo giá gia công + BOM trên estimate, duyệt President/VP, Production tích hợp B2B |

---

## 10. Ai đọc file này tiếp theo?

| Vai trò        | Đọc thêm                                                                                                                                                                        |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| PM / BA        | [`PM_YEU_CAU_TONG_HOP.md`](./PM_YEU_CAU_TONG_HOP.md), [`PHASE_BUSINESS_CONTEXT_EXAMPLE.md`](./PHASE_BUSINESS_CONTEXT_EXAMPLE.md)                                          |
| Dev / QA       | [`UI_RUNBOOK_PHASE1_QUOTATION_TO_SO.md`](./UI_RUNBOOK_PHASE1_QUOTATION_TO_SO.md), [`FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md`](../FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md) |
| Vận hành xưởng | [`PRODUCTION_MODULE_SOP.md`](./PRODUCTION_MODULE_SOP.md), [`FUNC_LOGIC/PRODUCTION_BUSINESS.md`](../FUNC_LOGIC/PRODUCTION_BUSINESS.md)                 |

---

_Cập nhật khi có thông tin pháp nhân / hợp đồng mới từ khách hàng._
