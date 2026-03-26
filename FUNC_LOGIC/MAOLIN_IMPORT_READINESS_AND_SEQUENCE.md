# MAOLIN - Import Readiness vs B2B Guide + Recommended Sequence

Nguon doi chieu:

- `FUNC_LOGIC/B2B_ERP_PO_DO_INVOICE_GUIDE.md`
- `PROJECT MAOLIN New/` (bo CSV/XLSX hien tai)

Muc tieu:

- Kiem tra he thong da du chuc nang de import bo file Maolin chua
- De xuat thu tu import theo dung quy trinh ERP/B2B

---

## 0) Danh sach file trong `PROJECT MAOLIN New` + cong dung

## 0.1 Nhom file du lieu import truc tiep (CSV/XLSX)

| File                                         | Cong dung chinh                                          | Module map                    |
| -------------------------------------------- | -------------------------------------------------------- | ----------------------------- |
| `Craveva customer.csv`                       | Master khach hang                                        | Client                        |
| `Craveva_product__商品.csv`                  | Master san pham (SKU, ten, quy cach, don vi...)          | Product                       |
| `Craveva_product__商品價格.csv`              | Bang gia san pham theo SKU                               | Product/Pricing               |
| `Quote_unit_price_inventory__產品價格表.csv` | Bang gia theo SKU (co them employee price)               | Product/Pricing               |
| `Quote_unit_price_inventory__產品庫存表.csv` | Ton kho da kho + batch + han su dung (co warehouse code) | Inventory/Warehouse           |
| `Craveva full inventory.csv`                 | Tong hop ton theo lo-han-kho (chu yeu doi soat)          | Inventory/Warehouse           |
| `Quote_unit_price_inventory__報價單匯出.csv` | Du lieu bao gia / chung tu kinh doanh                    | Tham chieu hoac adapter rieng |
| `Last_year_net_sales__2024-01.csv`           | Lich su doanh thu rong ky 2024-01                        | Reporting/Snapshot            |
| `Last_year_net_sales__2025-03.csv`           | Lich su doanh thu rong ky 2025-03                        | Reporting/Snapshot            |
| `Last_year_net_sales__2025-04.csv`           | Lich su doanh thu rong ky 2025-04                        | Reporting/Snapshot            |

## 0.2 Nhom file workbook nhieu sheet (can luu y)

1. `Craveva product.xlsx`

- Sheet `商品 | merchandise`: Product master
- Sheet `商品價格 | commodity prices`: Pricing

2. `Quote, unit price, inventory.xlsx`

- Sheet `報價單匯出`: quotation export (khong phai import chuan PO/DO/Invoice hien tai)
- Sheet `產品價格表`: Pricing
- Sheet `產品庫存表`: Inventory (multi-warehouse)

3. `Last year net sales.xlsx`

- Du lieu theo ky/thang (thuc te da tach ra CSV theo ky: 2024-01, 2025-03, 2025-04)

## 0.3 Nhom file tai lieu nghiep vu / hop dong (khong import data)

- `[BRD] Backend Miaolin x Craveva Sales Process Integration Requirements Planning.docx`
- `Miaolin Industrial Co., Ltd. B2B AI Smart Distribution Platform Planning Document.docx`
- `[Contract] Miaolin Industrial Co., Ltd. B2B AI Smart Distribution Platform Planning Document.pdf`
- Cac ban tieng Trung trong thu muc `chinese/` (tuong ung noi dung, khong phai file import data)
- `customer do.txt` (ghi chu van hanh/yeu cau trao doi)

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

### 4.0 Luu y workbook nhieu sheet truoc khi import

- `Craveva product.xlsx` va `Quote, unit price, inventory.xlsx` la file nhieu sheet.
- Neu import qua UI hien tai, nen:
    1. Tach moi sheet can import thanh file rieng (CSV UTF-8), hoac
    2. Dam bao sheet can import la sheet duoc chon/dung trong luong import.
- Khong import nguyen workbook nhieu sheet neu module khong ho tro chon sheet ro rang.

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

---

## 7) Tinh huong hien tai: chua co file Warehouse master

