# Staging — tra cứu nhanh

**Ưu tiên vận hành:** [`docs/SERVER_RUNBOOK.md`](../docs/SERVER_RUNBOOK.md) · [`docs/STAGING_OPERATIONS.md`](../docs/STAGING_OPERATIONS.md)

| Nhu cầu                                         | File                                                                                                         |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| SSH / GCP metadata / `upload_staging.ps1`       | [`BUG_STAGING_SSH.md`](BUG_STAGING_SSH.md) |
| Incident cũ (lệnh chi tiết, nginx, PHP upload…) | `docs/SERVER_RUNBOOK.md` — lịch sử chi tiết: `git log -- FUNC_BUG/STAGING_INCIDENTS_ARCHIVE.md`        |
| Bug registry                                    | [`SO_LOI.md`](SO_LOI.md) (OPS-STAGING-\*)                                                                |

## Checklist nhanh (trên server)

```bash
# Quyền upload import
ls -la public/user-uploads/temp public/user-uploads/import-files

# PHP-FPM / nginx
sudo systemctl status nginx php8.3-fpm

# Queue worker (nếu import/job treo)
sudo systemctl status craveva-staging-worker   # tên service theo runbook
```

## Deploy từ Windows

```powershell
.\scripts\upload_staging.ps1    # staging
.\scripts\upload_hub.ps1        # hub
.\scripts\ssh_staging.ps1       # SSH qua gcloud
```

_Không nhân bản runbook dài trong FUNC_BUG — incident cũ đã retire (pass 6); tra `git log` nếu cần._
