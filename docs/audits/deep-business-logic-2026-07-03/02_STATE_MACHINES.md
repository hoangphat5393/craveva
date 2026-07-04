# State Machines

## Estimate

| From | Action | To | Guard / side effect |
|---|---|---|---|
| Draft/waiting | Submit review | review state | Approval event/timeline |
| Review | VP/President decision | approved/revision/rejected | Phase-1 review gate |
| Approved/ready | Convert to SO | Estimate giu nguyen; Order pending | Copy items/totals; hien chi app-level duplicate check |
| Any editable | Import row | waiting/new or append existing | Append item va cong total |

## Sales DO

| From | Action | To | Stock effect |
|---|---|---|---|
| Draft | Confirm | Confirmed | Reserve stock |
| Draft/Confirmed | Ship | Shipped | Ensure reserve, outbound, consume reserve |
| Shipped | Deliver | Delivered | Khong doi stock |
| Shipped/Delivered | Reverse | Confirmed | Inbound reversal, reserve lai |
| Non-cancelled | Cancel | Cancelled | Reverse outbound neu co, release reservation |

Header lock trong `SalesShipmentStockService` bao ve post/reverse cua cung Sales DO. Entry service van can giu state transition trong transaction va test concurrent.

## GRN

| From | Action | To | Stock effect hien tai |
|---|---|---|---|
| Draft | Mark inbound | Inbound | Khong post |
| Draft/Inbound | Mark received | Received | Observer inbound accepted lines, set applied flag |
| Received | Edit lines | Received/khac | **Khong correction/repost** vi applied flag da true |
| Received | Downgrade status | Draft/Inbound | **Khong reversal** |
| Received | Delete | Deleted | **Khong reversal** |

State machine GRN hien tai vi pham conservation sau khi Received.

## Production Order

| From | Action | To | Side effect |
|---|---|---|---|
| Draft | Release | Released | Freeze BOM snapshot, reserve RM |
| Released | Post RM | In progress | RM outbound; consume reservation khi khong con batch pending |
| In progress | Post FG output(s) | In progress/Completed | FG inbound; complete khi moi batch/output da post |
| Draft | Cancel | Cancelled | Khong stock effect |
| Released, chua post | Cancel | Cancelled | Release reservation |
| In progress/Completed | Cancel | Blocked | Khong co generic reverse Production posting |

## Invoice stock

| Mode | Event | Effect |
|---|---|---|
| Shipment | Sales DO ship | Outbound; invoice stock service no-op |
| Invoice | Invoice non-draft create/update | Reverse posting cu, post current lines |
| Invoice | Draft/delete | Reverse current postings |

## Returns

| Document | Create line | Delete/change |
|---|---|---|
| Credit Note | Inbound sales return neu gate cho phep | Delete header outbound reversal |
| Vendor Credit | Outbound purchase return | Change/delete inbound reversal, co versioned keys |

## Purchase Order

PO status co `not_started`, `in_transaction`, `delivery_failed`, `delivered`. Khi `delivered`, observer van tang legacy purchase stock adjustment; Warehouse inbound chi chay neu config PO canonical duoc bat. Khong thay transition reversal khi roi trang thai delivered.

