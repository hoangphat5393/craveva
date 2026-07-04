Để chatbot trả lời **đúng từng URL và đủ thao tác trên UI**, bạn cần tài liệu **viết cho end-user**, không chỉ dựa vào những gì RAG đang tự thu thập. Dưới đây là bức tranh hệ thống hiện tại và danh sách file nên yêu cầu agent dự án tổng hợp.

## Hệ thống RAG đang “ăn” gì?

Script `process-all-website-content-rag.js` index:

| Nguồn                                    | Vào RAG?                                                                                      | Đủ cho “dùng từng URL”?                                                 |
| ---------------------------------------- | --------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| `AI.CRAVEVA.COM MASTER DOCUMENT.md`      | Có                                                                                            | Chủ yếu kỹ thuật/dev handover                                           |
| Mọi `docs/**/*.md` (trừ file bị exclude) | Có                                                                                            | Lẫn audit/test/fix — nhiều file không phải hướng dẫn user               |
| `frontend/data/blog-posts.ts`            | Có                                                                                            | Marketing, không phải manual từng màn                                   |
| Text tách từ `frontend/app/**/page.tsx`  | Có (shallow)                                                                                  | Chỉ label/string tĩnh, **không** flow click, validation, lỗi thường gặp |
| `docs/user-guide/`                       | **Chưa có** (đã lên kế hoạch trong `IMPLEMENTATION_PRIORITY_AND_TASKS.md` nhưng folder trống) | Đây là chỗ nên đặt docs theo URL                                        |

Kết luận: để bot hỗ trợ **toàn bộ tính năng theo URL**, cần **bộ docs mới có cấu trúc**, rồi chạy lại script index (bạn hoặc agent làm sau khi viết xong).

---

## Yêu cầu agent: viết những file nào?

### 1. File “khung” (bắt buộc trước)

| File                                        | Mục đích                                                                  |
| ------------------------------------------- | ------------------------------------------------------------------------- |
| `docs/platform-help/00-URL-INDEX.md`        | Bảng tra: URL → tên màn → role → file doc chi tiết                        |
| `docs/platform-help/01-ROLES-AND-ACCESS.md` | `admin` / `super_admin` / `master_admin`: ai vào URL nào                  |
| `docs/platform-help/02-GLOSSARY.md`         | Thuật ngữ: agent, data source, deployment, semantic layer, RAG, template… |

Mỗi doc con nên có **dòng đầu**: `URL: /path`, `Roles: ...`, `Related URLs: ...` để RAG retrieve đúng chunk.

---

### 2. Docs theo URL (một file ≈ một route hoặc một nhóm route gần nhau)

Đặt trong `docs/platform-help/pages/` (hoặc `docs/user-guide/pages/`). **Template mỗi file** agent nên tuân theo:

```markdown
# [Tên màn hình]

URL: /agents/builder
Roles: admin (company user)
Prerequisites: đã login, có plan/active subscription nếu có

## Mục đích

## Ai dùng / quyền

## Các bước thao tác (numbered, theo UI thật)

## Trường / nút / tab quan trọng

## Kết quả mong đợi sau mỗi bước

## Lỗi thường gặp + cách xử lý

## Câu hỏi user hay hỏi (FAQ ngắn)

## Liên kết flow khác (URL khác)
```

#### A. Marketing & public (không cần login)

- `home.md` → `/`
- `features.md` → `/features`
- `pricing.md` → `/pricing`
- `about.md`, `contact.md`, `careers.md`
- `solutions-overview.md` + từng file: `solutions-ai-layer.md`, `solutions-data-layer.md`, `solutions-deployment.md`, `solutions-infrastructure.md`, `solutions-security.md`, `solutions-architecture.md`
- `ai-models.md`, `ai-tools.md`, `ai-tools-slug.md` (pattern `/ai-tools/[slug]`)
- `resources.md`, `resources-integrations.md`
- `partners.md`, `partnership.md`, `partners-catalog.md`, `partners-brand-guide.md`
- `community.md`, `security-public.md`, `certifications.md`, `code-agents.md`, `templates.md`, `beta.md`
- `documentation-hub.md` → `/documentation` và các nhánh: `documentation-guides.md`, `documentation-api-reference.md`, `documentation-best-practices.md`, `documentation-troubleshooting.md`, `documentation-category.md`
- `blog.md`, `blog-post.md` (pattern `/blog/[slug]` — có thể tham chiếu blog RAG hiện có)
- `api-reference-public.md` → `/api-reference`

