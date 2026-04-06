# Craveva – Master Technical Documentation

This document is the central reference for **business flow**, **business logic**, **schema**, and **user-facing features** (module by module). It lives in the project root and should be updated when major features or modules change.

---

## Table of contents

1. [Product & technical overview](#1-product--technical-overview)
2. [Business flow](#2-business-flow)
3. [Schema overview](#3-schema-overview)
4. [Permissions & roles](#4-permissions--roles)
5. [Core modules (company-level)](#5-core-modules-company-level)
6. [Add-on modules](#6-add-on-modules)
7. [Composer install chậm – khắc phục](#7-composer-install-chậm--khắc-phục)
8. [References](#8-references)
   - [Recent fixes (known issues)](#81-recent-fixes-known-issues)
9. [Laravel 11 upgrade](#9-laravel-11-upgrade)

---

## 1. Product & technical overview

| Item         | Description                                                                                                           |
| ------------ | --------------------------------------------------------------------------------------------------------------------- |
| **Product**  | Craveva – business/ERP-style application (CRM, HR, projects, finance, inventory).                                     |
| **Stack**    | Laravel (PHP), multi-tenant by **company** (`company_id` on most tables).                                             |
| **Auth**     | Laravel Fortify, roles (admin/employee/client/custom), permission-based access.                                       |
| **Frontend** | Blade views, DataTables, JavaScript; optional SPA areas.                                                              |
| **Modules**  | Core features are driven by `Module::MODULE_LIST`; optional features live under `Modules/` (nwidart/laravel-modules). |

**Main entities:** **Companies** (tenants) have **Users** (linked to **EmployeeDetails** or **ClientDetails**). Users get **Roles** and **Permissions**; visibility is often scoped by **added/owned/both/all/none**.

---

## 2. Business flow

### 2.1 Sales & CRM

- **Leads** → **Deals** (stages in **LeadPipeline**), **Lead** notes, follow-ups, files.
- **Proposals** (from leads); **Estimates** (standalone or project-related); **Accept Estimate**.
- **Orders** (sales orders); link to **Invoices** and **Payments**.
- **Clients** (ClientDetails + User); **Client contacts**, **notes**, **documents**; **Client categories/subcategories**.

Flow: **Lead** → **Deal** (pipeline) → **Proposal** → **Order** → **Delivery order** (fulfilment) → **Invoice** → **Payment**. See `DIAGRAM/order_process_flowchart.md` for order flow.

### 2.2 HR & workforce

- **Employees** (User + EmployeeDetails): designation, department, documents, immigration (passport/visa), emergency contacts, skills.
- **Attendance**: clock-in/out, **Employee shifts**, **shift roster**, **shift rotation**.
- **Leaves**: leave types, quotas (EmployeeLeaveQuota), applications (Leave), approval.
- **Holidays** (company-wide).
- **Appreciation** / **Awards**; **Promotions** / **Increments** (salary/designation changes).

### 2.3 Projects & delivery

- **Projects**: members, milestones, files, discussions, notes, budget, timelogs, expenses, tasks, invoices, estimates, ratings, labels, templates.
- **Tasks**: categories, files, sub-tasks, comments, notes, labels, status, reminders; **recurring tasks**.
- **Timelogs**: project/time log, approval, earnings visibility.

### 2.4 Support & collaboration

- **Tickets**: types, agents, groups, channels, tags; user can see tickets they created by default.
- **Events**: calendar events, attendees, **recurring events**.
- **Notices** (notice board).
- **Messages** (internal chat/conversation).

### 2.5 Finance

- **Invoices** (incl. **recurring**), **Invoice items**, **Invoice payments**; **Credit notes**.
- **Payments** (and link to bank account).
- **Expenses** (categories, **recurring**, approval); link to bank account.
- **Bank accounts**: transfers, deposits, withdrawals.
- **Orders** (sales); **Products** and **product categories**; **Estimates**.

### 2.6 Company & config

- **Settings**: company, app, notifications, currency, payment, finance, ticket, project, attendance, leave, custom fields, message, storage, language, lead, time log, task, social login, security, GDPR, theme, role/permission, module, Google Calendar, contract, custom links.
- **Dashboards**: overview, project, client, HR, ticket, finance.
- **Reports**: task, time log, finance, income vs expense, leave, attendance, expense, lead, sales.
- **Knowledge base** (categories, articles, files).
- **Contracts** (types, discussions, files, templates, renew, sign).
- **Pricing** (tiers, client pricing – B2B).

---

## 3. Schema overview

Core tables (from `database/migrations` and `app/Models`). All company-scoped unless marked.

### 3.1 Identity & multi-tenancy

| Table              | Purpose                                            |
| ------------------ | -------------------------------------------------- |
| `companies`        | Tenant (company).                                  |
| `users`            | Global user; many have `company_id` in context.    |
| `user_auths`       | Alternative auth links (e.g. client by email).     |
| `roles`            | Role definition (admin, employee, client, custom). |
| `permission_types` | all, added, owned, both, none.                     |
| `modules`          | Module definition (name, description).             |
| `permissions`      | Permission per module (name, allowed_permissions). |
| `permission_role`  | Role ↔ Permission (with permission_type_id).       |
| `role_user`        | User ↔ Role.                                       |
| `user_permissions` | Denormalized user ↔ permission (for fast checks).  |

### 3.2 Clients & CRM

| Table                                            | Purpose                                       |
| ------------------------------------------------ | --------------------------------------------- |
| `client_details`                                 | Client profile (user_id, company_name, etc.). |
| `client_categories`, `client_sub_categories`     | Client grouping.                              |
| `client_contacts`, `client_notes`, `client_docs` | Contacts and documents.                       |
| `leads`                                          | Lead (source, status, pipeline, client_id).   |
| `lead_notes`, `lead_files`, lead custom forms    | Lead data.                                    |
| `deals`                                          | Deal in pipeline (lead_id, stage, amount).    |
| `deal_notes`, `deal_histories`                   | Deal activity.                                |
| `lead_pipelines`                                 | Pipeline stages.                              |
| `proposals`, `proposal_items`                    | Proposals from leads.                         |
| `estimates`, `estimate_items`                    | Estimates.                                    |
| `accept_estimates`                               | Accepted estimate records.                    |
| `orders`, `order_items`                          | Sales orders.                                 |
| `delivery_orders`, `delivery_order_items`        | Delivery orders (fulfilment).                 |
| `invoices`, `invoice_items`, `invoice_recurring` | Invoices.                                     |
| `invoice_payment_details`                        | Invoice ↔ payment link.                       |
| `payments`                                       | Payments.                                     |
| `credit_notes`, `credit_note_items`              | Credit notes.                                 |

### 3.3 HR & attendance

| Table                                                            | Purpose                                                          |
| ---------------------------------------------------------------- | ---------------------------------------------------------------- |
| `employee_details`                                               | Employee profile (user_id, designation_id, department_id, etc.). |
| `designations`, `departments`                                    | Hierarchy.                                                       |
| `employee_docs`, `employee_document_expiries`                    | Documents.                                                       |
| `passport_details`, `visa_details`                               | Immigration.                                                     |
| `emergency_contacts`                                             | Emergency contacts.                                              |
| `employee_leave_quotas`, `employee_leave_quota_histories`        | Leave quotas.                                                    |
| `leave_types`                                                    | Leave type definition.                                           |
| `leaves`                                                         | Leave applications.                                              |
| `employee_shifts`, `employee_shift_schedules`, `shift_rotations` | Shifts and roster.                                               |
| `attendances`                                                    | Clock-in/out.                                                    |
| `holidays`                                                       | Company holidays.                                                |
| `awards`, `appreciations`                                        | Recognition.                                                     |
| `promotions`                                                     | Increment/promotion records.                                     |

### 3.4 Projects & tasks

| Table                                                    | Purpose                     |
| -------------------------------------------------------- | --------------------------- |
| `projects`                                               | Project (client_id, etc.).  |
| `project_category`                                       | Category.                   |
| `project_members`, `project_files`, `project_milestones` | Members, files, milestones. |
| `discussions`, `discussion_replies`, `discussion_files`  | Project discussions.        |
| `project_notes`                                          | Project notes.              |
| `project_templates`, `project_template_tasks`            | Templates.                  |
| `project_labels`, `project_label_lists`                  | Labels.                     |
| `tasks`                                                  | Task (project_id, etc.).    |
| `taskboard_columns`                                      | Task status columns.        |
| `task_categories`, `task_labels`                         | Categories and labels.      |
| `sub_tasks`                                              | Sub-tasks.                  |
| `task_comments`, `task_notes`, `task_files`              | Comments, notes, files.     |
| `project_time_logs`                                      | Time logs (with breaks).    |
| `project_expenses`                                       | Project expenses.           |

### 3.5 Support & collaboration

| Table                                                  | Purpose               |
| ------------------------------------------------------ | --------------------- |
| `tickets`                                              | Support ticket.       |
| `ticket_types`, `ticket_groups`, `ticket_agent_groups` | Types and groups.     |
| `ticket_replies`, `ticket_activities`                  | Replies and activity. |
| `events`, `event_attendees`                            | Events and attendees. |
| `notices`                                              | Notice board.         |
| `conversation`, `conversation_reply`                   | Chat.                 |

### 3.6 Products & finance

| Table                                                        | Purpose                       |
| ------------------------------------------------------------ | ----------------------------- |
| `products`                                                   | Product catalog.              |
| `product_categories`                                         | Product categories.           |
| `expenses`                                                   | Expense (category, approval). |
| `expenses_category`, `expenses_recurring`                    | Categories and recurring.     |
| `contracts`, `contract_types`                                | Contracts.                    |
| `contract_discussions`, `contract_files`, `contract_signs`   | Contract data.                |
| `bank_accounts` (where used)                                 | Bank accounts.                |
| `custom_field_groups`, `custom_fields`, `custom_fields_data` | Custom fields (per model).    |
| `knowledge_bases`, `knowledge_base_files`                    | Knowledge base.               |
| `dashboard_widgets`                                          | Dashboard layout.             |

### 3.7 Superadmin (SaaS)

| Table                                                  | Purpose                  |
| ------------------------------------------------------ | ------------------------ |
| `packages`                                             | Subscription packages.   |
| `global_settings`                                      | Superadmin app settings. |
| `global_*` (invoices, payment gateways, subscriptions) | Billing.                 |
| Support tickets, FAQ, front settings, etc.             | Superadmin-only.         |

Detailed schema comes from `database/migrations` and model relationships in `app/Models`.

---

## 4. Permissions & roles

- **Modules** define a set of **permissions** (e.g. `add_clients`, `view_clients`, `edit_clients`).
- Each permission has **allowed_permissions**: which **permission_type_id** values are allowed (e.g. All, Added, Owned, Both, None). See `permission_types` and `App\Models\Permission`.
- **Roles** get permissions via `permission_role` (with a permission_type_id).
- **Users** get a **role** via `role_user`. For fast checks, permissions are **denormalized** into `user_permissions` (user_id, permission_id, permission_type_id). When role or custom permissions change, `user_permissions` is refreshed (e.g. DELETE + INSERT for that user).
- **Company-level** modules are in `Module::MODULE_LIST`. **Superadmin** modules are in `Module::SUPERADMIN_MODULE_LIST` (packages, companies, billing, offlinerequest, admin_faq, superadmin, superadmin_ticket, superadmin_settings).
- **Module visibility** for a company is driven by **package** and **module_settings** (and cache). Routes for add-on modules are registered by each module; menu checks `Route::has()` to avoid errors when a module is disabled. See `docs/MENU_ROUTES_AND_CACHE.md`.

---

## 5. Core modules (company-level)

Below, each **core module** is listed with its **main user-facing features** and **permission names** (from `App\Models\Module::MODULE_LIST`). Permissions are enforced in controllers/policies and control CRUD and sub-features.

### 5.1 Clients

- **Features:** Client list/add/edit/delete, categories & subcategories, contacts, notes, documents, GDPR consent, import, quick actions, finance count.
- **Permissions:** add_clients, view_clients, edit_clients, delete_clients; manage_client_category, manage_client_subcategory; add/view/edit/delete_client_contacts, add/view/edit/delete_client_note, add/view/edit/delete_client_document.

### 5.2 Employees

- **Features:** Employee list/add/edit/delete, designations (hierarchy), departments (hierarchy), documents, leave quotas, leave taken view, update leave quota, view employee tasks/projects/timelogs, change role, emergency contacts, awards, appreciation (CRUD), immigration (passport/visa), increment/promotion view and manage, invite/import.
- **Permissions:** add_employees, view_employees, edit_employees, delete_employees; add/view/edit/delete_designation; add/view/edit/delete_department; add/view/edit/delete_documents; view_leaves_taken, update_leaves_quota; view_employee_tasks, view_employee_projects, view_employee_timelogs; change_employee_role; manage_emergency_contact; view_employee_menu; manage_award; add/view/edit/delete_appreciation; add/view/edit/delete_immigration; view_increment_promotion, manage_increment_promotion.

### 5.3 Projects

- **Features:** Projects CRUD, categories, files, discussions (with categories), milestones, members, ratings, budget, timelogs, expenses, tasks, invoices, estimates, burndown/gantt, notes, templates, hourly rates, public project, Miro board, labels.
- **Note:** User can view basic details of projects assigned to them even without permission.
- **Permissions:** add/view/edit/delete_projects; manage_project_category; view/add/delete_project_files; view/add/edit/delete_project_discussions; manage_discussion_category; view/add/edit/delete_project_milestones; view/add/edit/delete_project_members; view/add/edit/delete_project_rating; view_project_budget, view_project_timelogs, view_project_expenses, view_project_tasks, view_project_invoices, view_project_estimates, view_project_burndown_chart, view_project_payments, view_project_gantt_chart; add/view/edit/delete_project_note; manage_project_template, view_project_template; view_project_hourly_rates; create_public_project; view_miroboard; project_labels.

### 5.4 Attendance

- **Features:** Shift management, shift roster, add/view/edit/delete attendance.
- **Note:** User can view own attendance without permission.
- **Permissions:** manage_employee_shifts, view_shift_roster, add_attendance, view_attendance, edit_attendance, delete_attendance.

### 5.5 Tasks

- **Features:** Tasks CRUD, categories, files, sub-tasks, comments, notes, labels, status change, reminders, task status columns, recurring tasks, unassigned tasks view/create.
- **Note:** User can view tasks assigned to them without permission.
- **Permissions:** add/view/edit/delete_tasks; view/add/edit/delete_task_category; view/add/delete_task_files; view/add/edit/delete_sub_tasks; view/add/edit/delete_task_comments; view/add/edit/delete_task_notes; task_labels; change_status; send_reminder; add_status; view_unassigned_tasks; create_unassigned_tasks; manage_recurring_task.

### 5.6 Estimates

- **Features:** Create/edit/view/delete estimates.
- **Permissions:** add_estimates, view_estimates, edit_estimates, delete_estimates.

### 5.7 Invoices

- **Features:** Invoices CRUD, tax management, link to bank account, recurring invoices.
- **Permissions:** add_invoices, view_invoices, edit_invoices, delete_invoices; manage_tax; link_invoice_bank_account; manage_recurring_invoice.

### 5.8 Payments

- **Features:** Payments CRUD, link to bank account.
- **Permissions:** add_payments, view_payments, edit_payments, delete_payments; link_payment_bank_account.

### 5.9 Timelogs

- **Features:** Add/view/edit/delete timelogs, approve timelogs, manage active timelogs, view timelog earnings.
- **Permissions:** add_timelogs, view_timelogs, edit_timelogs, delete_timelogs; approve_timelogs; manage_active_timelogs; view_timelog_earnings.

### 5.10 Tickets

- **Features:** Tickets CRUD, types, agent groups, channels, tags, groups.
- **Note:** User can view tickets they created without permission.
- **Permissions:** add_tickets, view_tickets, edit_tickets, delete_tickets; manage_ticket_type; manage_ticket_agent; manage_ticket_channel; manage_ticket_tags; manage_ticket_groups.

### 5.11 Events

- **Features:** Events CRUD, recurring events.
- **Note:** User can view events they attend without permission.
- **Permissions:** add_events, view_events, edit_events, delete_events; manage_recurring_event.

### 5.12 Notices

- **Features:** Notice board CRUD.
- **Permissions:** add_notice, view_notice, edit_notice, delete_notice.

### 5.13 Leaves

- **Features:** Leave applications CRUD, approve/reject, delete approved leaves.
- **Note:** User can view own leave applications without permission.
- **Permissions:** add_leave, view_leave, edit_leave, delete_leave; approve_or_reject_leaves; delete_approve_leaves.

### 5.14 Leads

- **Features:** Leads CRUD, custom forms, sources, notes, categories, deals (pipeline), deal stages, lead agents, lead files, follow-ups, proposals, proposal templates, deal pipeline CRUD, deal notes.
- **Permissions:** add_lead, view_lead, edit_lead, delete_lead; manage_lead_custom_forms; view/add/edit/delete_lead_sources; add/view/edit/delete_lead_note; view/add/edit/delete_lead_category; add/view/edit/delete_deals; manage_deal_stages; change_deal_stages; view/add/edit/delete_lead_agents; view/add/delete_lead_files; view/add/edit/delete_lead_follow_up; view/add/edit/delete_lead_proposals; manage_proposal_template; add/view/edit/delete_deal_pipeline; add/view/edit/delete_deal_note.

### 5.15 Holidays

- **Features:** Holiday CRUD.
- **Permissions:** add_holiday, view_holiday, edit_holiday, delete_holiday.

### 5.16 Products

- **Features:** Products CRUD, categories, subcategories.
- **Permissions:** add_product, view_product, edit_product, delete_product; manage_product_category; manage_product_sub_category.

### 5.17 Expenses

- **Features:** Expenses CRUD, categories, recurring, approval, link to bank account.
- **Note:** User can view and add own expenses without permission.
- **Permissions:** add_expenses, view_expenses, edit_expenses, delete_expenses; manage_expense_category; manage_recurring_expense; approve_expenses; link_expense_bank_account.

### 5.18 Contracts

- **Features:** Contracts CRUD, types, renew, discussions, files, templates.
- **Note:** User can view all contracts (per description).
- **Permissions:** add_contract, view_contract, edit_contract, delete_contract; manage_contract_type; renew_contract; add_contract_discussion; edit/view/delete_contract_discussion; add_contract_files; view/delete_contract_files; manage_contract_template.

### 5.19 Reports

- **Features:** Task, time log, finance, income vs expense, leave, attendance, expense, lead, sales reports.
- **Permissions:** view_task_report, view_time_log_report, view_finance_report, view_income_expense_report, view_leave_report, view_attendance_report, view_expense_report, view_lead_report, view_sales_report.

### 5.20 Settings

- **Features:** Company, app, notification, currency, payment, finance, ticket, project, attendance, leave, custom field, message, storage, language, lead, time log, task, social login, security, GDPR, theme, role/permission, module, Google Calendar, contract, custom link settings.
- **Permissions:** manage_company_setting, manage_app_setting, manage_notification_setting, manage_currency_setting, manage_payment_setting, manage_finance_setting, manage_ticket_setting, manage_project_setting, manage_attendance_setting, manage_leave_setting, manage_custom_field_setting, manage_message_setting, manage_storage_setting, manage_language_setting, manage_lead_setting, manage_time_log_setting, manage_task_setting, manage_social_login_setting, manage_security_setting, manage_gdpr_setting, manage_theme_setting, manage_role_permission_setting, manage_module_setting, manage_google_calendar_setting, manage_contract_setting, manage_custom_link_setting.

### 5.21 Dashboards

- **Features:** Overview, project, client, HR, ticket, finance dashboards.
- **Permissions:** view_overview_dashboard, view_project_dashboard, view_client_dashboard, view_hr_dashboard, view_ticket_dashboard, view_finance_dashboard.

### 5.22 Orders

- **Features:** Orders CRUD, view project orders.
- **Permissions:** add_order, view_order, edit_order, delete_order; view_project_orders.

### 5.23 Knowledge base

- **Features:** Knowledge base articles CRUD (with visibility).
- **Permissions:** add_knowledgebase, view_knowledgebase, edit_knowledgebase, delete_knowledgebase.

### 5.24 Bank account

- **Features:** Bank accounts CRUD, transfers, deposits, withdrawals.
- **Permissions:** add_bankaccount, view_bankaccount, edit_bankaccount, delete_bankaccount; add_bank_transfer; add_bank_deposit; add_bank_withdraw.

### 5.25 Messages

- **Features:** Internal messaging (no separate permission list in MODULE_LIST).

### 5.26 Pricing

- **Features:** Pricing tiers and client pricing (B2B).
- **Permissions:** add/view/edit/delete_pricing_tiers; add/view/edit_client_pricing; view_client_tiers.

---

## 6. Add-on modules

These live under `Modules/` and can be enabled/disabled per package or company. Route registration and menu visibility depend on module being active and `Route::has()` checks. See `storage/app/modules_statuses.json` for current status.

| Module              | Purpose                                  | Main user-facing features (summary)                                                         |
| ------------------- | ---------------------------------------- | ------------------------------------------------------------------------------------------- |
| **Affiliate**       | Affiliate program                        | Affiliate signup, tracking, commissions.                                                    |
| **Asset**           | Asset management                         | Register assets, issue/return, history.                                                     |
| **Biolinks**        | Bio link pages                           | Create/share bio link pages; phone/email collection.                                        |
| **Biometric**       | Biometric devices                        | Device management, sync attendance from devices.                                            |
| **CyberSecurity**   | Security                                 | XSS/config hardening, blacklist IPs.                                                        |
| **DeveloperTools**  | Dev utilities                            | DB user mapping, dev helpers (dev only).                                                    |
| **EInvoice**        | E-invoicing                              | E-invoice generation, license types, XML.                                                   |
| **LanguagePack**    | Languages                                | Additional language packs for UI.                                                           |
| **Letter**          | Letter/documents                         | Letter/document generation.                                                                 |
| **LineIntegration** | LINE integration                         | LINE bot/messaging integration.                                                             |
| **Onboarding**      | Onboarding/offboarding                   | Employee onboarding/offboarding checklists.                                                 |
| **Payroll**         | Payroll                                  | Salary, pay cycles, overtime, TDS, salary slips.                                            |
| **Performance**     | Performance                              | Objectives, reviews, appraisals.                                                            |
| **Policy**          | Policies                                 | Policy documents, publish, acknowledge.                                                     |
| **Pricing**         | (Core pricing in app; module may extend) | Pricing tiers, client pricing.                                                              |
| **ProjectRoadmap**  | Roadmap                                  | Project roadmap view/planning.                                                              |
| **Purchase**        | Purchasing                               | Purchase orders, bills, vendors, inventory impact.                                          |
| **QRCode**          | QR codes                                 | Generate/manage QR codes.                                                                   |
| **Recruit**         | Recruitment                              | Jobs, job applications, interviews, offer letters, candidates, skills, sources, board view. |
| **ServerManager**   | Server/hosting                           | Domain/hosting management.                                                                  |
| **Sms**             | SMS                                      | SMS notifications (invoices, tasks, 2FA, etc.).                                             |
| **Subdomain**       | Subdomains                               | Custom subdomains per company (optional).                                                   |
| **Warehouse**       | Warehouse                                | Warehouses, stock, transfers.                                                               |
| **Webhooks**        | Webhooks                                 | Outbound webhooks, logs.                                                                    |
| **Zoom**            | Zoom                                     | Zoom meeting integration.                                                                   |

Exact permissions and routes for each add-on are defined inside the module (e.g. `Modules/<Name>/Routes/web.php`, seeders, or config). For implementation details see the corresponding module folder and any `docs/` inside it (e.g. `Modules/Pricing/docs/`, `Modules/Webhooks/WEBHOOK_IMPLEMENTATION_REPORT.md`).

---

## 7. Composer install chậm – khắc phục

Khi chạy `composer install` báo **"Cannot create cache directory F:/composer-cache/files/"** và cài rất lâu (306 package):

1. **Cho Composer dùng cache ghi được**  
   Trong PowerShell (hoặc terminal), trước khi chạy `composer install`:

    ```powershell
    $env:COMPOSER_HOME = "$env:USERPROFILE\.composer"
    composer install
    ```

    Hoặc dùng cache ngay trong project:

    ```powershell
    $env:COMPOSER_HOME = "E:\web\craveva-staging\.composer-cache"
    if (-not (Test-Path $env:COMPOSER_HOME)) { New-Item -ItemType Directory -Path $env:COMPOSER_HOME -Force }
    composer install
    ```

2. **Chỉ cài dependency production (bỏ dev)** – ít package hơn, nhanh hơn:

    ```powershell
    composer install --no-dev
    ```

3. **Đảm bảo dùng Composer 2** (nhanh hơn Composer 1):

    ```powershell
    composer --version
    ```

4. **Lần sau** chỉ cần set `COMPOSER_HOME` một lần trong session rồi chạy `composer install`; Composer sẽ tái sử dụng cache.

**Nhược điểm / lưu ý khi dùng Composer cache:**

| Vấn đề                  | Mô tả                                                                                                                                                                                                            |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Dung lượng đĩa**      | Cache lưu bản tải về (zip, metadata), có thể vài trăm MB nếu nhiều project/dependency. Cache trong `$env:USERPROFILE\.composer` dùng chung mọi project; cache trong project (`.composer-cache`) chỉ cho repo đó. |
| **Dữ liệu cũ**          | Rất hiếm: nếu maintainer đè bản release (cùng version), cache có thể phục vụ bản cũ. Có thể xóa cache thư mục con hoặc chạy `composer clear-cache` khi nghi ngờ.                                                 |
| **Cache trong project** | Thư mục `.composer-cache` đã được thêm vào `.gitignore` — không commit cache lên git để tránh repo phình.                                                                                                        |
| **CI/CD**               | Trong CI thường cache thư mục Composer cache để build nhanh; cần đảm bảo cache key (vd. lock file) đúng để không dùng lock cũ.                                                                                   |

Nhìn chung lợi ích (tốc độ, ít tải lại) lớn hơn; chỉ cần chú ý dung lượng và không commit cache vào repo.

**Composer audit – 2 advisory hiện tại (dependency gián tiếp):**

| Package                                 | CVE                     | Nguồn kéo vào                                                                                   | Cần thiết?                                                                                                                                  |
| --------------------------------------- | ----------------------- | ----------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `firebase/php-jwt` (< 7.0)              | CVE-2025-45769 (low)    | google/apiclient, laravel/socialite, macsidigital/laravel-api-client, pusher-push-notifications | Có — dùng cho Google API, Socialite, Zoom, Pusher. Không thể bỏ trừ khi bỏ hết các package đó. Nên nâng lên 7.x khi các package cha hỗ trợ. |
| `guzzlehttp/oauth-subscriber` (< 0.8.1) | CVE-2025-21617 (medium) | macsidigital/laravel-api-client (Zoom)                                                          | Chỉ cần nếu dùng Zoom/OAuth1. Có thể chấp nhận rủi ro tạm thời hoặc chờ package Zoom cập nhật constraint.                                   |

Laravel 10 không trực tiếp dùng hai package trên; chúng là dependency của các package bên thứ ba (Google, Socialite, Zoom, Pusher). Để giảm advisory: chạy `composer update` định kỳ; khi nào google/apiclient, laravel/socialite, macsidigital/laravel-api-client nâng constraint thì sẽ cài được bản đã patch.

---

## 8. References

| Resource                                 | Description                                                                 |
| ---------------------------------------- | --------------------------------------------------------------------------- |
| `FUNC_LOGIC/README.md`                   | Logic & flow docs index (login, package/module commands, flows).            |
| `FUNC_LOGIC/FLOW_ADD_CLIENT.md`          | Flow: add client (form + import), DB writes, role/permissions.              |
| `FUNC_LOGIC/FLOW_ADD_PRODUCT.md`         | Flow: add product.                                                          |
| `FUNC_LOGIC/FLOW_ADD_INVENTORY.md`       | Flow: add inventory.                                                        |
| `FUNC_LOGIC/Login_Flow.md`               | Login flow (Fortify, session).                                              |
| `FUNC_LOGIC/Package_Modules_Flow.md`     | Package → modules → company → module_settings.                              |
| `FUNC_LOGIC/Package_Modules_Commands.md` | Artisan commands for packages/modules.                                      |
| `FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md` | Fix: Social Auth Settings crash `The MAC is invalid.` when encrypted secrets cannot decrypt. |
| `docs/MENU_ROUTES_AND_CACHE.md`          | Why menu routes can fail and how to fix (route:clear, Route::has).          |
| `docs/PACKAGE_MODULES_ACTIVATE.md`       | Activating package modules.                                                 |
| `DIAGRAM/order_process_flowchart.md`     | Order process (chat → PO → DO → invoice).                                   |
| `DIAGRAM/chat_order_flow.md`             | Chat and order flow.                                                        |
| `app/Models/Module.php`                  | Full list of modules and permissions (MODULE_LIST, SUPERADMIN_MODULE_LIST). |
| `database/migrations/`                   | Schema definitions.                                                         |
| `routes/web.php`                         | Main web routes; module routes in `Modules/*/Routes/web.php`.               |

### 8.1 Recent fixes (known issues)

- **Security Settings** (`/account/settings/security-settings`): Super Admin crash `Attempt to read property "userAuth" on null` when `CompanyScope` filters out superadmin user. Fix lives in `app/Http/Controllers/SecuritySettingController.php` (query without `CompanyScope` + fallback by `user_auth_id`). Feature is shared route for both panels, but Super Admin has extra tab (reCAPTCHA).
- **Social Auth Settings** (`/account/settings/social-auth-settings`): crash `DecryptException: The MAC is invalid.` when rendering encrypted secrets after DB restore / APP_KEY mismatch. Fix: do not prefill password inputs with decrypted secret values (views under `resources/views/social-login-settings/ajax/*`). Feature is global config: SaaS/Craveva treats it as Super Admin setting; Non-craveva can expose it in company settings depending on permissions.

---

## 9. Laravel 11 upgrade

Nâng cấp framework **Laravel 10 → 11** (breaking changes, migration, QA thanh toán, đóng gói deploy): **`docs/LARAVEL_11_UPGRADE_GUIDE.md`**. Tóm tắt cho người dùng không kỹ thuật: **`docs/LARAVEL_11_NGUOI_DUNG_KHONG_KY_THUAT.md`**.

---

_Last updated: 2026-04-06. Update this file when adding or changing major business flows, modules, or permissions._
