# FUNC_TEST Index

Navigation index for test strategy, test cases, and UAT execution evidence.

## Test Documents

- `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md` — ma trận UAT Bio-TC-001…021 + §7 lệnh Pest cụm Biomixing (lần chạy local **2026-05-07**: 40 passed / 8 file). **§4 MCP Browser (cùng ngày):** BIO-TC-002 / 003 / 013 ghi Pass; BIO-TC-020 Partial/Blocked (E2E chưa đủ).

## Usage

- Use this folder as the single source for all test case documents.
- Add new files with numeric prefixes: `02_...`, `03_...`
- Keep links updated in `FUNC_INDEX.md`.

## Status Convention

- `draft`: under preparation
- `ready`: approved for execution
- `executing`: in-progress testing
- `passed`: execution complete, no blockers
- `blocked`: execution blocked by defects/env
