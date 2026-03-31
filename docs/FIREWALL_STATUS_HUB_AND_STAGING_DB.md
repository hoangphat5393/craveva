# Firewall Status — `craveva-hub-server` and `craveva-staging-db`

**Generated at:** 2026-03-31  
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
- **iptables (INPUT):**
    - Default policy: `ACCEPT`
    - Has aaPanel ipset hooks:
        - `DROP ... match-set aapanel.ipv4.blacklist src`
        - `ACCEPT ... match-set aapanel.ipv4.whitelist src`

### GCP VPC firewall (project-level rules relevant to hub)

Observed broad rules in project include:

- `allow-all-tcp-temp` — ingress TCP `1-65535` from `0.0.0.0/0`
- `craveva-vpc-allow-all-tcp` — ingress TCP `1-65535` from `0.0.0.0/0`
- `craveva-allow-http` / `default-allow-http` — TCP `80` from `0.0.0.0/0` (tag `http-server`)
- `craveva-allow-https` / `default-allow-https` — TCP `443` from `0.0.0.0/0` (tag `https-server`)
- `craveva-allow-ssh` / `default-allow-ssh` / `allow-ssh*` — TCP `22` from `0.0.0.0/0` (some rules tag-based)
- `allow-iap-ssh` — TCP `22` from `35.235.240.0/20` (tag `iap-ssh`)
- `allow-panel-11280-craveva` — TCP `11280` from `0.0.0.0/0` (tag `panel`)

> Note: some rules are very permissive (`0.0.0.0/0` and full TCP ranges). Review and tighten if not required.

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
- **Authorized Networks** (current):
    - `136.110.35.154/32`
    - `35.240.153.233/32`
    - `34.126.124.196/32`
    - `116.108.126.47/32`
    - `35.240.234.226/32`
    - `35.240.158.191/32`
    - `123.20.159.147/32`
    - `14.224.214.181/32`
    - `116.102.45.168/32`
    - `35.198.237.131/32`

### Security note

- `0.0.0.0/0` is **not present** now in Authorized Networks.
- Because public IP access is enabled and `requireSsl=false`, consider enabling SSL requirement and keeping a strict IP allowlist.

---

## 3) Commands used

```bash
gcloud compute instances describe craveva-hub-server --project=craveva-org-55934-project --zone=asia-southeast1-a
gcloud compute firewall-rules list --project=craveva-org-55934-project
gcloud sql instances describe craveva-staging-db --project=craveva-org-55934-project
ssh craveva-hub-server "sudo ufw status; sudo systemctl is-active firewalld; sudo iptables -L INPUT -n -v"
```
