# SPECIFICATION — Index

Tài liệu đặc tả, audit luồng, và snapshot hạ tầng (không thay cho `docs/` runbook).

## System & product

| Nội dung                  | File                                                                       |
| ------------------------- | -------------------------------------------------------------------------- |
| Spec runtime / stack (EN) | [`CRAVEVA_SYSTEM_SPECIFICATION_EN.md`](CRAVEVA_SYSTEM_SPECIFICATION_EN.md) |

## Application behaviour

| Nội dung                        | File                                                   |
| ------------------------------- | ------------------------------------------------------ |
| Menu, route cache, `Route::has` | [`MENU_ROUTES_AND_CACHE.md`](MENU_ROUTES_AND_CACHE.md) |
| Luồng đăng ký (signup)          | [`SIGN_UP_FLOW_AUDIT.md`](SIGN_UP_FLOW_AUDIT.md)       |

## Infrastructure snapshots

| Nội dung                                               | File                                                                                                                 |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------- |
| Staging & Hub — RAM, FPM, Redis, disk (SSH snapshot)   | [`STAGING_HUB_SERVER_INFO_2026-04-06.md`](STAGING_HUB_SERVER_INFO_2026-04-06.md)                                     |
| GCP VMs + Cloud SQL + allowlist hub/staging/test (gộp) | [`GCP_AND_CLOUDSQL_SNAPSHOT.md`](GCP_AND_CLOUDSQL_SNAPSHOT.md) — gồm `craveva-staging-test` (clone 2026-05-23) |
