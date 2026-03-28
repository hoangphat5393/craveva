# Cursor / Agent prompt — Hoàn thiện Warehouse theo UAT (bỏ qua DigiWin)

**Mục đích:** Dán prompt dưới mục “Prompt để dán” vào Cursor (Agent) để triển khai hoặc rà soát code cho **đủ checklist UAT nội bộ Craveva**. **Không** làm tích hợp file/API DigiWin, không làm sync hai chiều với ERP ngoài.

**Trước khi chạy prompt / bật rộng kho:** làm audit baseline theo **[`WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md`](WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md)** — **ưu tiên local** (migrate + test + smoke) chạy ổn **trước push**; staging chỉ cần pull về và smoke bổ sung nếu cần.

**Nguồn bắt buộc đọc trước khi sửa code:**

- `FUNC_LOGIC/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` — toàn bộ mục A–I + tiêu chí sign-off §3
- `FUNC_LOGIC/WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md` — luồng nghiệp vụ
- `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md` — URL, permission, DB
- `FUNC_LOGIC/WAREHOUSE_TOM_TAT_NOI_BO.md` — trạng thái Scope B, đường dẫn file code

---

## Prompt để dán (copy từ đây)

```
You are working in the Craveva Laravel repo (Modules/Warehouse + Purchase + Invoice observers).

GOAL
Close gaps so the Warehouse module passes internal UAT described in FUNC_LOGIC/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md sections A–I, WITHOUT any DigiWin/external ERP integration (no new import/export jobs for Miaolin/DigiWin).

SCOPE
- In scope: warehouse master CRUD, bulk actions, Excel import, stock adjustment, transfer, movements ledger filters/search, PO-delivered OR DO-received inbound (single canonical), Purchase Inventory absolute sync, permissions/company scoping, validation hardening, UX smoke fixes for warehouse screens, Scope B sales outbound already in repo (verify + fix bugs only).
- Out of scope: DigiWin file formats, morning batch import from external ERP, reserve-vs-confirm two-step Dingxin parity, new public warehouse APIs unless checklist explicitly requires.

PROCESS
1) Read WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md end-to-end. For each subsection A–I, mark: PASS (verified in code), GAP (missing or broken), CONFIG-ONLY (needs .env/docs, no code).
2) For each GAP, implement the smallest change that satisfies the expected behavior. Use existing patterns: StockMovementService for all stock changes; DB::transaction for atomic operations; WarehouseBusinessException + existing lang keys; permission middleware/gates already used by the module.
3) Gap report in WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md §2:
   - Critical: sales outbound v1 — if code exists (InvoiceWarehouseStockService, InvoiceObserver, flag WAREHOUSE_SALES_OUTBOUND_ENABLED), verify idempotency, reversal on delete/update, validation insufficient stock; add/fix tests in tests/Unit/InvoiceWarehouseStockScopeBTest.php if gaps found. Do NOT change trigger to DigiWin import unless PM spec exists in-repo.
   - High: ensure only one inbound path active — document in .env.example; optional runtime warning already in provider — verify.
   - Medium: if stock adjustment/transfer UI lacks batch/expiry fields but service supports FEFO, add optional batch/expiry inputs on those modals/controllers, validated and passed into StockMovementService payloads. If too large, document exact UI gap in a short comment in checklist file and implement minimal path (e.g. optional batch on manual outbound).
   - Low: movements ledger deep links to PO/DO/invoice — add only if quick using existing routes; otherwise leave as backlog with a one-line note.
4) Permissions: verify keys warehouse_view, warehouse_add, warehouse_edit, warehouse_delete, warehouse_stock_view, warehouse_stock_add, warehouse_transfer_add, warehouse_movement_view match routes/controllers; fix any missing checks.
5) After changes: run relevant PHPUnit tests (at least tests/Unit/InvoiceWarehouseStockScopeBTest.php and any warehouse module tests); fix failures.

DELIVERABLES
- Code fixes + any new tests.
- Short summary table in a reply: checklist section → PASS/GAP/CONFIG and what you changed.
- Do not add unrelated refactors or new markdown files beyond updating checklist checkboxes or gap notes if the user wants that.
```

---

