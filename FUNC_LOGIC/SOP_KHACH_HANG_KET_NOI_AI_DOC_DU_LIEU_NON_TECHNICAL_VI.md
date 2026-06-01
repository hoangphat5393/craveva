# SOP (Non-Technical) — Thong tin khach hang can cung cap de AI ket noi va doc du lieu

**Muc dich:** Tai lieu nay dung de gui cho khach hang/business team, giup hai ben thong nhat nhanh "can gui gi" de AI co the ket noi ERP va doc du lieu dung.

**Doi tuong doc:** PM, Operations, Sales Admin, Data Admin (khong can biet code).

---

## 1) Tong quan pham vi du lieu AI can doc

De AI hoat dong dung trong giai doan dau, vui long chuan bi du lieu theo 7 nhom sau:

1. **Product** (san pham)
2. **Client** (khach hang)
3. **Inventory** (ton kho dieu chinh/lich su)
4. **Warehouse** (danh muc kho + ton theo kho/lo)
5. **Tier pricing** (bang gia theo nhom/khach)
6. **Order** (don hang va chi tiet dong hang)
7. **Order history** (lich su nghiep vu lien quan)

> Ghi chu: Danh sach chi tiet bang/field ky thuat da co san trong file `FUNC_LOGIC/API_DATA_TYPE_LIST_VI.md`.

---

## 2) Khach hang can gui cho ben Craveva nhung gi

### A. Thong tin he thong va nguoi phu trach

- Ten cong ty va domain dang su dung.
- 01 dau moi business (xac nhan quy trinh).
- 01 dau moi du lieu (xac nhan file va mapping cot).
- 01 dau moi ky thuat phia khach (de test ket noi neu can).

### B. Bo du lieu can ban (bat buoc)

- Danh sach **khach hang** (co ma khach hang duy nhat).
- Danh sach **san pham** (co SKU duy nhat).
- Danh sach **kho** (ma kho + ten kho).
- Du lieu **ton kho** theo kho (va theo lo neu co).
- Du lieu **bang gia** (gia chuan/gia theo nhom neu ap dung).

### C. Du lieu giao dich (khuyen nghi ngay tu dau)

- Don hang gan day (de doi soat ket qua AI).
- Lich su doanh so/net sales (neu can AI phan tich xu huong).

---

## 3) Quy tac chuan hoa du lieu truoc khi gui

Vui long thuc hien cac quy tac don gian sau de tranh loi khi AI doc:

1. **Ma khach hang (`client_code`) khong trung lap**.
2. **Ma san pham (`sku`) khong trung lap**.
3. **Ma kho (`warehouse_code`) on dinh** va dung cung 1 quy uoc dat ten.
4. Cung mot san pham khong dung nhieu kieu ghi ten khac nhau.
5. Ngay thang dung cung mot dinh dang trong toan bo file.
6. Khong de trong cot khoa (client_code, sku, warehouse_code).
7. Neu co lo/han dung, vui long gui day du so lo va ngay het han.

---

## 4) Checklist xac nhan truoc buoi ket noi AI

Truoc ngay go-live, khach hang va Craveva cung xac nhan:

- [ ] Da gui du bo file du lieu theo muc 2.
- [ ] Da thong nhat danh sach cot khoa (client_code, sku, warehouse_code).
- [ ] Da thong nhat nguon gia chinh thuc (pricing) dung de AI doc.
- [ ] Da thong nhat kho mac dinh neu 1 khach co nhieu kho.
- [ ] Da co bo mau 5-10 don de test doi soat.
- [ ] Da co dau moi phan hoi trong ngay (SLA test).

---

## 5) Quy trinh van hanh hang ngay (de xai thuc te)

### Buoi sang (khach -> ERP)

1. Khach export file tu he thong nguon.
2. Gui file theo dung format da thong nhat.
3. Craveva import va thong bao ket qua.
4. Hai ben doi soat nhanh cac loi neu co.

### Buoi toi (ERP -> khach)

1. ERP export file tra ve theo format da chot.
2. Khach import vao he thong nguon.
3. Khach xac nhan da dong bo thanh cong.

---

## 6) Tieu chi nghiem thu business (non-technical)

Buoc ket noi AI duoc xem la dat khi:

1. AI doc dung khach hang theo ma khach (`client_code`).
2. AI doc dung san pham theo SKU.
3. So lieu ton kho tra ve khop voi ton kho ky vong.
4. Gia ban AI su dung khop bang gia da chot.
5. Don test tao ra co the doi soat lai duoc tren ERP.

---

## 7) Ma tran trach nhiem ngan (RACI toi gian)

- **Khach hang Business:** xac nhan quy trinh, quy tac gia, nghiem thu ket qua.
- **Khach hang Data Admin:** chuan hoa file, dam bao ma khoa khong trung.
- **Craveva PM/BA:** chot pham vi, checklist, tiep nhan thay doi.
- **Craveva Tech:** cau hinh ket noi, mapping, test va xu ly loi.

---

## 8) Tai lieu tham chieu noi bo Craveva

- `FUNC_LOGIC/API_DATA_TYPE_LIST_VI.md` (danh sach data type canonical).
- `docs/AI_ORDER_INTEGRATION_REST.md` (tham chieu API don hang REST).
- `FUNC_LOGIC/MIAOLIN_SALES_ORDER_API_FIELDS.md` (required + full list).

---

## 9) Mau tin nhan gui khach hang (copy nhanh)

Xin chao anh/chi, de ben em ket noi AI va doc du lieu on dinh, nho anh/chi giup ben em 3 nhom thong tin:

1. Master data: Khach hang, San pham, Kho, Ton kho, Bang gia.
2. Giao dich mau: 5-10 don de doi soat.
3. Dau moi phoi hop: business + data + ky thuat.

Ben em da gui kem SOP non-technical de hai ben check theo checklist va chot ngay ket noi.

---

**Trang thai tai lieu:** Ready-to-send cho khach hang (ban Non-Technical SOP).
