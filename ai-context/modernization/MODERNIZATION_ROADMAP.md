# MODERNIZATION_ROADMAP

- Generated at: 2026-05-04T05:35:06+00:00

## Phase 0 (no behavior change)

- Generate and maintain /ai-context (this folder).
- Add lightweight static checks (Larastan, Pint) to CI if not present.
- Define module boundaries and allowed cross-module dependencies.

## Phase 1 (safety & correctness)

- Inventory: centralize stock movement posting + idempotency keys.
- Finance: standardize invoice/payment posting and reconciliation.
- Harden secrets encryption flows (APP_KEY mismatch recovery playbook).

## Phase 2 (maintainability)

- Extract services from fat controllers for high-risk flows.
- Introduce status enums/state machines for major workflows.
- Replace jQuery ajax patterns with a unified API client where appropriate.

