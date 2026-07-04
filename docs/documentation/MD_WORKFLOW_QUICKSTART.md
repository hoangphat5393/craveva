# Markdown Workflow Quickstart

Simple workflow to keep markdown docs organized and low-token for AI work.

## 1) Check current documentation sync

```powershell
.\scripts\md_master_sync.ps1
```

What you should watch:

- `Missing from group INDEX` should be low (ideally `0`)
- `Missing from both` should be `0`

## 2) Auto-fix missing links in INDEX files

```powershell
.\scripts\md_master_sync.ps1 -Fix
```

This only updates `FUNC_*/INDEX.md` with missing links.

## 3) Auto-add heuristic links to master guides

```powershell
.\scripts\md_master_sync.ps1 -FixMaster
```

This appends links into `*MASTER_GUIDE*.md` under auto-added section.

## 4) Get low-token shortlist by topic (before asking AI)

```powershell
.\scripts\md_master_sync.ps1 -Topic "warehouse inventory stock" -Limit 8
```

Use this output to avoid sending too many files to AI.

## 5) One-command AI context helper

```powershell
.\scripts\md_ai_context.ps1 -Topic "maolin import pricing" -Limit 6
```

This prints:

- matched files
- suggested master guide
- a copy-paste prompt block for AI

Prompt-only mode:

```powershell
.\scripts\md_ai_context.ps1 -Topic "warehouse stock transfer" -AsPromptOnly
```

## 6) Optional JSON output (for automation)

```powershell
.\scripts\md_master_sync.ps1 -AsJson -Topic "client import" -Limit 6
```

## Notes

- Rules file: `scripts/md_master_sync.rules.json`
- Update rules when naming patterns evolve.
- Keep `FUNC_INDEX.md` and `FUNC_*/INDEX.md` as navigation-only docs.
