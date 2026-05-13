# AI.Craveva → MySQL (`136.110.52.19:3306`) — Engineering questionnaire

**Ghi chú:** bản “boss report” tách riêng không còn trong repo; dùng file này làm bản đầy đủ checklist + ngữ cảnh hub/staging.

---

## Subject (email / ticket)

MySQL connectivity from AI.Craveva: worked briefly after your change, then TCP timeout (ETIMEDOUT) the next day — please document what you configured and confirm stability.

---

## Intro (optional)

We’re seeing MySQL connectivity to **136.110.52.19:3306** from **AI.Craveva**: it worked briefly after your change, then the next day we got **TCP timeout again (ETIMEDOUT)**. Please **document what you configured** and **confirm it’s stable** (not a temporary rule or single-layer fix).

---

## Prompt for engineering — please reply with concrete answers

Screenshots or CLI output are fine. We need clarity on the **DB allowlist / network path** from AI.Craveva’s side to our MySQL host.

1. **What exactly did you change when it “started working”?**  
   For each item, note **where** (cloud console, firewall appliance, iptables/firewalld, security group, NACL, VPN, etc.) and the **exact rule** (source IP/CIDR, destination, port, protocol, direction).

2. **Egress / source IP stability** — diagnostic reported **203.210.219.139**.  
   Is Craveva connecting from a **single fixed egress IP** or can it change? If you allowlisted one IP, confirm whether our app’s outbound IP is **still that same address today**.

3. **Inbound rules on the database side** for **136.110.52.19:3306** — confirm **TCP 3306** from **203.210.219.139/32** (or correct range), not only old/temporary IPs; confirm **nothing time-bound or temporary**.

4. **Persistence** — IaC drift, nightly reset? **Multiple firewalls** — was only one updated?

5. **Routing / VPN** — DB only over VPN/private link? Tunnel stable?

6. **DB listener** — MySQL still on **3306**, bind address unchanged?

7. **Evidence** — from same network path as production Craveva (or firewall logs SYN from egress to **136.110.52.19:3306**); **connect OK or timeout?**

---

## Context — why “works then fails the next day”

| Cause                        | Notes                           |
| ---------------------------- | ------------------------------- |
| Egress IP changed            | Allowlist no longer matches.    |
| Rule temporary / overwritten | IaC or another admin.           |
| Only one layer opened        | e.g. SG but not OS firewall.    |
| VPN / route                  | Tunnel flap or expired session. |

---

See các mục checklist trên và runbook `docs/SERVER_RUNBOOK_VI.md` cho xác minh GCP / allowlist.
