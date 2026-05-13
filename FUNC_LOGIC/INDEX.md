# FUNC_LOGIC Index

Navigation index for business logic, master guides, flow references, and audit reports.

## Primary Entry Points

- Group overview: `FUNC_LOGIC/README.md`
- Warehouse hub: `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`
- Maolin hub: `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`
- Sales fulfillment hub: `FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`

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
- `FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`
- `FUNC_LOGIC/API_DATA_TYPE_LIST_VI.md` — canonical (đã gộp nội dung trùng từ bản EN 2026-05)
- `FUNC_LOGIC/Libraries_And_Module_Names.md`
- `docs/AI_ORDER_INTEGRATION_REST.md` — REST AI order (`/api/integrations/orders`), auth, method toggles, troubleshooting 404
- `docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md` — hướng dẫn Postman / probe / CSRF
- `FUNC_LOGIC/AI_ORDER_REST_API_RUNTIME_AUDIT_VI.md` — audit luồng route + middleware + nguyên nhân 404 runtime
- `FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md` — đã gỡ `POST /ai-order-webhook/{hash}`; chỉ REST `/api/integrations/orders`

## Audit and Review Documents

- `FUNC_LOGIC/AUDIT_WAREHOUSE_MODULE_VI.md`
- `FUNC_LOGIC/AUDIT_PURCHASE_MODULE_VI.md`
- `FUNC_LOGIC/AUDIT_SALES_DO_FUNCTIONAL_VI.md`
- `FUNC_LOGIC/AUDIT_REPORTS_ERP_VI.md`
- `FUNC_LOGIC/AUDIT_BILLING_MODULE_VI.md`
- `FUNC_LOGIC/AUDIT_PERFORMANCE_MODULE_VI.md`
- `FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md`
- `FUNC_LOGIC/AUDIT_AI_ORDER_INBOUND_SO_API_VI.md` — inbound AI → Sales Order (lịch sử: webhook path; hiện dùng REST — xem `AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`)
- `FUNC_LOGIC/SURVEY_SYSTEM_WIDE_API_AND_REST_VI.md` — **khảo sát toàn repo** các route `/api` + mức độ REST; không có REST “toàn hệ thống”
- `FUNC_LOGIC/multi_warehouse_audit_report.md`

## Integration and Implementation Notes

- `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`
- `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`
- `FUNC_LOGIC/CLIENT_INLINE_VALIDATION_ROLLOUT.md`
- `FUNC_LOGIC/AI_LINE_TO_ORDER_ANALYSIS_VI.md`
- `FUNC_LOGIC/DEVELOPER_TOOLS_AUDIT_AND_FLOW_VI.md`
- `FUNC_LOGIC/DEVELOPER_TOOLS_EXT_PLAN.md`

## Maintenance Notes

- **Documentation audit (FUNC_LOGIC):** [`AUDIT_LOGIC_2026_VI.md`](AUDIT_LOGIC_2026_VI.md)
- **Documentation audit (FUNC_IMPORT — map cột, queue, prompt archive):** [`../FUNC_IMPORT/AUDIT_IMPORT_2026_VI.md`](../FUNC_IMPORT/AUDIT_IMPORT_2026_VI.md)
- Keep this file as route map only, not as a content duplicate.
- Add new docs in the nearest matching section.
- For major new domains, add a dedicated `<DOMAIN>_MASTER_GUIDE.md` and list it under Primary Entry Points.

## Auto-added by md_master_sync.ps1

- `FUNC_LOGIC/CF_SYSTEMWIDE_AUDIT_VI.md`
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
- `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`
- `FUNC_LOGIC/PROMPT_REFACTOR_SO_DO_PO_GRN_VI.md`
- `FUNC_LOGIC/PURCHASE_RETURN_VENDOR_CREDIT_STOCK_VI.md`
- `FUNC_LOGIC/SALES_RETURN_CREDIT_NOTE_STOCK_VI.md`
- `FUNC_LOGIC/SCHEMATIC_USERS_CLIENT_1_1_VI.md`
- `FUNC_LOGIC/SUPERADMIN_PACKAGE_AUDIT_VI.md`
- `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`
- `FUNC_LOGIC/WH_PURCHASE_ENV_REFERENCE_VI.md`
- `FUNC_LOGIC/WAREHOUSE_TOM_TAT_NOI_BO.md`
- `FUNC_LOGIC/AUDIT_LOGIC_2026_VI.md`
- `FUNC_LOGIC/MIAOLIN_SALES_ORDER_API_DATABASE_ALL_FIELDS.md`
- `FUNC_LOGIC/MIAOLIN_SALES_ORDER_API_DATABASE_REQUIRED_FIELDS.md`
