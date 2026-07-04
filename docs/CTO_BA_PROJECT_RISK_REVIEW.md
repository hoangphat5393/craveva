# CTO / BA Project Risk Review

**Updated:** 2026-06-22  
**Scope:** Craveva ERP staging project, Laravel 11 multi-tenant ERP with modular architecture.  
**Purpose:** Track senior BA/CTO-level project assessment, risks, and improvement priorities.

---

## 1. Executive Summary

This project is no longer a small Laravel CRUD application. It is a medium-to-large ERP platform with real operational depth:

- CRM / Sales / Estimates / Orders / Invoices
- Purchase / GRN / Sales Delivery Order
- Warehouse / stock batches / reservations / movements
- Production / BOM / production orders / batches
- Pricing / client pricing / tiers
- Import pipelines
- LanguagePack / multi-language operations
- Multi-tenant company/module settings
- Biomixing and Maolin customer-specific pilots
- AI / third-party order API direction

The system has real commercial potential, but it needs stronger release discipline, tenant safety, data correctness controls, and operational monitoring to keep scaling safely.

---

## 2. Strengths

| Area | Assessment |
| --- | --- |
| Business depth | Strong. The project covers real ERP workflows, not only simple CRUD. |
| Documentation | Better than average. `FUNC_LOGIC`, `FUNC_IMPROVE`, `FUNC_BUG`, `docs`, and project folders provide useful context. |
| Modular structure | Good direction. `Modules/` separates major domains such as Warehouse, Production, Pricing, Purchase. |
| Operational awareness | Good. There are runbooks for staging/hub deploy, permissions, LanguagePack, queue, server issues. |
| Test presence | Moderate. There are many Pest/PHP tests and some critical flow tests. |
| Product direction | Promising. Biomixing, Maolin, AI order API, Warehouse/Production flows show productization potential. |

---

## 3. Main Risks

### R1. Multi-Tenant Data Safety

**Risk:** Missing `company_id` scope, wrong global scope usage, unsafe export/download/API routes can expose or mutate another tenant's data.

**Why it matters:** This is the highest-risk class for SaaS ERP. One IDOR bug can become a serious data breach.

**Track:**

- Controllers that use `find($id)` without tenant constraint.
- Export/download endpoints.
- Import jobs.
- AI/API endpoints.
- Superadmin exceptions.

### R2. Warehouse / Production Data Correctness

**Risk:** Stock, reservation, production consumption, FG receipt, SO/DO, and invoice flows can diverge if each path updates inventory differently.

**Why it matters:** ERP credibility depends on stock and financial data being correct.

**Track:**

- Single source of truth for stock movement.
- Reservation lifecycle.
- Production batch post RM / post FG.
- Sales DO ship/deduct logic.
- Return flows and credit notes.

### R3. Release Discipline

**Risk:** The current worktree can contain many unrelated modified/deleted/untracked files. Deploy scripts can commit/push broad changes if run from a dirty tree.

**Why it matters:** Accidental deploy can ship unrelated docs, generated files, runtime artifacts, or half-finished feature changes.

**Track:**

- `git status --short` before any deploy.
- Separate branches for docs cleanup, feature work, migrations, and hotfixes.
- Avoid deploying from a dirty workspace.

### R4. Operational Permissions

**Risk:** Runtime folders can be owned by deploy user instead of web user, causing cache/session/import failures.

**Why it matters:** Hub incident showed `storage/framework/cache/data` permission drift can take the live login page down.

**Track:**

- `storage/` and `bootstrap/cache/` owner should be `www-data:www-data` on staging/hub.
- Run artisan cache commands as `www-data`.
- Run git commands as deploy owner.

### R5. Import and Large Data Processing

**Risk:** Large Excel/CSV imports can time out, create partial data, consume memory, or run duplicate jobs.

**Track:**

- Chunked import.
- Queue isolation.
- Idempotency key per file/company/user.
- Progress status.
- Rollback / failure reporting.

### R6. AI / API Access Control

**Risk:** AI or third-party integration can read too broadly or bypass UI policies if it touches DB/service logic directly.

**Track:**

- Fixed API endpoints, not raw SQL.
- Same company/user permission policy as UI.
- Request logging with `company_id`, user/service identity, request id.
- Rate limits and query cost limits.

### R7. Bus Factor

**Risk:** One developer cannot safely own all ERP domains, deployment, QA, database, security, and customer implementation long-term.

**Recommended minimum team:**

- 1 senior Laravel/backend lead
- 1 backend developer
- 0.5-1 QA
- 0.25-0.5 DevOps/SRE
- BA/Product owner involvement for acceptance criteria

---

## 4. 90-Day Improvement Priorities

| Priority | Workstream | Goal |
| --- | --- | --- |
| P0 | Release discipline | No deploy from dirty worktree; branch and commit hygiene. |
| P0 | Tenant safety audit | Review critical routes/jobs/API for `company_id` safety. |
| P0 | Storage/cache permissions | Standardize staging/hub runtime ownership and artisan user. |
| P1 | Warehouse/Production correctness | Consolidate stock movement and reservation rules behind services. |
| P1 | Critical test suite | Keep reliable tests for auth, sales, invoice, warehouse, production, import, pricing. |
| P1 | Observability | Sentry/logging with request id, company id, user id. |
| P2 | Import hardening | Chunk, queue, progress, idempotency, clear failure reporting. |
| P2 | AI/API guardrails | Only expose policy-checked service/API endpoints. |
| P2 | Documentation governance | Keep `FUNC_INDEX.md`, module guides, bug registry, and runbooks current. |

---

## 5. Tracking Checklist

Use this as a recurring review checklist.

### Weekly

- [ ] Review production/staging 5xx and Laravel logs.
- [ ] Check deploys were done from clean branches.
- [ ] Review new migrations for destructive or tenant-risk changes.
- [ ] Run targeted critical tests for recently touched modules.
- [ ] Confirm no runtime/generated folders are included in commits.

### Before Deploy

- [ ] `git status --short` reviewed.
- [ ] Branch and commit scope are clear.
- [ ] Migrations reviewed.
- [ ] Critical tests run or explicitly skipped with reason.
- [ ] Backup plan known for hub/live.
- [ ] `storage/` and `bootstrap/cache/` permissions understood.

### Monthly

- [ ] Tenant safety audit sample.
- [ ] Restore backup to a non-production environment.
- [ ] Review top slow queries / heavy DataTables screens.
- [ ] Review import failure logs.
- [ ] Update `FUNC_BUG/SO_LOI.md` for repeated incidents.

---

## 6. Current CTO Assessment

The project has strong product potential and real ERP value. The main concern is not the Laravel stack or Bootstrap UI. The main concerns are:

1. Data correctness.
2. Tenant safety.
3. Deploy discipline.
4. Operational monitoring.
5. Bus factor.

If these are controlled, the project can grow into a serious ERP/B2B platform. If not, the project will become increasingly expensive to maintain as more customer-specific workflows are added.

---

## 7. Related References

- `AGENTS.md`
- `FUNC_INDEX.md`
- `MASTER_DOCUMENTATION.md`
- `docs/ERP_SCALING.md`
- `docs/SERVER_RUNBOOK.md`
- `docs/CI_PEST_SAFE.md`
- `FUNC_BUG/SO_LOI.md`
- `FUNC_LOGIC/INDEX.md`
- `FUNC_IMPROVE/INDEX.md`
- `FUNC_TEST/INDEX.md`
