# Rewrite notes — Laravel 11→13 + Vue 2→3

Survey done **2026-09-17** against commit `b2cbcf7` (branch `master`, clean tree).
Written so the next session can skip re-deriving all of this.

## Files here

| File | Contents |
|---|---|
| `00-estimate.md` | The headline numbers and what "minimum scope" means |
| `01-inventory.md` | What's actually in the codebase, measured |
| `02-backend-laravel13.md` | Dependency audit, blockers, step plan |
| `03-frontend-vue3.md` | Package-by-package migration table, step plan |
| `04-open-questions.md` | What must be answered before starting |

## The 30-second version

- **7–9 working days** of focused work, ~2 weeks calendar with review.
  Backend 2–2.5 days, frontend 5–6 days.
- Backend is in **better shape than it looks**: every third-party dep already
  has a Laravel 13 release. Only blocker is your own `marceli-to/image-cache`.
- Frontend Vue code is **unusually clean** (no `$listeners`, no event bus, no
  `slot-scope`). The cost is in replacing dead Vue 2 packages, not in your code.
- Biggest single risk: **`vue2-dropzone` has no Vue 3 port** — full rewrite.
- Hard gate: **Laravel 13 needs PHP 8.3+**. Verify hosting before anything else.

## Ground rule for "minimum"

Stay on Options API. Keep the mixins. No test suite. No design changes.
Do **not** rewrite into `<script setup>` / Composition API while in there —
it roughly doubles the frontend number and buys nothing functional.
