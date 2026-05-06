# Markdown Header Template

Use this header block at the top of every new functional documentation file.

```md
# <DOCUMENT_TITLE>

## Metadata

| Field                | Value                                                   |
| -------------------- | ------------------------------------------------------- |
| Doc ID               | <DOC_ID>                                                |
| Module               | <MODULE_NAME>                                           |
| Group                | <FUNC_BUG \| FUNC_IMPORT \| FUNC_IMPROVE \| FUNC_LOGIC> |
| Status               | <draft \| active \| review \| deprecated \| archived>   |
| Owner                | <TEAM_OR_PERSON>                                        |
| Last Updated         | <YYYY-MM-DD>                                            |
| Source of Truth      | <Yes/No>                                                |
| Related Master Guide | <PATH_TO_MASTER_GUIDE>                                  |
| Related Docs         | <DOC_PATH_1, DOC_PATH_2, ...>                           |

## Purpose

<Why this document exists and what decision/process it supports.>

## Scope

- In scope: <items>
- Out of scope: <items>

## Business/Technical Context

<Short context required before reading details.>
```

## Recommended Section Order

1. Purpose
2. Scope
3. Preconditions
4. Main flow or implementation details
5. Rules and validations
6. Risks and limitations
7. Test or verification checklist
8. Change log

## Minimal Change Log Template

```md
## Change Log

| Date       | Author | Change    |
| ---------- | ------ | --------- |
| YYYY-MM-DD | <name> | <summary> |
```

## Naming Suggestions

- Prefix by sequence when needed: `01_`, `02_`, `03_`
- Use uppercase snake-like style for clarity:
    - `WAREHOUSE_MASTER_GUIDE.md`
    - `CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md`
- Add `_VI` suffix for Vietnamese docs when mixed-language docs coexist.
