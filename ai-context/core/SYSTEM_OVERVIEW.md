# SYSTEM_OVERVIEW

- Generated at: 2026-05-04T05:35:06+00:00
- Stack: PHP ^8.3, Laravel ^11.0, nwidart/laravel-modules ^11.0
- Modules discovered: 25

## Entry routes

- routes/web.php (250 routes extracted)
- routes/web-settings.php (133 routes extracted)
- routes/web-public.php (81 routes extracted)
- routes/api.php (1 routes extracted)
- routes/channels.php (0 routes extracted)
- routes/SuperAdmin/web.php (144 routes extracted)
- routes/SuperAdmin/web-public.php (12 routes extracted)

## Scan coverage (counts)

- app/Http/Controllers: total=254, php=254, blade=0, js=0
- app/Models: total=294, php=294, blade=0, js=0
- database/migrations: total=385, php=385, blade=0, js=0
- resources/views: total=1336, php=1328, blade=1327, js=0
- resources/js: total=7, php=0, blade=0, js=7
- public/js: total=11, php=0, blade=0, js=6
- public/css: total=6, php=0, blade=0, js=0

## Risk signals (heuristics)

- mass_assignment: 80 files
- raw_sql: 80 files
- file_ops: 14 files
- api_keys: 71 files
- command_exec: 4 files
- crypto_decrypt: 4 files

## References

- MASTER_DOCUMENTATION.md
- FUNC_BUG/ (bug notes)
- FUNC_LOGIC/ (flow notes)
