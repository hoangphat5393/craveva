# SERVICE_ARCHITECTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Notes

- This repo mixes controller-driven logic with services in some modules. Prioritize service extraction for inventory/finance workflows.
- Standardize: Request validation → domain service → repository/unit-of-work → events.
