# UX/UI Improvement Backlog

## Muc tieu

- Day la file backlog tap trung cho cac yeu cau cai thien UX/UI.
- Tu nay, moi yeu cau UX/UI moi se duoc ghi them vao file nay.
- Giu dinh dang thong nhat de team de theo doi va uu tien.

## Cach ghi yeu cau moi

- Them 1 dong moi vao bang backlog ben duoi.
- Bat buoc co: `Ngay`, `Module`, `URL/Man hinh`, `Van de`, `De xuat`, `Uu tien`, `Trang thai`.
- Neu da fix, cap nhat `Trang thai` va bo sung `Ghi chu`.

## Backlog

| ID     | Ngay       | Module     | URL / Man hinh                                                                                            | Van de UX/UI                                                                                                          | De xuat cai thien                                                                                                                                                                 | Uu tien | Trang thai  | Ghi chu                                                                                   |
| ------ | ---------- | ---------- | --------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- | ----------- | ----------------------------------------------------------------------------------------- |
| UX-001 | 2026-05-06 | Production | `/account/production/boms/create`                                                                         | Form BOM truoc day render nhieu dong trong gay roi, va cho phep chon RM trung FG trong mot so tinh huong thao tac.    | Dung giao dien them/xoa dong theo nut Add; hard block RM = FG o UI (change + submit guard).                                                                                       | High    | In Progress | Can xac nhan lai tren staging sau deploy.                                                 |
| UX-002 | 2026-05-06 | Production | `/account/production/orders/create`, `/account/production/orders/edit`, `/account/production/orders/show` | Label kho viet tat RM/FG kho hieu voi nguoi dung moi.                                                                 | Doi label hien thi sang Raw Material / Finished Goods bang key moi, giu key cu de tuong thich.                                                                                    | Medium  | Done        | Da them key moi LanguagePack, da doi view sang key moi.                                   |
| UX-003 | 2026-05-06 | Production | `/account/production/boms`, sidebar Production                                                            | Ten "Production BOMs" chua than thien voi nguoi dung khong ky thuat.                                                  | Doi sang "Bill of Materials" bang key moi, khong xoa key cu.                                                                                                                      | Medium  | Done        | Da doi title/page/menu va key LanguagePack.                                               |
| UX-004 | 2026-05-07 | Production | Batch / Order detail (planned consumption, BOM snapshot)                                                  | Badge % chenh lech planned vs shadow cho PM demo.                                                                     | Chi lam sau khi business bat `yield_uom_shadow_enabled` va xac nhan nguong; xem `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`.                                        | Low     | Deferred    | Khong tu y trien khai neu chua co xac nhan.                                               |
| UX-005 | 2026-05-20 | Purchase   | `/account/purchase-products/create`, `/edit` — field Opening stock                                        | Popover "Ton kha dung" gay hieu nham voi ton kho warehouse; opening 100 khong hien Inventory/Production.              | P0: doi popover/help (key moi); checklist onboarding; **khong** them select kho tren form SP. P1: sync opening -> kho mac dinh — xem `13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`. | High    | Done        | Key openingStock\* trong LanguagePack en/vi; product-form-fields popover/fieldHelp/alert. |
| UX-006 | 2026-05-23 | Clients    | `/account/clients` — Client listing DataTable                                                             | PM: bang tom tat (tier, contract badge, outstanding, NVKD); an email/mobile/category/created; optional qua Columns.   | Phase 0–7: `FUNC_IMPROVE/14_CLIENT_LISTING_TABLE_UX_PLAN_VI.md` — Phase 1 chi visibility, DB/consolidation cuoi.                                                                  | High    | Open        | Tham chieu PM tables SHOW BY DEFAULT / OPTIONAL.                                          |
| UX-007 | 2026-05-23 | Settings   | `/account/settings`, `/account/sales-order-settings`, Purchase/DO settings sidebar                        | Menu Settings qua dai, phang; SO/PO/DO settings xa nhau; Finance Settings ten gay nham.                               | Phuong an A: nhom + sap xep lai — `FUNC_IMPROVE/17_SETTINGS_MENU_REORGANIZATION_VI.md`; PM sign-off truoc khi code.                                                               | High    | Open        | Doc only 2026-05-23; lien quan `05_SO_DO_PO_GRN_REFACTOR_VI.md`.                          |
| UX-008 | 2026-05-24 | Production | Batch detail — cot **Variance approval** (output FG)                                                      | Chi hien **Pending approval** / **Approved**; sau post van Pending neu chua bam Approve du khi policy khong bat buoc. | Trang thai **Khong yeu cau** / Cho / Da phe duyet; nut Approve chi khi `pending`. Ref `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3.2.                 | Medium  | Done        | `outputVarianceApprovalUiState` + tests 2026-05-24. |

## Quy uoc trang thai

- `Open`: moi ghi nhan, chua bat dau.
- `In Progress`: dang xu ly.
- `Done`: da xong va da verify.
- `Blocked`: bi chan, can them thong tin/phan quyen/deploy.
