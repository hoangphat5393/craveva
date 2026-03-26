# MAOLIN - Import Readiness vs B2B Guide + Recommended Sequence

Nguon doi chieu:

- `FUNC_LOGIC/B2B_ERP_PO_DO_INVOICE_GUIDE.md`
- `PROJECT MAOLIN New/` (bo CSV/XLSX hien tai)

Muc tieu:

- Kiem tra he thong da du chuc nang de import bo file Maolin chua
- De xuat thu tu import theo dung quy trinh ERP/B2B

---

## 1) Ket luan nhanh

- He thong **da co the import duoc** nhom du lieu cot loi: `Client`, `Product`, `Pricing`, `Inventory`.
- He thong **chua du full** cho nhom file bao gia/quotation (`報價單匯出`) de di truc tiep vao luong `PO/DO/Invoice`.
- De chay on dinh theo nghiep vu B2B trong guide, nen import theo trinh tu (chi tiet muc 4):
    1. Warehouse master
    2. Client
    3. Product master
    4. Pricing
    5. Inventory (uu tien file co `warehouse_code`)
    6. Lich su giao dich ban hang / **Last year net sales** (sau khi da co Client + Product de match `client_code` + `sku`)
    7. Tai lieu **bao gia** (`報價單匯出`) — chi tham chieu hoac qua adapter rieng; khong xep vao chuoi import chuan

---

## 2) Doi chieu theo module va file thuc te

## 2.1 Client module

File lien quan:

- `Craveva customer.csv`

Thuc te cot:

- Co `客戶代號`, `客戶簡稱`, `統一編號`, `送貨地址`, `TEL_NO(一)`, `TEL_NO(二)`.
- Co `指定庫別名稱` (designated warehouse name).

Danh gia:

- **Dat cho import Client core** (code khach hang, ten, lien he).
- Phan kho client default co the map bang `designated_warehouse_name` (fallback theo ten kho).
- Neu muon khoi tao chuan hon, nen co them `designated_warehouse_code` (khong bat buoc cho lan dau).

Trang thai: **DU de import (co dieu kien)**.

## 2.2 Product module

File lien quan:

- `Craveva_product__商品.csv`

Thuc te cot:

- Co `SKU`, `Product Name`, `Specification`, `Inventory Units`, `Product Grade`, `Brand Category`, `Storage Days`, `Inventory type`.

Danh gia:

- Du cho khoi tao master SKU + thong tin san pham.
- Khong can doi `PO/DO` moi import duoc.

Trang thai: **DU de import**.

## 2.3 Pricing module

File lien quan:

- `Quote_unit_price_inventory__產品價格表.csv`
- `Craveva_product__商品價格.csv`

Thuc te cot:

- Co `品號/SKU`, `標準售價`, `中盤價`, `成箱價`, (mot file co them `員工價`).

Danh gia:

- Du cap nhat gia theo SKU sau khi da co Product.
- Nen chon 1 nguon chinh de tranh ghi de gia 2 lan.

Trang thai: **DU de import/update**.

## 2.4 Inventory module (multi-warehouse)

File lien quan:

- `Quote_unit_price_inventory__產品庫存表.csv`
- `Craveva full inventory.csv`

Thuc te cot:

- `產品庫存表` co day du: `庫別` (warehouse_code), `庫別名稱`, `批號`, `有效日期`, `製造日期`, `期末庫存`...
- `Craveva full inventory` co `Warehouse Name`, `Lot Number`, `Expiration Date`, `Inventory` (nhung **khong co warehouse_code**).

Danh gia:

- Import multi-warehouse chuan nen uu tien `產品庫存表` vi co `warehouse_code`.
- `Craveva full inventory` dung tot cho doi soat/bo sung, nhung vi thieu code nen phu thuoc map theo ten kho.

Trang thai: **DU de import, nhung uu tien file co warehouse_code**.

## 2.5 PO / DO / Invoice theo guide

File lien quan:

- `Quote_unit_price_inventory__報價單匯出.csv`

Thuc te cot:

- File mang tinh quotation export, nhieu cot chung tu kinh doanh.

Danh gia theo guide:

- Guide yeu cau tach ro:
    - luong vat ly kho (inbound/outbound/transfer)
    - luong tai chinh (invoice)
- File quotation nay **khong phai** format import chuan PO/DO/Invoice hien tai.

Trang thai: **CHUA du de import truc tiep vao PO/DO/Invoice** (can adapter mapping rieng neu bat buoc).

## 2.6 Lich su giao dich ban hang (Last year net sales)

File lien quan:

- `Last year net sales.xlsx` (nhieu sheet theo thang / ky)

Ban chat theo PN / `MAOLIN_MASTER_GUIDE.md`:

- **Khong phai ton kho**; la **lich su giao dich (sales transactions)** Miaolin/DigiWin xuat ra: ngay, ma khach, SKU, so luong, tien net, v.v.
- Muc dich: doi soat voi ERP goc, bao cao, goi y mua lai — thuong import vao **lop reporting / bang snapshot** (khong bat buoc trung voi bang `orders` cua Craveva neu chua map 1-1).

