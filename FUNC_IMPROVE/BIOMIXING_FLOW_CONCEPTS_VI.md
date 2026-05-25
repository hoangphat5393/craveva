# Luồng & khái niệm Production — ghi chép nội bộ (Craveva / Biomixing)

**Mục đích:** Tóm các **khái niệm** và **lưu ý vận hành/kho** thường gây nhầm giữa B2B, PO và Production — bổ sung cho playbook triển khai; **không** thay thế `FUNC_LOGIC` hay spec kỹ thuật chi tiết.

**Đọc cùng:** **`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`** (luồng bước chuẩn — LIVE) · `BIOMIXING_PLAYBOOK_P0P1_VI.md` · `BIOMIXING_BASELINE_PREP_2026_VI.md`.

**Cập nhật:** 2026-05

---

## Từ vựng nhanh

| Thuật ngữ | Ý nghĩa |
| --------- | ------- |
| **RM (Raw Materials)** | Nguyên liệu (SKU trong `products`, dùng cho đầu vào SX). |
| **FG (Finished Goods)** | Thành phẩm (SKU trong `products`, đầu ra SX và thường là hàng giao DO). |
| **BOM** | Bill of Materials — định mức RM cho 1 SKU FG theo một **phiên bản**. |
| **BOM version** | Phiên bản công thức của cùng 1 SKU FG — lệnh SX nên **chốt** dùng version nào để không bị sai khi đổi công thức sau đó. |
| **Production Order / Batch** | Lệnh sản xuất và lô sản xuất (số batch SX, thời gian, operator… theo MVP). |

---

## Thành phẩm đã có trong danh mục — Receive FG không “tạo SKU mới”

- **SKU FG phải tồn tại** trong master `Product` trước khi nhận thành phẩm.
- Bước **Receive FG** làm **tăng inventory** của **đúng `product_id` đó** và thường tạo **lô/batch FG mới** (truy xuất) — **không** đồng nghĩa tự sinh dòng mới trong catalogue trừ khi nghiệp vụ riêng yêu cầu (ngoài MVP).

---

## Vì sao **Consume RM** trừ tồn mà **Receive FG** lại cộng tồn — không mâu thuẫn

Đó là **hai loại tồn khác nhau** (khác `product_id`):

- **Consume RM:** **trừ** tồn các **nguyên liệu** (RM-A, RM-B…).
- **Receive FG:** **cộng** tồn **thành phẩm** (ví dụ EHPurge).

Vật lý: RM “biến thành” FG — trong sổ kho là **xuất RM + nhập FG**, không phải cùng một dòng tồn trừ rồi cộng lại.

**So sánh nhanh với PO:** Cả **GRN nhận hàng** và **Receive FG** đều có thể dẫn tới **inbound** (tăng tồn SKU đã định nghĩa). Khác nguồn: **mua ngoài (PO/GRN)** vs **sản xuất nội bộ (Production receipt)** — `reference_type` / chứng từ gốc phải tách để báo cáo và truy xuất đúng.

---

## Luồng tổng thể — Production như mở rộng B2B

**B2B thuần (đã có):**

`SO → DO → Invoice`

**Có sản xuất (mở rộng):**

`SO → Production Order → Consume RM → Receive FG → DO → Invoice`

- **DO** nên **dùng chung** với B2B hiện tại (cùng rule confirm/ship theo baseline Hub).
- Phần **tách** nằm **trước DO**: biến RM → FG; sau đó giao hàng như cũ.

---

## Trừ tồn / Reserved theo từng bước (chuỗi có Production)

`SO → Production Order → Consume RM → Receive FG → DO → Invoice`

| Bước | Tồn kho (tóm tắt) | Ghi chú |
| ---- | ----------------- | ------- |
| **SO** | Chưa đổi tồn (thường) | Cam kết bán. |
| **Production Order** | Chưa đổi tồn (MVP) | Lệnh SX; optional sau này có “allocate/reserve RM” nếu thiết kế thêm. |
| **Consume RM** | **Trừ tồn RM** | Xuất nguyên liệu theo lô/batch đã chọn. |
| **Receive FG** | **Cộng tồn FG** | Nhập thành phẩm + batch FG. |
| **DO confirm** | **Reserved FG** (theo baseline Sales DO) | Giữ hàng để giao. |
| **DO ship** | **Trừ tồn FG** | Xuất giao khách. |
| **Invoice** | Không đổi tồn (thường) | Tài chính theo cấu hình sale/shipment. |

---

## Luồng mua RM — dùng chung PO với B2B mua hàng

**Chuỗi tham chiếu:**

`PO → GRN / Receive RM → Vendor Invoice → Payment`

| Bước | Tồn kho |
| ---- | ------- |
| **PO** | Không tăng/giảm tồn (cam kết mua). |
| **GRN / Receive RM** | **Tăng tồn RM** (nhập kho). |
| **Vendor Invoice / Payment** | Không đổi tồn (công nợ / thanh toán). |

**Không cần tách module PO riêng cho Production** — cùng luồng mua; Production chỉ **tiêu** RM đã có trong kho khi **Consume RM**.

**Reserved:** kiểu “reserve” rõ nhất trên Hub hiện tại gắn **Sales DO confirm**; PO/GRN thường **không** reserve outbound như DO bán hàng.

---

## Liên hệ tài liệu kỹ thuật

- Baseline kho/DO: `BIOMIXING_BASELINE_PREP_2026_VI.md`, `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`.
- Playbook triển khai MVP: `BIOMIXING_PLAYBOOK_P0P1_VI.md`.
- Roadmap tổng: `BIOMIXING_DEV_PLAN.md`.
