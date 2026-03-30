# AI.Craveva → MySQL (`136.110.52.19:3306`) — Boss report & engineering follow-up

**Audience:** Leadership / management  
**Language:** English  
**Last updated:** 2026-03-30

---

## 1. Subject / intro (optional — for email or ticket)

**Subject:** MySQL connectivity from AI.Craveva to `136.110.52.19:3306` — intermittent TCP timeout (ETIMEDOUT); need documented allowlist and stability confirmation.

**Intro:**

We are seeing MySQL connectivity to **136.110.52.19:3306** from **AI.Craveva**: it worked briefly after a change, then the next day we saw **TCP timeout again (ETIMEDOUT)**. Please **document what was configured** and **confirm it is stable** (not a temporary rule or single-layer fix).

---

## 2. Executive summary (answers for leadership)

| Topic                                             | What we know                                                                                                                                                                                                                                                                                     |
| ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Which database is `136.110.52.19`?**            | In GCP project `craveva-org-55934-project`, this public IP is **Cloud SQL instance `craveva-staging-db`** (staging MySQL). It is **not** the same instance as **hub.craveva.com** production ERP, which uses **`craveva-hub-server`** with private IP **`10.249.0.4`** (see §5).                 |
| **Likely cause of ETIMEDOUT from “wrong” egress** | Cloud SQL **Authorized networks** only allow listed source IPs. If AI connects from an IP that is **not** allowlisted, **TCP SYN** does not complete → **ETIMEDOUT**.                                                                                                                            |
| **Is `203.210.219.139` allowlisted?**             | **No.** As of **2026-03-30**, `gcloud sql instances describe craveva-staging-db` shows **no** entry for **203.210.219.139/32** in authorized networks. If any workload uses that egress to reach this DB, it will fail at the network layer.                                                     |
| **What about the `craveva-ai` VM?**               | From the **`craveva-ai`** VM, outbound IP was **136.110.35.154** (measured via `api.ipify.org`). That IP **is** allowlisted (**136.110.35.154/32**). **TCP connect to `136.110.52.19:3306` succeeded** from that VM. So **not all** “AI” paths are broken — they differ by **actual egress IP**. |
| **Why “works one day, fails the next”?**          | Common causes: **egress IP changed** (new node, redeploy, NAT), **rule overwritten** (IaC, template), **only one firewall layer** updated, or **VPN/route** flaps. Needs confirmation from infra for the specific path that uses **203.210.219.139**.                                            |

---

## 3. Answers aligned to the seven engineering questions

Below are **internal findings** where we have evidence; items **pending** remain for the platform / infra team.

### 1. What exactly did you change when it “started working”?

**Our status:** **Not documented in this repo.** Requires engineering to list each change (GCP Console → Cloud SQL → **Connections → Authorized networks**, or other firewall layers) with **exact source CIDR, destination, port, protocol, direction**.

**Our finding:** For **`craveva-staging-db`**, authorized networks (snapshot **2026-03-30**) include multiple `/32` entries (e.g. staging VM IPs, **136.110.35.154**, dev/office IPs). **203.210.219.139** is **not** among them.

### 2. Egress / source IP stability (`203.210.219.139`)

**Our status:** The diagnostic **reported** egress **203.210.219.139**. That address is **not** in Cloud SQL authorized networks for **`craveva-staging-db`**.

**Our finding:** The **`craveva-ai`** GCP VM uses a **different** egress (**136.110.35.154**) which **is** allowlisted. So **Craveva / AI is not necessarily a single fixed egress** — it depends which **host** (VM, serverless, external worker) runs the connection.

**Pending:** Confirm whether **203.210.219.139** is still current, which component emits it, and whether it should be allowlisted or traffic should be **pinned** to a known egress.

### 3. Inbound rules on the database side (`136.110.52.19:3306`)

**Our finding:** **Inbound TCP 3306** from **203.210.219.139/32** is **not** confirmed in GCP **Authorized networks** for **`craveva-staging-db`**. **136.110.35.154/32** **is** present.

**Pending:** Confirm whether any **temporary** or **time-bound** rules were used elsewhere; confirm **no** conflicting corporate firewall.

### 4. Persistence / automation

**Our status:** **Not verified** from this repo (no IaC diff for Cloud SQL).

**Pending:** Confirm **Terraform / gcloud** / nightly jobs do **not** overwrite authorized networks; confirm **all** relevant layers (Cloud SQL + any other firewall) stay aligned.

### 5. Routing / VPN

**Our finding:** **`craveva-staging-db`** has **public IP** enabled (`ipv4Enabled: true`) and **private** IP **10.249.0.12** in VPC. **Hub** and other GCP VMs can use **private** connectivity; **internet** clients must match **Authorized networks**.

**Pending:** If any AI component is **not** in GCP and must use **public** IP **136.110.52.19**, that path depends entirely on **allowlist + routing** from that environment.

