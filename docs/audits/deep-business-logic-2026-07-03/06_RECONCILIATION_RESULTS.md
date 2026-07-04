# Reconciliation Results

**Nguon:** database local hien tai, truy van chi doc ngay 2026-07-03.  
**Gioi han:** count 0 chi phan anh snapshot hien tai; khong chung minh code khong co race. Ledger co the bat dau sau du lieu legacy, nen khong suy dien opening balance tu movement neu chua co cutover marker.

| Check | Count | Ket luan |
|---|---:|---|
| Duplicate `(company, movement_type, idempotency_key)` groups | 0 | Data hien tai sach, schema van thieu unique |
| Duplicate normalized batch identity groups | 0 | Chua co anomaly hien tai |
| `warehouse_product_stock` khac tong batch | 0 | Projection khop batch tai snapshot |
| Duplicate orders per non-null Estimate | 0 | Chua co duplicate conversion hien tai |
| Duplicate company + order number groups | 7 | Can phan loai legacy/semantic truoc khi unique |
| Received GRN nhung applied=false | 0 | Khong co missing flag |
| Applied GRN khong co inbound movement | 0 | Khong co anomaly |
| Received GRN accepted qty khac inbound qty | 0 | Khong co anomaly |
| Sales DO applied khong co outbound movement | 0 | Khong co anomaly |
| Shipped/delivered Sales DO applied=false | 0 | Khong co anomaly |
| Active reservation sum khac batch reserved | 0 | Khong co anomaly |
| Posted FG output khong co FG movement | 0 | Khong co anomaly |
| Posted RM consumption khong co any `production-consume:*` movement | 8 lines | Confirmed data anomaly |
| Invoice posting khong co outbound movement | 0 | Khong co anomaly |
| Invoice posting qty khac movement qty cung key | 0 | Snapshot hien tai khop |
| Duplicate Estimate line signature groups | 1 | Candidate; co the la line hop le lap lai, khong tu dong sua |

## Production anomaly details

| Batch ID | Company ID | Posted at | Missing lines |
|---:|---:|---|---:|
| 1 | 1 | 2026-05-05 21:27:24 | 1 |
| 2 | 1 | 2026-05-05 23:00:52 | 1 |
| 6 | 1 | 2026-05-07 12:51:08 | 2 |
| 7 | 1 | 2026-05-07 12:53:13 | 2 |
| 8 | 1 | 2026-05-07 13:09:00 | 2 |

Khong backfill truc tiep. Truoc tien can xac dinh cac batch nay la pre-ledger history hay posting that bi mat movement. Dry-run backfill phai chi tao audit ledger neu physical stock da bao gom consumption; tuyet doi khong tru stock lan nua.

## Query templates

```sql
SELECT company_id, movement_type, idempotency_key, COUNT(*)
FROM stock_movements
WHERE idempotency_key IS NOT NULL AND idempotency_key <> ''
GROUP BY company_id, movement_type, idempotency_key
HAVING COUNT(*) > 1;
```

```sql
SELECT b.id, b.company_id, b.posted_consumptions_at, COUNT(*) AS missing_lines
FROM production_batch_consumptions c
JOIN production_batches b ON b.id = c.production_batch_id
WHERE b.posted_consumptions_at IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM stock_movements sm
    WHERE sm.company_id = b.company_id
      AND sm.movement_type = 'outbound'
      AND sm.idempotency_key LIKE CONCAT('production-consume:', c.id, ':%')
  )
GROUP BY b.id, b.company_id, b.posted_consumptions_at;
```

```sql
SELECT g.id
FROM grns g
JOIN (
  SELECT grn_id, SUM(CASE WHEN COALESCE(qc_status,'accepted')='accepted' THEN quantity_received ELSE 0 END) qty
  FROM grn_items GROUP BY grn_id
) i ON i.grn_id = g.id
LEFT JOIN (
  SELECT company_id, reference_id, SUM(quantity) qty
  FROM stock_movements
  WHERE movement_type='inbound' AND idempotency_key LIKE 'delivery-order-inbound:%'
  GROUP BY company_id, reference_id
) m ON m.company_id=g.company_id AND m.reference_id=g.id
WHERE g.status='received' AND g.inbound_stock_applied=1
  AND ABS(i.qty-COALESCE(m.qty,0)) > 0.0001;
```