Dieu kien thu tu:

- **Sau buoc Client + Product master** (de resolve `client_code` → khach, `sku` → san pham khi he thong ho tro).
- **Doc lap** voi buoc Inventory: co the import song song hoac sau Inventory, nhung **khong thay the** file ton kho.

Trang thai he thong (cap nhat theo code):

- **Importer chuyen dung** co the chua co; khi co, van giu thu tu: sau Client + Product (xem muc 4 buoc 6).

---

## 3) He thong da du chuc nang chua?

Theo bo file Maolin hien tai:

1. Import duoc:

- Client
- Product
- Pricing
- Inventory

2. Chua du full:

- Quotation -> PO/DO/Invoice auto flow tu file `報價單匯出.csv`.
- (Tuy chon) Import **Last year net sales** vao bang lich su giao dich / reporting — xem `MAOLIN_MASTER_GUIDE.md`.

=> Ket luan tong:

- **Du cho giai doan import master + ton kho da kho**
- **Chua du cho import chung tu ban hang (quotation/order/do/invoice) tu file quotation dang co**

---

## 4) Thu tu import de dung quy trinh ERP/B2B

Nguyen tac: **master & khoa ngoai truoc, snapshot sau**; **vat ly kho (ton/movement)** tach khoi **lich su ban (net sales)**.

### 4.1 Chuoi bat buoc (master + ton)

| Buoc  | Noi dung             | Phu thuoc            | Ghi chu ngan                                                                                        |
| ----- | -------------------- | -------------------- | --------------------------------------------------------------------------------------------------- |
| **1** | **Warehouse master** | —                    | Bat buoc truoc Inventory va truoc Client neu map `designated_warehouse_*` → `default_warehouse_id`. |
| **2** | **Client**           | Buoc 1 (khuyen nghi) | Import `Craveva customer`; map ma/ten kho chi dinh.                                                 |
| **3** | **Product master**   | —                    | Import SKU + thuoc tinh; bat buoc truoc Pricing & Inventory.                                        |
| **4** | **Pricing**          | Buoc 3               | Mot nguon gia chinh (`產品價格表` hoac `商品價格`) de tranh ghi de.                                 |
| **5** | **Inventory**        | Buoc 1 + 3           | Uu tien file co `warehouse_code`; full inventory chi bo sung / doi soat.                            |

### 4.2 Chuoi khuyen nghi sau master (lich su & van hanh)

6. **Lich su giao dich ban hang — Last year net sales**
    - **Sau buoc 2 va 3** (Client + Product) de match `client_code`, `sku` khi importer ho tro.
    - **Khong** xen giua Pricing va Inventory; co the lam **sau buoc 5** neu muon on dinh ton kho truoc khi nap lich su.
    - Ban chat: transaction history Miaolin/DigiWin, khong thay the file ton (`MAOLIN_MASTER_GUIDE.md`).

7. **PO / DO / Invoice (luong B2B trong app)**
    - Tao tren UI hoac luong mua hang chuan (`B2B_ERP_PO_DO_INVOICE_GUIDE.md`) **sau** khi master da day du.
    - **Khong** co buoc import file quotation (`報價單匯出`) vao PO/DO/Invoice cho den khi co adapter.

8. **Bao gia / quotation (file `報價單匯出`)**
    - **Ngoai chuoi import chuan** — chi tham chieu, hoac xu ly rieng neu PM chot adapter.

### 4.3 Thu tu tom tat (mot dong)

`Warehouse → Client → Product → Pricing → Inventory → [Last year net sales] → [PO/DO/Invoice thu cong / luong app]`

- Dau `[]`: buoc nghiep vu hoac tinh nang co the lam sau, khong chan buoc 1–5.

### 4.4 (Tuy chon) Truoc dot import lon

- Chuan bi **custom field** (vi du `region` / cac CF Client) **truoc** buoc 2 neu PM yeu cau day du cot file customer.
- **Trial** 20–50 dong moi loai file truoc khi chay full (xem muc 5).

---

## 5) Checklist truoc khi import that

- [ ] Da co warehouse master day du code + ten kho
- [ ] Da chot file pricing nguon chinh (tranh ghi de)
- [ ] Da map khoa chinh:
    - client: `client_code`
    - product: `sku`
    - warehouse: `warehouse_code`
- [ ] Da test trial 20-50 dong cho moi module
- [ ] Da chot quy tac khi khong map duoc kho (skip + log)
- [ ] (Neu nap Last year net sales) Da xong buoc Client + Product; da chot key doi soat (`ngay + client_code + sku` hoac theo spec DigiWin)
- [ ] (Neu can cot customer them) Da tao/map custom field (vi du `region`) truoc import Client

---

## 6) Recommendation de giam loi demo

- Inventory import lan dau: chi dung file co `warehouse_code`.
- `Craveva full inventory.csv` dung de doi soat batch/expiry/so luong, khong dung lam nguon duy nhat.
- File `報價單匯出.csv`: tam thoi xem la tai lieu tham chieu, chua coi la file import chung tu chuan.