#### B. Auth, onboarding, billing công khai

- `login.md`, `register.md`, `forgot-password.md`, `reset-password.md`
- `select-plan.md`, `checkout.md`, `checkout-success.md`

#### C. Product sau login (customer chính — **ưu tiên cao nhất**)

- `dashboard.md` → `/dashboard`
- `account-profile.md`, `account-preferences.md`
- `agents-list.md` → `/agents`
- **`agents-builder.md`** → `/agents/builder` (bắt buộc chi tiết: từng loại source, từng bước wizard — đối chiếu `AgentBuilderClient.tsx` / master doc § Agent Builder)
- `data-sources-list.md` → `/data-sources`
- `data-sources-builder.md` → `/data-sources/[id]/builder`
- `data-sources-view.md` → `/data-sources/[id]/view`
- `chat-session.md` → `/chat/[sessionId]`
- `deployment-widget.md` → `/[deploymentId]` (embed/test widget)
- `share-deployment.md` → `/share/deployments/[deploymentId]`
- **Billing (từng URL):** `billing-overview.md`, `billing-subscription.md`, `billing-credits.md`, `billing-addons.md`, `billing-invoices.md`, `billing-invoice-detail.md`, `billing-payment-methods.md`, `billing-payments.md`, `billing-support.md`, `billing-usage.md`

#### D. Panel Admin (`/panel/admin/...`) — ~15 màn

Ví dụ: `panel-admin-home.md`, `panel-admin-users.md`, `panel-admin-deployments.md`, `panel-admin-storage.md`, `panel-admin-storage-company.md`, `panel-admin-chat-history.md`, `panel-admin-support-tickets.md`, `panel-admin-analytics.md`, `panel-admin-billing.md`, `panel-admin-billing-model-pricing.md`, `panel-admin-billing-model-packages.md`, `panel-admin-billing-model-access.md`, `panel-admin-billing-credits-adjust.md`, `panel-admin-billing-invoices-generate.md`, `panel-admin-billing-billable-items-create.md`, `panel-admin-platform-chat-widget.md`, `panel-support-tickets.md`

#### E. Panel Super Admin (`/panel/super-admin/...`) — ~20 màn

Ví dụ: home, users, organizations, licenses, pricing, credits, usage, analytics, queries, alerts, tracking, translations, preferences, support-tickets, support-call-config, billing, billing-invoices, admins, **ai-platform-agents**, **ai-platform-data-sources**, **ai-platform-deployments**

→ Cần doc riêng cho **Prompt Engineering / template** (UI chủ yếu ở super admin; tham chiếu `docs/PLATFORM_CHAT_PROMPT_ENGINEERING.md` nhưng viết lại theo thao tác UI).

#### F. Panel Master Admin

- `panel-master-admin-home.md`, `panel-master-admin-users.md`, `panel-master-admin-pricing.md`, `panel-master-admin-support-tickets.md`

---

### 3. Docs “flow xuyên URL” (không thay thế doc từng URL, nhưng bot cần để trả lời “làm sao từ A → Z”)

| File                                                        | Nội dung                                                   |
| ----------------------------------------------------------- | ---------------------------------------------------------- |
| `docs/platform-help/flows/10-first-agent-end-to-end.md`     | Data source → agent → test → deploy                        |
| `docs/platform-help/flows/20-data-sources-by-type.md`       | File, DB, Google Drive, REST, GraphQL, Webhook — từng loại |
| `docs/platform-help/flows/30-deployments-channels.md`       | Widget, WhatsApp, Telegram, API, webhook…                  |
| `docs/platform-help/flows/40-semantic-layer-and-queries.md` | MDL, NL→SQL, scope tenant                                  |
| `docs/platform-help/flows/50-billing-and-limits.md`         | Credits, subscription, usage                               |
| `docs/platform-help/flows/60-troubleshooting.md`            | Lỗi phổ biến theo khu vực sản phẩm                         |

Có thể bổ sung file đã có trong `docs/` nếu đúng nghiệp vụ (ví dụ deployment, semantic layer) — agent **tóm tắt lại góc user**, không copy nguyên audit/test.