## Hệ thống kho **còn thiếu gì** so với UAT (bỏ qua DigiWin)

Tóm tắt từ `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` §2 + §3 — **chỉ phía Craveva:**

| Mức                                            | Còn thiếu / cần làm                                                                                                                                                                                               |
| ---------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Chưa phải “thiếu code” mà thiếu bằng chứng** | **Verify** đủ mục A–I (migration, quyền, một cờ inbound, ledger, PO/DO/Purchase Inventory): **local trước push**; staging sau pull nếu cần bằng chứng PM. **Sales outbound** v1 — kiểm movement + tồn + reversal. |
| **Có thể thiếu / yếu (Medium)**                | **UI nhập lô/HSD** trên điều chỉnh tồn / chuyển kho: service hỗ trợ FEFO nhưng form có thể chưa truyền batch/expiry → UAT mục C “batch may be null if UI doesn’t capture”.                                        |
| **Low**                                        | Deep link từ movement sang chứng từ nguồn; stub API — không chặn UAT lõi. **Không** làm `sort_order` / kéo thả thứ tự kho trong phase này.                                                                        |
| **Cấu hình / vận hành**                        | Chỉ bật **một** trong hai: PO delivered **hoặc** DO received. `WAREHOUSE_ALLOW_NEGATIVE_STOCK` theo policy. Bật `WAREHOUSE_SALES_OUTBOUND_ENABLED` khi muốn test nhánh bán hàng trong UAT.                        |
| **Ngoài UAT checklist**                        | PM vẫn có thể yêu cầu trigger khác (paid/confirm) hoặc kho từng dòng — đó là **mở rộng** sau khi UAT hiện tại pass, không ghi trong prompt này nếu đã bỏ DigiWin.                                                 |

**Kết luận một dòng:** Về **chức năng trong checklist**, phần lớn đã có trong code; **thiếu chủ yếu** là (1) **chạy UAT + evidence**, (2) **bổ sung UI lô/HSD** nếu muốn đúng kỳ vọng FEFO trong checklist, (3) **rà soát/fix nhỏ** permission, validation, ledger filter/search nếu QA phát hiện lệch.

---

## PM nói multi warehouse “cơ bản” — có đúng không? UAT bổ sung gì?

**Đúng một phần, tùy chuẩn so sánh.**

- So với **WMS đầy đủ** (mobile picking, wave, ASN, automation…) thì Craveva **không** ở tầm đó — có thể gọi là **multi-warehouse kiểu ERP SMB**.
- So với **Dingxin / ERP lớn toàn phân hệ**, “cơ bản” cũng **có thể chấp nhận** nếu ý PM là **không clone** mọi trạng thái nghiệp vụ bên ngoài.
- **Nhưng** gọi là **“chỉ multi warehouse đơn giản”** thì **chưa khớp** với UAT: checklist đã yêu cầu **hơn** việc chỉ có nhiều kho + tồn theo kho.

| Nhóm UAT         | UAT **bổ sung** so với ý “chỉ đa kho + tồn”                               |
| ---------------- | ------------------------------------------------------------------------- |
| **B**            | CRUD kho + **import Excel** + **bulk** + guard xóa.                       |
| **C**            | **Điều chỉnh tồn tay**, chính sách **âm tồn**, kỳ vọng **FEFO** (lô/HSD). |
| **D**            | **Chuyển kho** atomic, validation nguồn≠đích.                             |
| **E**            | **Ledger movement**: filter, search, reference type/id.                   |
| **F**            | **Mua → nhập kho** (PO **hoặc** DO), **một nguồn** inbound.               |
| **G**            | **Sync tồn tuyệt đối** (Purchase Inventory).                              |
| **H**            | **Permission**, multi-company, validation server-side.                    |
| **I**            | Smoke UX / performance list.                                              |
| **Gap Critical** | **Bán trừ tồn** (Scope B, invoice) — **vượt** “kho vận hành thuần”.       |

**Một dòng đối thoại PM:** _“Đa kho trong UAT không dừng ở danh mục kho; đã gồm điều chỉnh, chuyển, sổ cái, mua nhập, sync tồn, phân quyền và nhánh bán trừ tồn nếu bật flag.”_

---

_Liên kết: [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) · [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md)_