### 6. DB listener

**Our finding:** Instance is **RUNNABLE** (GCP). **MySQL 8** on **`craveva-staging-db`** (from `gcloud sql instances list`). From **`craveva-ai`**, **TCP to `136.110.52.19:3306` succeeds**.

**Note:** **hub.craveva.com** app server also runs a **local MariaDB** on port 3306; Laravel on hub uses **`DB_HOST=10.249.0.4`** (Cloud SQL private), **not** localhost — do **not** confuse local MariaDB with the Cloud SQL instance used by the ERP app.

### 7. Evidence (same path as production AI)

**Our finding:** Evidence from **`craveva-ai` VM** (egress **136.110.35.154**): **TCP connect OK** to **136.110.52.19:3306**.

**Pending:** Evidence from a host that uses the **same network path as the component that shows egress 203.210.219.139** (or firewall logs showing **SYN** from that IP to **136.110.52.19:3306**).

---

## 4. hub.craveva.com vs `136.110.52.19` (clarification)

| Item                       | hub.craveva.com (production ERP)                             | `136.110.52.19` (this ticket)                |
| -------------------------- | ------------------------------------------------------------ | -------------------------------------------- |
| **GCP Cloud SQL instance** | `craveva-hub-server`                                         | `craveva-staging-db`                         |
| **Typical app connection** | Private IP **`10.249.0.4:3306`** (from `.env` on hub server) | Public **`136.110.52.19:3306`** (staging DB) |
| **Role**                   | Production hub database                                      | **Staging** database                         |

The connectivity issue **reported for AI → 136.110.52.19** concerns **staging** Cloud SQL (`craveva-staging-db`), **not** the hub production DB instance, unless the product explicitly points AI at staging.

---

## 5. Why “works then fails the next day” (context)

| Cause                            | Explanation                                                                   |
| -------------------------------- | ----------------------------------------------------------------------------- |
| Egress IP changed                | After restart, deploy, or new node — yesterday’s allowlist no longer matches. |
| Rule was temporary / overwritten | IaC apply, another admin, or template reapply.                                |
| Only one layer opened            | e.g. Cloud SQL updated but not OS firewall, or vice versa.                    |
| VPN / route                      | Tunnel flap, session expired, or renumbered routes.                           |

---

## 6. Prompt to paste to engineering (full checklist)

Please review and reply with **concrete answers** (screenshots or CLI output are fine) for our **DB allowlist / network path** from **AI.Craveva** to our MySQL host.

1. **What exactly did you change when it “started working”?**  
   For each item, note **where** (cloud console, firewall appliance, iptables/firewalld, security group, NACL, VPN, etc.) and the **exact rule** (source IP/CIDR, destination, port, protocol, direction).

2. **Egress / source IP stability**  
   The diagnostic reported platform egress **203.210.219.139**.  
   Is Craveva connecting from a **single fixed egress IP** or can it change (new VM, autoscale, redeploy, NAT gateway change, ISP change)?  
   If you allowlisted one IP, confirm whether our app’s outbound IP is **still that same address today** (re-run the same connectivity diagnostic or check what source IP hits your firewall logs).

3. **Inbound rules on the database side**  
   For the path to **136.110.52.19:3306**:  
   Confirm inbound **TCP 3306** is allowed from **203.210.219.139/32** (or the correct documented Craveva range), not only from an old IP or a temporary test IP.  
   Confirm **nothing time-bound or temporary** was used (expiring rules, “test” SG, maintenance window only).

4. **Persistence**  
   Were any changes **reverted by automation** (IaC drift, nightly reset, template reapply)?  
   Are there **multiple firewalls** (cloud SG + OS firewall + corporate firewall) and was **only one** updated?

5. **Routing / VPN**  
   Is the DB reachable **only over VPN or private link**? If yes, confirm the Craveva environment **still uses that route** and the tunnel wasn’t down or renumbered.

6. **DB listener**  
   Confirm MySQL is still listening on **3306** on that host and the service wasn’t restarted with a **different bind address**.

7. **Evidence after your checks**  
   Please run from a host that uses the **same network path as production Craveva** (or share firewall logs showing **SYN** from our egress IP hitting **136.110.52.19:3306**) and state whether **TCP connect succeeds or times out now**.

---

## 7. Reference commands (for reproducibility)

```bash
# Authorized networks for staging DB (public IP 136.110.52.19)
gcloud sql instances describe craveva-staging-db \
  --project=craveva-org-55934-project \
  --format="yaml(settings.ipConfiguration.authorizedNetworks,ipAddresses)"

# From craveva-ai VM: egress + TCP test
curl -s https://api.ipify.org
timeout 4 bash -c 'echo >/dev/tcp/136.110.52.19/3306' && echo OK || echo FAIL
```

---

_This document is maintained in the repo for reporting; GCP state may change — re-run `gcloud` before critical decisions._
