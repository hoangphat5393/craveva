# PM Demo 3PM - SO -> DO -> Invoice & PO -> GRN

Muc tieu: demo full flow theo nghiep vu hien tai, du lieu ton kho khop voi ship/inbound va co bang chung.

## 0) Chuan bi nhanh (5 phut)

- Xac nhan env:
  - `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`
  - `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`
  - Chi bat 1 inbound canonical: `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` hoac `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true`
- Chay migration moi:
  - `php artisan migrate`
- Du lieu demo:
  - 1 kho active
  - 1 san pham goods co batch + expiry va ton > 0
  - 1 client, 1 vendor

## 1) Demo luong ban (SO -> DO -> ship -> Invoice)

1. Tao SO (1 dong hang so luong 3).
2. Tao Sales DO tu SO:
   - Chon kho.
   - Chon batch theo dropdown (batch + expiry + available).
   - Nhap ship qty hop le.
3. Confirm DO (giu cho).
4. Ship DO:
   - Ky vong: trang thai `shipped`, movement outbound duoc tao.
5. Tao Invoice tu SO:
   - Ky vong: trong mode `shipment`, invoice khong tru ton them (khong double deduction).

### Bang chung can show cho PM

- Muc Stock Movements co dong outbound reference `SalesDo`.
- Muc Inventory hien quantity da giam ngay sau ship.

## 2) Demo luong mua (PO -> inbound -> Bill)

1. Tao PO co `warehouse_id`.
2. Nhan hang theo cau hinh canonical:
   - Neu theo PO: doi `delivery_status=delivered`.
   - Neu theo GRN: tao GRN/DO inbound va `received`.
3. Tao Purchase Bill:
   - Ky vong: AP update, khong tao movement kho moi.

### Bang chung can show cho PM

- Stock Movements co dong inbound tu chung tu mua.
- Inventory quantity tang dung theo nhan hang.

## 3) Checklist pass/fail trong buoi demo

- [ ] Ship DO tao outbound 1 lan, khong trung.
- [ ] Inventory quantity thay doi khop ship (giam).
- [ ] Tao Invoice sau ship khong tru ton lan 2 (mode shipment).
- [ ] Inbound mua tang ton dung 1 lan theo canonical event.
- [ ] Bill khong tu y ghi movement kho.

## 4) Neu gap loi tai demo

- `Ship quantity cannot exceed remaining quantity`: qty ship > remaining, giam qty hoac tao them DO.
- `Please select a valid batch`: chua chon batch identity day du.
- Ton tren man hinh chua doi: refresh table va doi chieu voi Stock Movements.
