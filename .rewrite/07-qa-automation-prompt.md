# Automate the rework QA checklist

You are working in the luvo.ch repo (Laravel 13, PHP 8.3+, admin SPA in Vue 3 + Vite, public site jQuery + Vite, images via Glide, editor Tiptap). The upgrade is done. There is no real test suite yet. Your job is to automate as much of the manual QA checklist as possible, so it can run again before and after every deploy.

## Read first

1. `.rewrite/README.md`, `05-image-pipeline.md`, `06-progress.md`: what changed, what was fixed, known behaviour.
2. `.rewrite/qa-checklist.json`: the checklist, 135 items, each with a stable `id`. **Every automated test must be linked to one or more of these ids.**
3. `routes/web.php`, `routes/api.php`, `app/Http/Requests/*`, `resources/js/dashboard/**` (router, views, composables, `components/ui/editor/*`, `components/images/ImageManager.vue`, `components/ui/Uploader.vue`).

## Phase 1: plan, then stop

Write `.rewrite/08-test-plan.md` and **stop for my review** before writing any test code. It must contain:

- A table of all 135 checklist ids with the chosen layer for each:
  - `feature`: PHPUnit feature test against the Laravel app (routes, auth, API, validation, uploads, image route).
  - `e2e`: Playwright test in a real browser against a locally running app.
  - `visual`: Playwright screenshot / pixel comparison against production (read-only).
  - `manual`: cannot be automated sensibly (say why in a few words, e.g. real iOS Safari, deploy on the server, paste from real Word).
- The test-data strategy (see constraints below), the test-user setup, and how uploaded files are cleaned up.
- Any checklist item that looks wrong or untestable as written.
- New dependencies you want to add, with one line each on why.

## Phase 2: build (after I approve the plan)

### Setup

- Work on a new branch `test/qa-automation`, branched from the current branch. Small, focused commits.
- **Backend:** use the existing PHPUnit setup (`phpunit.xml`). Delete the two example stubs. Use a dedicated MySQL database `luvo_test`; never `luvo`. Check first whether the migrations still run cleanly from scratch; if not, load a schema dump of the local DB instead and say so in the plan.
- **E2E:** `@playwright/test` with the Chromium already on the machine (no `playwright install` of extra browsers unless I agree). Tests in `tests/e2e/`. Add projects for desktop Chromium (1280×800), a tall desktop (1280×1000, for the member-image fix) and a phone viewport (375×812). Add WebKit only if it is already installed.
- Run the app for E2E with `php artisan serve --host=127.0.0.1 --port=8010` using an `.env.e2e` (gitignored, plus a committed `.env.e2e.example`): `APP_ENV=e2e`, DB `luvo_e2e` (a clone of the local `luvo` DB, which is a production copy), `APP_URL` and `SANCTUM_STATEFUL_DOMAINS` set to `127.0.0.1:8010`, `SESSION_SECURE_COOKIE=false`, mail to the log driver. Playwright's `webServer` starts it. Provide a script that (re)creates `luvo_e2e` from `luvo`.
- Scripts: `composer test` → `php artisan test`; `npm run test:e2e`; `npm run test:visual`; `npm run test:all`.

### Test users

Create them in a global setup (seeder or artisan command, only for `e2e`/`testing` envs): a verified admin, a verified non-admin, an unverified admin. Credentials come from env, not hard-coded.

### Shared E2E fixture (applies to every test)

- Fail the test on any `console.error`, uncaught page error, or any response with status ≥ 400. Allow exceptions per test only when the test expects them (404 page, 401/422 checks).
- Tag each test with its checklist ids: `test.info().annotations.push({ type: 'qa', description: 'pg-home-urls' })`.

### What to cover (guidance; the plan decides the details)

