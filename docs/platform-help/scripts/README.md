# Scripts — platform-help maintenance

## Quick: English corpus + all pages

```bash
php docs/platform-help/scripts/convert-to-english.php --regenerate
```

Runs `generate-pages.php --force` (English, in-corpus links) then phrase cleanup on all `.md` files.

## Step by step

```bash
php docs/platform-help/scripts/build-url-index.php
php docs/platform-help/scripts/generate-pages.php --force
php docs/platform-help/scripts/convert-to-english.php
```

## Filter rules (end-user screens)

- GET only (includes `GET|HEAD`)
- Exclude route names containing: `quick_action`, `apply_quick`, `datatable`, `widget`, `export`, `import`, …
- One index row per resource stem (`.index` routes)

## External links policy

Generators and `convert-to-english.php` rewrite or remove links to `FUNC_LOGIC/`, `docs/`, `Modules/`, `SPECIFICATION/`. Use `REFERENCE/` and `flows/` instead.

## RAG (later)

Index only `docs/platform-help/**` — [RAG_SOURCES.md](../RAG_SOURCES.md).
