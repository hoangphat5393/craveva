# Firewall Status — `craveva-hub-server` and `craveva-staging-db`

**Generated at:** 2026-03-31  
**Updated at:** 2026-05-05 (staging IP allowlist adjusted)  
**Project:** `craveva-org-55934-project`

---

## 1) `craveva-hub-server` (Compute Engine VM)

### VM identity

- **Name:** `craveva-hub-server`
- **Zone:** `asia-southeast1-a`
- **Status:** `RUNNING`
- **Network:** `craveva-vpc` / subnet `craveva-subnet-singapore`
- **Private IP:** `10.1.0.5`
- **Public IP:** `34.126.124.196`
- **Network tags:** `http-server`, `https-server`, `iap-ssh`, `panel`, `ssh-server`

### OS-level firewall (inside VM)

- **UFW:** `inactive`
- **firewalld:** `inactive`

---

## 2) `craveva-staging-db` (Cloud SQL MySQL)

### Instance identity

- **Name:** `craveva-staging-db`
- **Engine:** `MYSQL_8_0`
- **State:** `RUNNABLE`
- **Public IP:** `136.110.52.19`
- **Private IP:** `10.249.0.12`

### Cloud SQL network controls

- **IPv4 enabled:** `true`
- **Require SSL:** `false`
- **Authorized Networks (highlights):**
    - `35.240.198.61/32` (staging VM current IP)
    - `35.240.234.226/32` (staging VM old IP)
    - `34.126.124.196/32` (hub)
    - `136.110.35.154/32` (ai)

### Security note

- `0.0.0.0/0` is **not present** in Authorized Networks.
- Because public IP access is enabled and `requireSsl=false`, consider enabling SSL requirement and keeping strict IP allowlist.

---

## 3) Commands used

```bash
gcloud compute firewall-rules list --project=craveva-org-55934-project
gcloud sql instances describe craveva-staging-db --project=craveva-org-55934-project
```
