# Product import — hiệu năng (rút gọn, 2026-05-27)

**SSOT vận hành:**

- [`IMPORT_CHUNK_AND_BULK_INSERT.md`](./IMPORT_CHUNK_AND_BULK_INSERT.md) — chunk, bulk insert, so sánh module
- [`../FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md`](../FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md) §1 — poll, `import_progress_*`, queue `ProductImport`
- [`FLOW_ADD_PRODUCT.md`](./FLOW_ADD_PRODUCT.md) — luồng map cột / nghiệp vụ
- Bug / staging: [`../FUNC_BUG/PRODUCT_IMPORT_VI.md`](../FUNC_BUG/PRODUCT_IMPORT_VI.md)

**Phân tích chi tiết (~216 dòng, 2026-04):** `git log -- FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`

---

## Tóm tắt (đã áp dụng trong code)

| Chủ đề              | Trạng thái                                                                                          |
| ------------------- | --------------------------------------------------------------------------------------------------- |
| Chunk size          | Mặc định **100** dòng/job (`ProductController`, `PurchaseProductController`); override `chunk_size` |
| SKU trùng           | Prefetch `whereIn` theo chunk — O(1) trong memory                                                   |
| Unit / category     | Cache trong job                                                                                     |
| Custom field import | Không ghi CF động khi import (giảm query)                                                           |
| Queue               | `ProductImport-chunked` — xem IMPORT_POLL_TRACKERS §1                                               |

## Backlog / vận hành

- File lớn: tăng worker nền; không rely worker trong poll trên staging (`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false`).
- Miaolin-scale: xem `MAOLIN_MASTER_GUIDE.md` + `IMPORT_SPECS_VI.md`.
