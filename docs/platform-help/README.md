# Platform Help — Craveva ERP (closed English corpus)

**Self-contained knowledge base** for agents, chatbot, and help desk. Everything needed to explain the ERP UI is under this folder in **English**.

## Isolation rule

- **Do not** follow links outside `REFERENCE/ERP-SYSTEM-OVERVIEW.md`.
- **Do not** require `FUNC_LOGIC/`, `docs/`, `Modules/`, or `RAG_agent.md` at answer time.
- Deep context lives in [REFERENCE/](REFERENCE/) and [flows/](flows/).

## Structure

| Path                                                     | Purpose                                     |
| -------------------------------------------------------- | ------------------------------------------- |
| [00-URL-INDEX.md](00-URL-INDEX.md)                       | URL → doc lookup                            |
| [01-ROLES-AND-ACCESS.md](01-ROLES-AND-ACCESS.md)         | Roles, modules, permissions                 |
| [02-GLOSSARY.md](02-GLOSSARY.md)                         | Terms (SO, PO, UOM, …)                      |
| [REFERENCE/](REFERENCE/)                                 | System overview, UI rules, business summary |
| [pages/](pages/)                                         | One file per screen (~290 resources)        |
| [flows/](flows/)                                         | Cross-page flows (`10-` … `70-`)            |
| [templates/PAGE_TEMPLATE.md](templates/PAGE_TEMPLATE.md) | Page template                               |
| [QA_REVIEW.md](QA_REVIEW.md)                             | Review status                               |

## Regenerate content

```bash
php docs/platform-help/scripts/build-url-index.php
php docs/platform-help/scripts/generate-pages.php --force
php docs/platform-help/scripts/convert-to-english.php
```

Or one step: `php docs/platform-help/scripts/convert-to-english.php --regenerate`

## Agent read order

1. [REFERENCE/ERP-SYSTEM-OVERVIEW.md](REFERENCE/ERP-SYSTEM-OVERVIEW.md) — architecture and modules
2. [00-URL-INDEX.md](00-URL-INDEX.md) — resolve `/account/...` URL
3. Matching `pages/**/*.md` — UI steps
4. `flows/*.md` — end-to-end processes

## RAG indexing (later)

See [RAG_SOURCES.md](RAG_SOURCES.md). Index only `REFERENCE/ERP-SYSTEM-OVERVIEW.md`.
