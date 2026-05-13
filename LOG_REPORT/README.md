# Backend Lines-of-Code Report

Thư mục: **`LOG_REPORT`** (đổi tên từ `LOC_REPORT`, 2026-05-12 — nội dung vẫn là báo cáo số dòng backend, không phải “application log” runtime).

Generated: 2026-05-07

## Scope

Counted: every `*.php` file under
`app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `Modules/`, `lang/`.

Excluded:

- Any path inside a `tests/`, `Tests/`, `test/`, `Test/` segment.
- Blade templates (`*.blade.php`) — those are views, not backend logic.
- `vendor/`, `node_modules/`, `docs/`, `FUNC_*`, `SPECIFICATION/`,
  `DIAGRAM/`, `backup/`, `graphify-out/`, all `*.md` files.

Line count = raw line count (blank + comment lines included).

## i18n locale-merging

Three locations contain locale-suffixed translation arrays. In all
"merged" views below, the locale path segment is replaced with
`<locale>` and rows for the same filename across all locales are summed:

| Location                            | Pattern                                                                |     Files |       Lines |
| ----------------------------------- | ---------------------------------------------------------------------- | --------: | ----------: |
| Top-level Laravel lang dir          | `lang/<locale>/<file>.php`                                             |       471 |     184,301 |
| Per-module translations             | `Modules/<X>/Resources/lang/<locale>/<file>.php`                       |     1,380 |      74,280 |
| LanguagePack published translations | `Modules/LanguagePack/Languages/{app,modules/<X>}/<locale>/<file>.php` |     1,975 |     263,122 |
| **Total i18n**                      |                                                                        | **3,826** | **521,703** |

The remaining ~363K lines is the real backend logic.

## Totals

| Metric                                                 |     Files |       Lines |
| ------------------------------------------------------ | --------: | ----------: |
| Backend PHP (full, including i18n)                     | **7,684** | **885,275** |
| Backend PHP excluding **all** i18n / translation files | **3,858** | **363,572** |

## File index

Each `.txt` file in this folder begins with an 8-line `#` header that
restates the scope, totals, i18n rules, and what view it represents.

| File                                        | View                                                                                                                                   |  Rows |
| ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- | ----: |
| `backend_loc_per_file.txt` / `.csv`         | **Recommended.** Every backend PHP file individually, with i18n files merged per filename across all locales (`<locale>` placeholder). | 4,184 |
| `backend_loc_per_file_no_i18n.txt` / `.csv` | All translation files excluded — best for ranking real code files / refactor targets.                                                  | 3,858 |
| `backend_loc_per_module.txt` / `.csv`       | Rollup by top-level dir / each `Modules/*`.                                                                                            |    32 |
| `_tinykeys.txt`                             | Auxiliary keys list (legacy tooling).                                                                                                  |   —   |

All files are sorted by line count descending.

**Gọn thư mục (2026-05-12):** đã xóa các bản copy trùng dữ liệu (`backend_loc_per_file_full*`, `backend_loc_per_file_lp_by_filename*`, `backend_loc_per_file_no_languagepack*`). Chi tiết: `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md` · mục lục: `LOG_REPORT/INDEX.md`.

## Per-module rollup

| Area                    | Files |   Lines |
| ----------------------- | ----: | ------: |
| Modules/LanguagePack    | 2,049 | 264,640 |
| app/                    | 1,709 | 190,683 |
| lang/                   |   471 | 184,301 |
| Modules/Recruit         |   359 |  43,145 |
| Modules/Purchase        |   343 |  32,323 |
| database/               |   445 |  31,858 |
| Modules/Payroll         |   246 |  18,583 |
| Modules/Sms             |   233 |  16,622 |
| Modules/ServerManager   |    98 |  11,817 |
| Modules/Performance     |   110 |  10,074 |
| Modules/Biolinks        |   225 |   9,645 |
| Modules/Zoom            |   157 |   8,031 |
| Modules/Warehouse       |    63 |   5,887 |
| config/                 |    48 |   5,236 |
| Modules/QRCode          |    54 |   4,752 |
| Modules/Asset           |   134 |   4,735 |
| Modules/Webhooks        |   192 |   4,249 |
| Modules/Production      |    43 |   4,142 |
| Modules/Onboarding      |    51 |   3,981 |
| Modules/CyberSecurity   |   153 |   3,777 |
| Modules/Policy          |    51 |   3,711 |
| Modules/Pricing         |    47 |   3,489 |
| Modules/Affiliate       |    64 |   3,058 |
| Modules/Biometric       |    40 |   2,972 |
| Modules/DeveloperTools  |    26 |   2,210 |
| Modules/ProjectRoadmap  |    44 |   2,125 |
| Modules/EInvoice        |    97 |   2,105 |
| Modules/Letter          |    32 |   1,914 |
| routes/                 |     7 |   1,814 |
| Modules/Subdomain       |    57 |   1,688 |
| bootstrap/              |    29 |   1,410 |
| Modules/LineIntegration |     7 |     298 |

## Top 10 actual code files (excluding i18n)

| File                                                                 | Lines |
| -------------------------------------------------------------------- | ----: |
| `database/migrations/2018_01_01_000000_create_craveva_new_table.php` | 3,010 |
| `app/Http/Controllers/ProjectController.php`                         | 2,329 |
| `app/Http/Controllers/AttendanceController.php`                      | 2,173 |
| `app/Models/Module.php`                                              | 2,165 |
| `app/Http/Controllers/InvoiceController.php`                         | 1,857 |
| `app/Helper/start.php`                                               | 1,605 |
| `Modules/Payroll/Http/Controllers/PayrollController.php`             | 1,529 |
| `app/Http/Controllers/HomeController.php`                            | 1,345 |
| `app/Http/Controllers/TaskController.php`                            | 1,319 |
| `app/Http/Controllers/EmployeeController.php`                        | 1,298 |

The controllers in the 1.2K–2.3K range are reasonable refactor candidates
when you have time.

## How this was produced

Raw line count via `[System.IO.File]::ReadAllLines($f).Length` over a
recursive PHP scan. To regenerate after code changes:

1. Re-run the scan over `app/, bootstrap/, config/, database/, routes/, Modules/, lang/`.
2. Apply the same exclusions (tests, blade, doc folders).
3. Apply the i18n merge regex:
    ```
    ^(lang/|Modules/[^/]+/Resources/lang/|Modules/LanguagePack/Languages/app/|Modules/LanguagePack/Languages/modules/[^/]+/)([^/]+)/
    ```
    Replace the second capture group with `<locale>` and sum lines/files
    per merged path.
4. Emit the views above plus the per-module rollup.
