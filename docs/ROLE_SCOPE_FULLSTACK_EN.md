# Role scope — Fullstack ERP + B2B (English)

## 1. Fullstack Development

- Develop frontend and backend for the ERP + B2B system
- Build APIs and implement business logic for modules (client, product, inventory)
- Work with the database at the level needed for feature development (CRUD, queries, validation)

## 2. System Development & Enhancement

- Take over the project and implement new features as required
- Analyse customer requirements and turn them into system capabilities
- Extend and refine ERP modules

## 3. Data Processing

- Implement bulk import (~20,000 rows) for client, product, and inventory modules
- Validate data and preserve data integrity

## 4. Integration (AI & Chatbot)

- Integrate AI via API to retrieve data from the ERP system
- Set up webhooks and connect LINE and WhatsApp
- Implement the flow: Chat → Webhook → AI → DB → Response

## 5. System Operation (Takeover)

- Operate staging and production environments
- Configure Git for staging/live workflows
- Work with GCP at an operational level (deploy, checks, basic troubleshooting)

## 6. Testing & Debug

- Perform testing and functional verification
- Debug issues related to APIs, the database, and integrations

## 7. Current Focus

- Resolving cases where AI responses do not match DB data
- Focusing on:
    - Normalising API response payloads
    - Improving data mapping for AI
    - Verifying how AI interprets and uses the data

---

## Role mapping (Fullstack vs other roles)

1. **Can do.**

| Task                    | Fullstack | Typical role           |
| ----------------------- | --------- | ---------------------- |
| FE + BE for ERP         | Yes       | Fullstack Developer    |
| APIs & business logic   | Yes       | Backend / Fullstack    |
| DB work (CRUD, queries) | Yes       | Fullstack              |
| Basic DB design         | Yes       | Fullstack (data-aware) |

2. **Need support from Business / System Analyst.**

| Task                          | Fullstack | Typical role              |
| ----------------------------- | --------- | ------------------------- |
| Analyse customer requirements | Partial   | Business / System Analyst |
| Propose features              | Partial   | Solution Engineer         |
| System flow design            | Partial   | Solution / System Analyst |

3. **Can do but not full optimize.**

| Task                   | Fullstack     | Typical role                     |
| ---------------------- | ------------- | -------------------------------- |
| Import ~20k rows       | Yes / Partial | Senior Fullstack / Data Engineer |
| Large-scale validation | Partial       | Data Engineer                    |

4. **Can only Test basic, system debugging, cannot do In-depth Business Testing.**

| Task             | Fullstack | Typical role   |
| ---------------- | --------- | -------------- |
| Testing          | Partial   | QA / Fullstack |
| System debugging | Yes       | Fullstack      |

5. **Almost zero knowledge.**

| Task                            | Fullstack | Typical role             |
| ------------------------------- | --------- | ------------------------ |
| GCP work                        | No\*      | DevOps / Cloud Engineer  |
| Staging / production operations | No\*      | DevOps                   |
| Git / environment workflow      | Partial   | DevOps / light Fullstack |
| Deployments                     | No\*      | DevOps                   |
| Optimized DB                    | No        | DB Engineer              |
