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
- De chay on dinh theo nghiep vu B2B trong guide, nen import theo trinh tu:
    1. Warehouse master
    2. Client
    3. Product master
    4. Pricing
    5. Inventory (uu tien file co `warehouse_code`)

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

=> Ket luan tong:

- **Du cho giai doan import master + ton kho da kho**
- **Chua du cho import chung tu ban hang (quotation/order/do/invoice) tu file quotation dang co**

---

## 4) Thu tu import de dung quy trinh ERP/B2B

Thu tu de nghi:

1. **Warehouse master** (bat buoc truoc Inventory)
    - Dam bao kho da co `warehouse_code` + `warehouse_name`.

2. **Client**
    - Tao khach hang + map kho mac dinh (neu co designated warehouse).

3. **Product master**
    - Tao SKU truoc de pricing/inventory co khoa doi chieu.

4. **Pricing**
    - Cap nhat gia sau khi Product da ton tai.

5. **Inventory**
    - Uu tien `Quote_unit_price_inventory__產品庫存表.csv`.
    - Sau do neu can, import/doi soat them tu `Craveva full inventory.csv`.

6. **Quotation/Order docs** (neu can)
    - Chua import truc tiep vao PO/DO/Invoice cho den khi co adapter rieng.

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

---

## 6) Recommendation de giam loi demo

- Inventory import lan dau: chi dung file co `warehouse_code`.
- `Craveva full inventory.csv` dung de doi soat batch/expiry/so luong, khong dung lam nguon duy nhat.
- File `報價單匯出.csv`: tam thoi xem la tai lieu tham chieu, chua coi la file import chung tu chuan.