Van de:

- Thu tu chuan yeu cau `Warehouse master` truoc Inventory.
- Bo file Maolin hien tai chua co file danh muc kho rieng (warehouse master list).

## 7.1 Co nen yeu cau khach bo sung khong?

**Co - nen yeu cau bo sung** de chay on dinh va giam sai map.

File de nghi khach cung cap:

- `warehouse_master.csv` (hoac xlsx)

Cot toi thieu:

- `warehouse_code` (bat buoc, unique)
- `warehouse_name` (bat buoc)
- `status` (active/inactive, tuy chon)
- `region` (tuy chon)
- `address` (tuy chon)

Rule:

- 1 code chi map 1 kho duy nhat.
- Khong dung ten kho lam khoa chinh.

## 7.2 Phuong an tam thoi neu khach chua bo sung kip

### Phuong an A (khuyen nghi cho demo): tao warehouse master tu chinh file Inventory

Nguon:

- `Quote_unit_price_inventory__產品庫存表.csv` (co `庫別` + `庫別名稱`)

Lam tam:

1. Trich distinct cap `warehouse_code + warehouse_name` tu file inventory.
2. Tao danh muc kho trong he thong tu danh sach nay.
3. Chot danh sach kho voi khach (xac nhan code/name) truoc khi import full.
4. Moi import Inventory.

Rui ro:

- Neu file inventory khong day du tat ca kho dang hoat dong, danh muc kho se thieu.
- Neu code/name sai chinh ta, se tao kho sai.

### Phuong an B (chi dung khi qua gap): map theo warehouse_name

Nguon:

- `Craveva full inventory.csv` (chi co `warehouse_name`)

Lam tam:

- Tao kho theo ten, khong co code.

Rui ro cao:

- De trung ten / lech ten / doi ten theo thoi gian.
- Sau nay bo sung code se ton cong backfill.

=> **Khong uu tien**, chi dung neu can demo gap va kho it.

## 7.3 Khuyen nghi van hanh thuc te cho dot nay

1. Van giu thu tu import:
    - Warehouse master -> Client -> Product -> Pricing -> Inventory
2. Trong dot nay, dung **Phuong an A** de tao warehouse master tam tu `產品庫存表`.
3. Song song gui yeu cau khach bo sung file warehouse chuan hoa.
4. Sau khi nhan file chuan:
    - doi chieu code/name
    - merge/chinh master
    - khoa quy tac import: uu tien `warehouse_code`, ten chi fallback

## 7.4 Mau noi dung gui khach (de copy)

> De dam bao import ton kho da kho chinh xac, vui long gui them file Warehouse Master voi toi thieu cac cot: `warehouse_code`, `warehouse_name` (bat buoc), va neu co them `status`, `region`, `address`.
> Tam thoi ben em co the khoi tao danh muc kho tu file ton kho hien tai (`庫別` + `庫別名稱`) de demo, nhung de di production can file master chuan de tranh map sai kho.

---

## 8) Cap nhat moi: da bo sung chuc nang Import Warehouse (UI giong Product/Inventory)

Trang thai: **Da bo sung luong import Warehouse** theo mau upload -> map cot -> progress -> process chunk.

File nguon tam thoi co the dung:

- `PROJECT MAOLIN New/Quote_inventory.csv`
- map cot:
    - `庫別` -> `warehouse_code`
    - `庫別名稱` -> `warehouse_name`

Rule dang ap dung:

- Upsert uu tien theo `(company_id, warehouse_code)`.
- Neu thieu `warehouse_code`, fallback theo `(company_id, warehouse_name)` va ghi warning.
- Bo qua dong rong.
- Validate `status` chi nhan `active|inactive` (neu co map cot status).
- Chan duplicate trong cung chunk import:
    - duplicate `warehouse_code`
    - hoac duplicate `warehouse_name` khi khong co code.

Luu y van hanh:

- Vi file tam thoi la inventory snapshot, danh muc kho co the chua day du 100%.
- Van nen yeu cau khach gui them `warehouse_master.csv` de chot production.
