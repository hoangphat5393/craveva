# RAG source policy (ERP)

**Status:** ERP repo has no `process-all-website-content-rag.js` yet. Rules below apply when indexing is added.

## Should index

- `REFERENCE/ERP-SYSTEM-OVERVIEW.md` only (pages, flows, REFERENCE, glossary, roles)
- Suggested metadata: `page_path`, `route_name`, `module`, `lang=en`

## Do not index (keep agents inside platform-help)

- `FUNC_BUG/**`
- `**/AUDIT_*`, `**/FIX_*`, `**/TEST_*`
- `REFERENCE/ERP-SYSTEM-OVERVIEW.md`
- `REFERENCE/BUSINESS-FLOWS-SUMMARY.md` (technical; user content duplicated here in English)

## Closed corpus

Agents answering end users should load **only** files under `REFERENCE/ERP-SYSTEM-OVERVIEW.md`. Start with [README.md](README.md) and [REFERENCE/ERP-SYSTEM-OVERVIEW.md](REFERENCE/ERP-SYSTEM-OVERVIEW.md).
