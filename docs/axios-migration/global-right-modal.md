# Global: `.openRightModal` (right panel)

## Status

- [x] Completed — uses `window.apiHttp.get` instead of `$.easyAjax`

## Scope

- `resources/js/custom.js` — click handler for `.openRightModal` (loads AJAX HTML into `#right-modal-content`).

## Behavior (unchanged)

| Concern        | Implementation                                                                                   |
| -------------- | ------------------------------------------------------------------------------------------------ |
| Request        | `GET` modal URL + optional `redirectUrl` query (same as legacy jQuery data).                     |
| History        | `historyPush(requestUrl)` when not `.inModal` (global from `helper.js`).                         |
| Block UI       | `$.easyBlockUI(RIGHT_MODAL)` / `$.easyUnblockUI` in `finally`.                                   |
| Success        | `response.status === 'success'` → inject `html` + `title`.                                       |
| HTTP errors    | 401 → full page reload (align with `helper.js` `ajaxError`); 403 / 404 / 500 → message in panel. |
| Missing client | If `window.apiHttp` absent, show short error (layout should load `main.js` before `custom.js`).  |

## Changes log

- 2026-04-05 — Replaced `$.easyAjax` with `apiHttp.get` per `docs/axios-migration/README.md`.
