# UI conventions (Hub / account screens)

Applies to most list and form screens under `/account/`.

## Lists (DataTable)

- **Filter bar:** top of list; status, date, client, project filters.
- **Primary action:** blue **Add** button (`btn-primary`) — often opens **right modal**, not full page.
- **Row actions:** dropdown **Action** (edit, delete, view).
- **Quick actions:** bulk select + apply — not separate URLs.
- **Export / Import:** secondary buttons when permission allows.

## Forms

- **Create / Edit:** full page or **right modal** (`openRightModal`). Modal loads HTML via AJAX; inline `<script>` in partial may **not** run — logic must live in bundled `public/js/custom.js` or `@push('scripts')` after `custom.js`.
- **Save:** primary button; AJAX submit on many modules → toast + stay on list or close modal.

## Select fields

- Class **`select-picker`** (bootstrap-select).
- In **tables**, use `data-container="body"` so dropdown is not clipped by `overflow:hidden` on `.table-responsive`.

## Validation (two layers)

1. **Client:** required fields, `.is-invalid` on field; single-line Swal toast summary.
2. **Server:** `handleApiFormError` → field `.has-error` + `.help-block`; orphan errors → toast only.

Do not expect jQuery Validate / Parsley on standard Hub forms.

## Status on lists

- Binary status: colored dot + inline toggle (revert on API error).
- Multi-status: badge + optional inline change per design of module.

## Permissions UX

- Missing module or permission → **403** or menu hidden.
- Fix: **Settings → Module Settings** and **Role & Permissions**.

## Troubleshooting UI

See [flows/70-troubleshooting-common.md](../flows/70-troubleshooting-common.md).
