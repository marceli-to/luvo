# Estimate

Scope: **absolute minimum**. Get onto Laravel 13 and Vue 3 with identical
behaviour. Nothing else.

## Explicitly in scope

- Laravel 11 → 13, PHP 8.1 → 8.3+, Carbon 2 → 3
- Legacy L8 skeleton → slim L11+ skeleton (`bootstrap/app.php`)
- Vue 2.7 → Vue 3, vue-router 3 → 4, drop Vuex
- Laravel Mix → Vite
- Replace every Vue 2-only package

## Explicitly OUT of scope

- Composition API / `<script setup>` rewrite — keep Options API + mixins
- Test suite (there is none; QA stays manual click-through)
- Any design or UX change
- The **public site** frontend: plain JS + jQuery + Bootstrap 4, no Vue. Untouched.
- The 8.7k LOC of Sass. Untouched apart from the Vite entry points.

## Numbers

| Phase | Days |
|---|---|
| Backend L11 → L13 | **2 – 2.5** |
| Frontend Vue 3 + Vite | **5 – 6** |
| **Total (my working time)** | **7 – 9** |

Add client-side review and click-through QA → **~2 weeks calendar** if reviewed
as we go.

### Backend breakdown

| Task | Days |
|---|---|
| Dependency bump + conflict resolution + retag `marceli-to/image-cache` | 0.5 |
| Skeleton migration (Kernel → `bootstrap/app.php`, providers, exception handler) | 0.5 |
| PHP 8.3, Carbon 3, `config/` diffs, multilingual-routes v4 → v6 | 0.5 – 1 |
| Smoke test every route + CRUD path | 0.5 |

### Frontend breakdown

| Task | Days |
|---|---|
| Vite setup, `@vite` in blade, aliases, sass, web bundle | 0.5 |
| `app.js` bootstrap rewrite, router 4, drop vuex/interceptors/cleave/moment | 0.5 |
| Icon swap across 24 files | 0.25 |
| **Dropzone replacement** (2 components + upload flow) | 0.5 – 1 |
| `vuedraggable` 2 → 4 across 9 files | 0.5 |
| TinyMCE 3 → 6 | 0.25 |
| Cropper + the 6 near-duplicate `images/Edit.vue` | 0.5 – 1 |
| Remaining ~40 components: `.sync`, filters, emits, mechanical fixes | 1 |
| Click-through QA of every admin screen | 1 |

## What could move these numbers

1. **PHP 8.3+ on hosting.** Hard requirement for L13. If the server is stuck
   on 8.1, nothing else matters. Check first.
2. **Zero test coverage.** Every verification is manual. Baked into the estimate
   — it's why the QA day isn't compressible.
3. **Dropzone.** Only piece with no migration path. Upload flow gets rewritten
   and needs real testing against the image/file endpoints.
