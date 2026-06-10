# Biomixing Documentation Sync — 2026-06-10

## Scope

Rà soát tài liệu Markdown liên quan Biomixing / Production để đồng bộ với implementation hiện tại.

Nguồn đối chiếu chính:

- `FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`
- `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`
- `PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_VI.md`
- `Modules/Production/Config/config.php`

## Kết luận

Flow chuẩn hiện tại:

```text
Estimate / Quotation
  -> President + VP approval
  -> Sales Order
  -> Production Order
  -> Release + BOM snapshot + RM reserve
  -> Batch 4 bước
  -> Post RM / Post FG
  -> Sales DO / Invoice
```

## Điểm đã đồng bộ

| Chủ đề | Trạng thái đúng hiện tại |
| ------ | ------------------------ |
| Product type thành phẩm trong Production | UI: **Manufactured product**; code: `products.type = goods` |
| Legacy finished-goods enum wording | Không dùng trong code hiện tại; tài liệu đã đổi sang `goods` / Manufactured product |
| Batch checklist | 4 bước hiện tại; planned RM tự sinh từ BOM snapshot khi Release / mở batch |
| Production Release reserve | Đã triển khai trong luồng hiện tại |
| Demo / proposal Biomixing | Đã thêm note phân biệt stakeholder overlay với live operational flow |

## Audit queries đã chạy

Đã quét toàn bộ Markdown ngoài `node_modules`, `vendor`, `storage` với các pattern lệch đã biết:

- legacy finished-goods enum wording
- legacy batch-step wording
- ghi chú lỗi thời về Production Release reserve

Kết quả sau sync: không còn match trong tài liệu dự án ngoài dependency/runtime.

## Ghi chú duy trì

- Khi đổi logic Production, cập nhật `FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` trước.
- Khi đổi lifecycle / reserve / batch, cập nhật `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`.
- Proposal/demo script chỉ dùng làm storytelling; không dùng làm nguồn sự thật vận hành.