- **Public site:** every page type in de / fr / en, localized slugs, `<html lang>`, language switcher keeps the page, desktop + mobile menu, member menu shows only published members in admin order, swipers (desktop vertical, mobile horizontal), simplebar, lazysizes, SEO title / description, 404 status.
- **Images:** collect every image URL each public page emits (all `<source>` and `<img>`). Assert 200, correct `content-type` (avif / webp / jpeg), and that pixel dimensions match the requested slot rather than 2400 px. Assert the size / format whitelist rejects other values. Check `/img/original` and `/img/thumbnail`. On the tall project, the member image loads.
- **Auth & access:** login success / failure, guest redirect, `api/*` 401 JSON, non-admin 403, unverified blocked, register and password reset disabled, logout, expired session (clear cookies, then save), password change with validation.
- **Admin CRUD for every entity** (Home, Team, Team member, Publication, Assistant, Contact, Dateien): list, create, edit, save notification, reload persists, publish toggle (also inside draggable lists), delete with confirmation (the app uses `window.confirm`; handle the dialog, test both cancel and accept), 422 validation shows message + marks field, language tabs save each language separately, public page reflects the change. Name every record you create with the prefix `QA-` and delete it in teardown.
- **Drag-and-drop reorder** (members within a team, publications, images in list view): vuedraggable / SortableJS uses native HTML5 DnD. Try `locator.dragTo()` first; if Sortable doesn't react, use `page.mouse` with small intermediate moves, and as a last resort dispatch `dragstart` / `dragover` / `drop` events. Do not change the app's Sortable options to make this pass. Verify the order after reload **and** via the API **and** on the public page.
- **Image manager:** upload png / jpg / jpeg (one by one and several at once), reject gif / pdf / > 8 MB with a message, upload on an unsaved record then save, caption overlay (single and DE / FR / EN), device switch, cropper opens on the saved crop, crop ratio per variant (desktop 10:12, mobile 3:2, Vorschau 1:1, Home default 16:10), save crop changes the public framing, cancel discards, Escape closes, grid / list toggle, eye toggle, delete.
- **Editor (Tiptap):** drive the toolbar by its button `title` attributes (Überschrift 1–3, Fett, Liste, Nummerierte Liste, Hochgestellt, Kleine Schrift, Worttrennung deaktivieren, Link, Formatierung entfernen, Rückgängig, Wiederholen). After saving, assert the stored HTML through the API. Link dialog: URL with new tab, E-Mail, Telefon, Datei (pick an uploaded PDF and upload one from inside the dialog), edit and remove. Paste: dispatch a `paste` event with a Word-style HTML fixture and assert the junk is gone. Round-trip: open the three longest stored texts, save unchanged, assert the public page's visible text and links are identical.
- **Dateien:** upload PDF, reject non-PDF and > 16 MB, delete (known: the file stays on disk; assert the record is gone, don't fail on the file), the file shows up in the editor's Datei list.
- **Visual vs production (read-only):** screenshot each public page in each language at 375 and 1280 px, locally and on `https://luvo.ch`, and compare with a tolerance. Expected differences (home hero crop, the 6 previously ignored crops, member image on tall screens) must be listed explicitly and masked or accepted, not silently ignored. For image framing, compare each local crop against the production image downscaled to the same size (PSNR or pixelmatch with tolerance), as in `.rewrite/tools/glide-render.php`.

### Fixtures

Generate upload fixtures in global setup (small png / jpg / jpeg / gif, a 9 MB jpg, a small pdf, a 17 MB pdf, a Word-paste HTML file). Don't commit files over 1 MB.

### Results file

Write a custom Playwright reporter plus a small PHPUnit hook (or parse the JUnit XML) that produce `tests/qa-results.json`:

```json
{ "run": "<ISO timestamp>", "commit": "<sha>", "results": { "<checklist id>": { "status": "pass|fail|manual", "tests": ["<test title>"], "error": "<first line if failed>" } } }
```

Every checklist id must appear: `manual` for the ones not automated. I'll import this into the checklist artifact.

## Constraints

- **Never write to production.** Only GET requests to `https://luvo.ch`, and only in the visual / image comparison tests. Throttle them (no more than 2 in parallel).
- **Never touch the `luvo` dev database or delete existing files in `storage/`.** Tests use `luvo_test` / `luvo_e2e`. Before the E2E run, record the file list of `storage/app`; after it, remove only files that are new.
- **Don't change application code to make a test pass.** If a test finds a real bug, leave the test failing, mark it with `test.fail()` or a skip that links to the finding, and list it in `.rewrite/08-test-plan.md` under "Findings". Ask me before fixing anything.
- Deploy items (`dp-*`) stay `manual`. Don't run anything against the server.
- Keep tests independent and runnable in any order; no shared state between files except the global setup.

## Done when

- `npm run test:all` runs green apart from documented findings, from a fresh `luvo_e2e` clone, twice in a row (proves cleanup works).
- `tests/qa-results.json` covers all 135 ids.
- `.rewrite/08-test-plan.md` is updated with: final coverage (counts per layer), how to run, findings, and what stayed manual and why.
