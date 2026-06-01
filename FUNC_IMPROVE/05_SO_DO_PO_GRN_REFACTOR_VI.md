# Refactor SO → DO → Invoice & PO → GRN → Bill — tracker (rút gọn pass 8)

**Cập nhật:** 2026-05-27 (pass 8 — giữ vận hành + Phase 4–5; lịch sử đầy đủ: `git log -- FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md`)

**Schema / DROP legacy / ma trận bảng:** [`../FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`](../FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md)  
**QA vận hành SO/PO/DO/GRN:** [`../FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`](../FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md)  
**Staging ops (migrate/rehearsal):** [`../docs/STAGING_OPERATIONS.md`](../docs/STAGING_OPERATIONS.md) §5  
**UAT E2E mua-bán-kho:** [`../FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](../FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md)

---

## 1. Quyết định kiến trúc (tóm tắt)

| Cũ (tên gây nhầm)              | Mới (ERP)    | Ghi chú vận hành                                      |
| ------------------------------ | ------------ | ----------------------------------------------------- |
| Sales Shipment                 | **Sales DO** | Xuất kho bán — bảng đích `sales_dos*` khi cutover bật |
| Delivery Order (Purchase menu) | **GRN**      | Nhập kho mua — bảng đích `grns*` khi cutover bật      |
| —                              | SO → Invoice | Kế toán; trigger kho qua DO, không qua Invoice        |
| —                              | PO → Bill    | Kế toán; trigger kho qua GRN                          |

**Chiến lược:** compat song song — **không** big-bang xóa `sales_shipments` cho đến Phase 5 pass UAT.

---

## 2. Trạng thái phase

| Phase | Mô tả ngắn                         | Trạng thái      |
| ----- | ---------------------------------- | --------------- |
| **1** | Naming UI, permission/route compat | **Done**        |
| **2** | Flow E2E + service alias + tests   | **In Progress** |
| **3** | Migrate data rehearsal + rollback  | **Done**        |
| **4** | Staging cutover + runtime resolver | **In Progress** |
| **5** | Drop legacy tables & dead code     | **Not Started** |

---

## 3. Cutover & config (vận hành)

| Key / flag                        | Giá trị khi cutover active (staging đã chạy 2026-03) |
| --------------------------------- | ---------------------------------------------------- |
| `purchase.do_grn_cutover_enabled` | `true`                                               |
| `purchase.flow_naming_mode`       | `compat_v2`                                          |

**Route naming:** UI/redirect dần sang `sales-do.*`, `grn.*`; backend vẫn bridge từ `sales-shipments.*`, `delivery-orders.*` khi cần.

**Runtime:** `SalesDoRuntime` (`sales_shipments*` ↔ `sales_dos*`), `GrnRuntime` (`delivery_orders*` ↔ `grns*`) — chọn bảng theo flag cutover.

---

## 4. Lệnh Artisan (migrate / rehearsal)

Luôn **`--dry-run` trước**, backup DB trước `--execute`.

| Lệnh                                                   | Mục đích                                                 |
| ------------------------------------------------------ | -------------------------------------------------------- |
| `purchase:sales-do-migration-rehearsal`                | Rehearsal Sales DO                                       |
| `purchase:sales-do-reconcile-report --baseline=<json>` | Đối soát baseline vs hiện tại                            |
| `purchase:sales-do-migrate-data`                       | Migrate header/lines → `sales_dos` (`--execute --force`) |
| `purchase:sales-do-migrate-rollback --manifest=...`    | Rollback theo manifest                                   |
| `purchase:grn-migrate-data`                            | Migrate PO receiving → `grns`                            |
| `purchase:grn-migrate-rollback --manifest=...`         | Rollback GRN                                             |

Chi tiết thứ tự staging: `docs/STAGING_OPERATIONS.md` §5.1–5.9.

---

## 5. Phase 4 — còn lại (In Progress)

### Checklist

- [x] Backup DB staging, deploy code, migrate schema + data, bật cutover flag
- [x] Runtime Sales DO + GRN trên staging; smoke route/syntax pass
- [ ] **Smoke test + UAT nghiệp vụ người dùng cuối** (SO flow + PO/GRN flow)

### Acceptance (chưa đạt hết)

- [ ] SO flow ổn định trên staging
- [ ] PO/GRN flow ổn định
- [ ] Log không critical mới sau UAT

**Tham chiếu test:** `SalesDoServiceLifecycleTest`, `GrnService` lifecycle tests, `UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`.

---

## 6. Phase 5 — retirement (Not Started)

### Điều kiện mở phase

- [ ] Phase 1–4 đều **Done**
- [ ] UAT sign-off + reconciliation staging pass
- [ ] Không bug blocker mở

### Checklist công việc

- [ ] Gỡ route/controller/view/permission `sales-shipments.*` cũ
- [ ] Migration drop an toàn `sales_shipments` (+ artifact legacy liên quan) sau grace period
- [ ] Remove compat/dead code
- [ ] Cập nhật tài liệu + `ERP_SO_PO_DO_GRN_SCHEMA_MATRIX` (DROP legacy)

### Thứ tự cutover an toàn (nhắc)

1. Backup → deploy compat code → migrate schema
2. `migrate-data --dry-run` → report → execute
3. Reconcile → bật flag → UAT
4. Grace period giữ bảng cũ → **chỉ drop khi sign-off**

---

## 7. Definition of Done (khi đóng Phase 5)

- Unit: idempotent post outbound/inbound, reverse, anti-double-post
- Feature: lifecycle Sales DO + GRN; invoice/bill theo policy
- UAT: 2 happy path + partial + rollback scenario

---

## 8. Rủi ro trọng yếu

| Rủi ro               | Giảm thiểu                               |
| -------------------- | ---------------------------------------- |
| Double stock posting | Trigger canonical + test idempotent      |
| Mất mapping migrate  | dry-run + reconcile + backup             |
| Permission/UI miss   | Matrix Phase 1 + smoke theo role         |
| Rollback khó         | Rollback manifest bắt buộc trước cutover |

---

_Lịch sử tracker chi tiết (2026-03-30), permission matrix đầy đủ, issue log append-only: `git show` bản file trước pass 8._
