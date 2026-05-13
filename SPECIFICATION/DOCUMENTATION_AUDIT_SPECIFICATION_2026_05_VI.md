# SPECIFICATION — Audit & gộp (2026-05-12)

## 1) Bối cảnh

Thư mục `SPECIFICATION/` gồm spec hệ thống, audit luồng, và snapshot hạ tầng. Trước đợt này có **6** file markdown.

## 2) Thay đổi

| Việc                             | Chi tiết                                                                                                                                                                                         |
| -------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Gộp snapshot GCP + Cloud SQL** | `GCP_RESOURCE_INVENTORY_2026-04-06.md` + `CLOUDSQL_HUB_STAGING_FIREWALL_SETTINGS.md` → **`GCP_AND_CLOUDSQL_SNAPSHOT_VI.md`** (Phần A: inventory gcloud; Phần B: allowlist chi tiết hub/staging). |
| **Mục lục**                      | Thêm **`INDEX.md`**.                                                                                                                                                                             |
| **Sửa link ngoài thư mục**       | `docs/SERVER_RUNBOOK_VI.md` trỏ nhầm `STAGING_AND_HUB_SERVER_INVENTORY_2026-04-03.md` (không tồn tại) → **`SPECIFICATION/STAGING_HUB_SERVER_INFO_2026-04-06.md`**.                               |
| **Tham chiếu `deploy/`**         | Repo **xóa** `deploy/`; `STAGING_HUB_SERVER_INFO_2026-04-06.md` trỏ mẫu Supervisor → **`docs/SERVER_RUNBOOK_VI.md` mục 10.4** (2026-05-12).                                                      |

## 3) Giữ nguyên (chủ đề tách bạch)

- `CRAVEVA_SYSTEM_SPECIFICATION_EN.md` — spec runtime/build tổng quan (EN).
- `MENU_ROUTES_AND_CACHE.md` — menu, `Route::has`, cache route module.
- `SIGN_UP_FLOW_AUDIT.md` — luồng đăng ký / Super Admin.
- `STAGING_HUB_SERVER_INFO_2026-04-06.md` — inventory SSH (RAM, FPM, Redis, …); bổ sung cho bảng IP trong Phần A của file GCP (đối chiếu, không gộp nội dung dài).

## 4) Regenerate / cập nhật snapshot

Khi đổi VM hoặc allowlist, cập nhật **`GCP_AND_CLOUDSQL_SNAPSHOT_VI.md`** (hoặc tách lại file riêng nếu team muốn diff nhỏ hơn) và ghi ngày ở đầu Phần A/B.
