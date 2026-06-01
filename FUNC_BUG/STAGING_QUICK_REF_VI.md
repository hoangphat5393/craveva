# Staging — tra cứu nhanh

**Ưu tiên vận hành:** [`docs/SERVER_RUNBOOK_VI.md`](../docs/SERVER_RUNBOOK_VI.md) · [`docs/STAGING_OPERATIONS.md`](../docs/STAGING_OPERATIONS.md)

| Nhu cầu                                         | File                                                                                                         |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| SSH / GCP metadata / `upload_staging.ps1`       | [`STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md`](STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md) |
| Incident cũ (lệnh chi tiết, nginx, PHP upload…) | `docs/SERVER_RUNBOOK_VI.md` — lịch sử chi tiết: `git log -- FUNC_BUG/STAGING_INCIDENTS_ARCHIVE_VI.md`        |
| Bug registry                                    | [`REGISTRY.md`](REGISTRY.md) (OPS-STAGING-\*)                                                                |

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
