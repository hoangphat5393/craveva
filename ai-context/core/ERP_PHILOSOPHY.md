# ERP_PHILOSOPHY

- Module-first architecture: domain logic should live inside Modules/*; keep cross-module coupling explicit.
- Workflow consistency: sales → delivery → invoice → payment should be traceable and idempotent.
- Inventory consistency: stock movements should be canonical, reversible, and auditable.
- Maintainability: reduce fat controllers; push business logic into services/domain classes.
- Scalability: remove N+1 patterns; enforce eager loading; use queues for heavy tasks.
