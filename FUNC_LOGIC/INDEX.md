# FUNC_LOGIC Index

Navigation index for business logic, master guides, flow references, and audit reports.

## Primary Entry Points

- **End-user help (closed English corpus for agents/RAG):** `docs/platform-help/README.md` — self-contained; do not link out. Index [00-URL-INDEX.md](../docs/platform-help/00-URL-INDEX.md), `REFERENCE/`, `pages/`, `flows/`. Regenerate: `php docs/platform-help/scripts/convert-to-english.php --regenerate`
- Group overview: `FUNC_LOGIC/README.md`
- Warehouse hub: `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`
- Maolin hub: `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`
- Sales fulfillment hub: `FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`
- **Production vận hành live:** `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` (§7 batch, §8 audit dev)
- **Loại sản phẩm / BOM:** `FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_VI.md`, `PRODUCTION_TERMINOLOGY_CODE_VS_UI_VI.md`
- **Biomixing pilot — test & UAT (một cửa, tiếng Việt):** `FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE_VI.md` — chỉ mục đầy đủ runbook + `php artisan test`; **hàng đợi bước tiếp theo P0:** `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md`

## Domain Index Files

- `FUNC_LOGIC/WAREHOUSE_INDEX.md`
- `FUNC_LOGIC/MAOLIN_INDEX.md`

## Core Business Flows

- `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`
- `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
- `FUNC_LOGIC/WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`
- `FUNC_LOGIC/FLOW_ADD_PRODUCT.md`
- `FUNC_LOGIC/FLOW_ADD_INVENTORY.md`
- `FUNC_LOGIC/FLOW_ADD_CLIENT.md`
- `FUNC_LOGIC/FLOW_USERS_CLIENT.md`
- `FUNC_LOGIC/FLOW_Pricing_Module_VI.md`

## Technical and Data References

- `FUNC_LOGIC/SYSTEM_DATABASE_OVERVIEW_REPORT_VI.md`
- `docs/STAGING_CLOUD_SQL_BACKUP_POLICY_VI.md` — staging DB trên GCP: automated backup (hằng ngày), giữ 7 bản, xoay vòng bản cũ, binary log / PITR 7 ngày
- `FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`
- `FUNC_LOGIC/API_DATA_TYPE_LIST_VI.md` — canonical (đã gộp nội dung trùng từ bản EN 2026-05)
- `FUNC_LOGIC/Libraries_And_Module_Names.md`
- `docs/AI_ORDER_INTEGRATION_REST.md` — REST AI order (`/api/integrations/orders`), auth, method toggles, troubleshooting 404
- `docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md` — hướng dẫn Postman / probe / CSRF
- `FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md` — đã gỡ `POST /ai-order-webhook/{hash}`; chỉ REST `/api/integrations/orders`

## Audit and Review Documents

**Snapshot audits đã xóa (pass 6):** xem [`LEGACY_ARCHIVE.md`](LEGACY_ARCHIVE.md).

- `FUNC_LOGIC/SURVEY_SYSTEM_WIDE_API_AND_REST_VI.md` — khảo sát route `/api` toàn repo

## Integration and Implementation Notes

- `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`
- `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`
- `FUNC_LOGIC/CLIENT_INLINE_VALIDATION_ROLLOUT.md`
- `FUNC_LOGIC/AI_LINE_TO_ORDER_ANALYSIS_VI.md`
- `FUNC_LOGIC/DEVELOPER_TOOLS_EXT_PLAN.md`

## Maintenance Notes

- **Documentation cleanup log:** [`../FUNC_REPORT/DOCUMENTATION_CLEANUP_AUDIT_2026_05_27.md`](../FUNC_REPORT/DOCUMENTATION_CLEANUP_AUDIT_2026_05_27.md)
- **Documentation audit (FUNC_IMPORT):** [`../FUNC_IMPORT/INDEX.md`](../FUNC_IMPORT/INDEX.md) (lịch sử gộp §)
- Keep this file as route map only, not as a content duplicate.
- Add new docs in the nearest matching section.
- For major new domains, add a dedicated `<DOMAIN>_MASTER_GUIDE.md` and list it under Primary Entry Points.

## Auto-added by md_master_sync.ps1

- `FUNC_LOGIC/CF_SYSTEMWIDE_AUDIT_VI.md` _(retired pass 6)_
- `FUNC_LOGIC/ENV_LOCAL_VS_SERVER_HOSTNAMES_VI.md`
- `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`
- `FUNC_LOGIC/ERP_TECH_REVIEW_REPORT_VI.md`
- `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md`
- `FUNC_LOGIC/DESIGN_FRONTEND_UI.md` — layout/JS load (trước đây `FRONTEND_UI.md`)
- `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md` — đặc tả UI/UX tính năng mới (kèm `DESIGN_FRONTEND_UI.md` §5); trước đây `HUB_BACKEND_UI_UX_DESIGN_SPEC_VI.md` / `HUB_FORM_UI_CONVENTIONS_VI.md`
- `FUNC_LOGIC/HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md`
- `FUNC_LOGIC/MAOLIN_IMPORT_MAPPING.md`
- `FUNC_LOGIC/MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`
- `FUNC_LOGIC/PM_DEMO_SO_DO_PO_INVOICE_3PM_VI.md`
- `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md` — runbook inbound AI → SO (cập nhật client: URL dùng REST `/api/integrations/orders`; xem `AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`)
- `FUNC_LOGIC/AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md` — secret DB + `client_code` / `client_id` + audit payload (payload giữ nguyên; endpoint = REST)
- `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md` _(retired pass 6 — xem MAOLIN_MASTER_GUIDE)_
- `FUNC_LOGIC/PROMPT_REFACTOR_SO_DO_PO_GRN_VI.md`
- `FUNC_LOGIC/PURCHASE_RETURN_VENDOR_CREDIT_STOCK_VI.md`
- `FUNC_LOGIC/SALES_RETURN_CREDIT_NOTE_STOCK_VI.md`
- `FUNC_LOGIC/SCHEMATIC_USERS_CLIENT_1_1_VI.md`
- `FUNC_LOGIC/SUPERADMIN_PACKAGE_AUDIT_VI.md` _(retired pass 6)_
- `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`
- `FUNC_LOGIC/WH_PURCHASE_ENV_REFERENCE_VI.md`
- `FUNC_LOGIC/WAREHOUSE_TOM_TAT_NOI_BO.md`
- `FUNC_LOGIC/COMPANY_TRANSACTION_PURGE_GUIDE_VI.md` — purge giao dịch theo `company_id` (`company:purge-transactions`)
- `FUNC_LOGIC/MIAOLIN_SALES_ORDER_API_FIELDS.md`