---

### 4. Cập nhật (không chỉ tạo mới)

| File                                                                               | Việc agent nên làm                                                                                |
| ---------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `AI.CRAVEVA.COM MASTER DOCUMENT.md`                                                | Giữ phần kỹ thuật; thêm/đồng bộ mục “Customer usage per URL” hoặc link sang `docs/platform-help/` |
| `docs/CHAT_DATA_SOURCES.md`                                                        | Ghi rõ nguồn mới `platform-help`                                                                  |
| `Test Scripts/rag-exclusions.js` (hoặc `backend/scripts/rag-exclusions.js` nếu có) | **Không** index file FIX*\*, TEST*\_, AUDIT\_\_; **có** index `platform-help/`                    |

---

## Prompt mẫu bạn gửi agent dự án

```text
Mục tiêu: Viết bộ tài liệu end-user cho Platform Chat RAG, mỗi URL chính một file markdown trong docs/platform-help/pages/.

Nguồn sự thật:
1. Quét frontend/app/**/page.tsx và map URL (bỏ /test).
2. Đối chiếu AI.CRAVEVA.COM MASTER DOCUMENT.md và docs liên quan (agent builder, data sources, deployment, billing).
3. Không copy secret/credential; không dùng nội dung FIX_*, TEST_*, AUDIT_*.

Deliverables:
- docs/platform-help/00-URL-INDEX.md (đủ ~100 route, gom dynamic route thành [param])
- docs/platform-help/01-ROLES-AND-ACCESS.md
- docs/platform-help/pages/*.md theo template (URL, Roles, Steps, FAQ, Errors)
- docs/platform-help/flows/*.md (5–6 flow xuyên trang)
- Cập nhật master doc: link tới platform-help

Sau khi xong: chạy node backend/scripts/process-all-website-content-rag.js và xác nhận chunk có metadata page_path / category phù hợp.
```

---

## Lưu ý thực tế

1. **Chỉ index file `.md` trong `docs/`** — blog và page.tsx đã có pipeline riêng; docs theo URL vẫn là cách ổn định nhất cho “bấm nút nào, ở đâu”.
2. **Extract từ `page.tsx` không đủ** — agent phải đọc component (ví dụ `AgentBuilderClient.tsx`), không chỉ `page.tsx`.
3. **Phân quyền** — cùng URL có thể khác nội dung theo role; ghi rõ trong từng file.
4. **Dynamic routes** — viết theo pattern (`/data-sources/[id]/builder`), không viết cho từng ID cụ thể.
5. Sau khi viết xong, knowledge base chỉ cập nhật khi chạy `process-all-website-content-rag.js` (hoặc admin reprocess master doc — chỉ legacy).

Tóm lại: yêu cầu agent tạo **`docs/platform-help/`** (index + roles + **~80–100 page doc** + **5–6 flow doc**), đồng bộ master doc, loại trừ tài liệu nội bộ khỏi RAG, rồi re-index. Đó là bộ tối thiểu để widget trả lời đủ “trên từng URL” thay vì chỉ marketing + kiến trúc kỹ thuật như hiện tại.

---

## Biến thể ERP (Craveva staging — Laravel)

Repo **`craveva-staging`** dùng URL **`/account/...`**, role **`admin` / `employee` / `client`**, permission `view_*` + `user_modules()` — **không** dùng danh sách route AI platform ở trên.

| ERP (đã triển khai) | Đường dẫn                                                                              |
| ------------------- | -------------------------------------------------------------------------------------- |
| Hub + index URL     | [docs/platform-help/README.md](docs/platform-help/README.md)                           |
| Bảng tra URL        | [docs/platform-help/00-URL-INDEX.md](docs/platform-help/00-URL-INDEX.md)               |
| Roles & RAG rules   | [docs/platform-help/01-ROLES-AND-ACCESS.md](docs/platform-help/01-ROLES-AND-ACCESS.md) |
| FUNC_LOGIC index    | [FUNC_LOGIC/INDEX.md](FUNC_LOGIC/INDEX.md) (mục platform-help)                         |

Regenerate index: `php docs/platform-help/scripts/build-url-index.php`. RAG index ERP: phase sau — xem [docs/platform-help/RAG_SOURCES.md](docs/platform-help/RAG_SOURCES.md).
