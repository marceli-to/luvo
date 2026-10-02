# QA automation: test plan (Phase 1, for review)

Written 2026-10-02 against `master` @ `c9c7dc9`. No test code exists yet.
Source checklist: `.rewrite/qa-checklist.json` (135 ids). Brief: `07-qa-automation-prompt.md`.

## Summary

| Layer | Ids | Runs against |
|---|---|---|
| `feature` | 24 | PHPUnit, Laravel app in-process, DB `luvo_test`, temp storage dir |
| `e2e` | 96 | Playwright + Chromium 1243, `php artisan serve` on 127.0.0.1:8010, DB `luvo_e2e` |
| `visual` | 3 | Playwright, local vs `https://luvo.ch` (GET only, at most 2 in flight) |
| `manual` | 12 | 9 deploy items + setup-db, setup-browsers, ak-privacy |
| **Total** | **135** | |

Many ids also get a secondary test in another layer (e.g. a 422 shape in `feature`
plus the UI marking in `e2e`). The table names the **primary** layer; the
"Secondary" column lists the others. All linked tests feed the id's result.

Before any of this: the code reading turned up **13 likely bugs and gaps**
(see [Findings](#findings-from-reading-the-code)) and **11 checklist items that
don't match the code** as written. Please look at those two sections first;
some change what a test should assert.

---

## 1. Layer per checklist id

### Setup & baseline

| Id | Layer | Secondary | Test |
|---|---|---|---|
| setup-db | manual | e2e | Rollback point is a human check. E2E global setup asserts `luvo_e2e` was cloned and `storage/app/public/uploads` is non-empty. |
| setup-build | e2e | | Preflight spec: `vite build --outDir <tmp>`, compare file hashes with `public/build`, and `git status --porcelain public/build` is empty. |
| setup-glide | feature | e2e | `storage/app/.glide-cache` exists and is writable (real path, before the temp-storage swap). |
| setup-users | e2e | | Global setup seeds the 3 QA users; a spec checks each one's login outcome. |
| setup-browsers | manual | | Real Safari, Firefox, iOS Safari, Android. Only Chromium is installed (no WebKit). |

### Public site: global

| Id | Layer | Secondary | Test |
|---|---|---|---|
| pg-home-urls | feature | e2e | `/`, `/de`, `/fr`, `/en`, `/{de,fr,en}/home` → 200. |
| pg-lang-attr | feature | e2e | `<html lang>` for every page type × de/fr/en. |
| pg-switcher | e2e | | On team, member, assistant, contact: click D/E/F, same page in the other locale, active link highlighted. |
| pg-localized-urls | feature | | The five localized slugs → 200; also `/en/team-luks/assistant` (EN has no translated slug). |
| pg-404 | feature | e2e | Unknown URL → 404 + styled 404 view (not the Laravel default, not 500). E2E allows the 404 for that test only. |
| pg-logo | e2e | | Logo href = home in current locale, on every page type. |
| pg-desktop-menu | e2e | | Team Luks / Vogt / Kontakt links work, dropdowns open (1280 px). |
| pg-mobile-menu | e2e | | Phone project: burger opens/closes, team submenus expand, "Über uns" + member links work. |
| pg-member-menu | feature | e2e | Menu lists only published members of the team in `order`, plus "Assistenz" when a published assistant exists. |
| pg-console | e2e | | Crawl every public page × locale × 3 viewports with the strict fixture (no console error, page error, or response ≥ 400). |
| pg-favicons | e2e | | Every `<link rel=icon|apple-touch-icon|manifest>` href → 200, plus icons listed in `site.webmanifest`. |
| pg-plugins | e2e | | lazysizes swaps `data-src` on scroll, simplebar wrapper present and scrolls, swiper instances initialised. |
| pg-seo | feature | | `<title>` and meta description on every page; member page uses `meta_description`, falls back to 30 words of description. |
| pg-visual | visual | | Screenshot compare local vs production, 375 + 1280 px, every page × locale. |

### Public site: pages

| Id | Layer | Secondary | Test |
|---|---|---|---|
| pp-home-hero | feature | visual | Hero `<source>`/`<img>` URLs carry the saved coords. Visual: framing compare (listed expected diff vs production's live page). |
| pp-home-hero-sizes | e2e | | Viewports 900/1200/1600/1920: `currentSrc` picks the right slot; response is `image/avif`. |
| pp-home-scroll | e2e | | Click the arrow; `scrollY` ends at the text section. |
| pp-home-text | visual | e2e | Visual compare of the text block; e2e checks h1/h2/h3, `strong`, lists, links render. |
| pp-team-text | e2e | | Team text inside the simplebar column, scrollable when long. |
| pp-team-desktop | e2e | | 1280 px: vertical swiper, prev/next present only with > 1 image, slides change. |
| pp-team-mobile | e2e | | 375 px: horizontal swiper with the mobile-device images. |
| pp-team-unpublished | feature | | Unpublished team image's URL absent from the HTML. |
| pp-member-all | feature | e2e | Every member (published and not, see F7) × 3 locales → 200. E2E crawls the published ones. |
| pp-member-tall | e2e | | Tall project (1280×1000): member desktop image `complete && naturalWidth > 0`, 200, ~1600 px wide. |
| pp-member-crops | feature | visual | URLs of the 6 top-left crops carry `w,h,0,0`. Visual: framing vs production (fetched with the same coords). |
| pp-member-sections | feature | | QA member with each section filled/empty → block shown/hidden. **Will fail on F2.** |
| pp-member-pubs | feature | e2e | Publications in admin order, unpublished hidden. **Will fail on F1.** |
| pp-assistant | feature | e2e | Both teams: description, list, images (de/fr/en). |
| pp-contact | feature | e2e | Address, Maps link, Impressum, Datenschutz, images. |
| pp-privacy-lang | feature | | Empty FR/EN privacy → German text; filled → translated text. |

### Image delivery (Glide)

| Id | Layer | Secondary | Test |
|---|---|---|---|
| img-formats | e2e | feature | Collect every `<source srcset>` and `<img src>` per page; fetch each: 200 and `content-type` matching `?fm` (avif/webp/jpeg). |
| img-sizes | e2e | feature | Decode each response's dimensions; the bounded side equals the slot (or the crop, if smaller), never 2400 unless asked. |
| img-framing | visual | | Each local crop vs production's image for the same URL, downscaled to the same size, PSNR ≥ 30 dB. |
| img-original-thumb | feature | e2e | `/img/original/x` = bytes of the upload; `/img/thumbnail/x` = 300×300; unknown file / `../` → 404. |
| img-guard | feature | | Size > 2400, 0, non-digit → 404. **Non-whitelisted sizes and formats are not rejected (F5).** |
| img-cache | feature | | Second request for the same variant is served from `.glide-cache` (cache file exists, not re-rendered). |

### Login & access

| Id | Layer | Secondary | Test |
|---|---|---|---|
| auth-login | e2e | feature | Wrong password → error alert; QA admin → lands on `/administration/home`. |
| auth-guest | feature | e2e | Logged out: `/administration`, `/administration/team/member/edit/1` → redirect `/login`. |
| auth-api | feature | | Logged out: `GET /api/home` (with `Accept: application/json`) → 401 JSON. |
| auth-role | feature | e2e | Non-admin: `/administration` → 403. **API is not role-guarded (F3).** |
| auth-disabled | feature | | `/register` → 404; `POST /password/email` → 404/405; `/password/reset` shows the home page (no form). |
| auth-logout | e2e | | Sidebar logout → `/`; back button → admin redirects to login; a replayed save → 401. |
| auth-expired | e2e | | Clear cookies, click Save → redirect to `/login` (the 401 handler). Also stale XSRF → 419 (F12). |
| auth-password | e2e | feature | Mismatch and < 6 chars → fields marked; valid change → logout, log in with the new password; teardown re-seeds the user. |

### Admin: shell & navigation

| Id | Layer | Secondary | Test |
|---|---|---|---|
| sh-dashboard | e2e | | `/administration` shows "Willkommen" + QA admin's name. |
| sh-nav | e2e | | Each sidebar link loads its screen; the current one has the active class. |
| sh-reload | e2e | | Direct load of a list, an edit form and a create form for each entity. |
| sh-history | e2e | | list → form → back → forward. |
| sh-mobile | e2e | | Phone project: header button opens the menu, arrow closes it, navigating closes it. |
| sh-notify | e2e | | Success toast after save and error toast after 422 appear, then disappear. |
| sh-icons | e2e | | Every `a`/`button` in the shell and forms has text or a non-empty `svg`. |
| sh-unknown | e2e | | Records today's behaviour: shell renders, empty content, no console error. See "Checklist items" (C6). |
| sh-errors | e2e | | Reached in-app: open `/administration/home/edit/999999` → 404 → router shows Not Found. See C7. |

### Admin: Home

| Id | Layer | Secondary | Test |
|---|---|---|---|
| ah-list | e2e | | List loads; eye toggles `publish` (API confirms). Public side: see F4, restored in teardown. |
| ah-delete | e2e | | On a `QA-` record: confirm → cancel keeps it, accept removes it. |
| ah-create | e2e | | Create `QA-Home`, save, found in list. |
| ah-required | e2e | feature | Empty Titel → 422, toast, field `.has-error`. Feature: 422 body shape `errors.title.de[] = {field, error}`. |
| ah-langs | e2e | | DE/FR/EN tabs show separate Titel/Text; save; API has each language separately. |
| ah-publish | e2e | | "Publizieren?" radio → API `publish`. |
| ah-save | e2e | | Edit the real home text, save, toast, reload shows it, public `/de` shows it; teardown restores. |
| ah-images | e2e | | Home-specific image checks: "Vorschau" label, 1:1 crop, DE/FR/EN captions, eye, delete. |

### Admin: Teams

| Id | Layer | Secondary | Test |
|---|---|---|---|
| at-teams-list | e2e | feature | Toggle, edit, delete-cancel in the UI. Create + delete-accept via feature test (C1). |
| at-team-form | e2e | feature | Team select, Titel*/Text* required in DE, FR/EN optional, publish. |
| at-team-images | e2e | | Desktop/Mobile, DE/FR/EN captions, desktop info text visible for a desktop image. |
| at-members-groups | e2e | | One group per team, members match the API's `team_id`. |
| at-members-toggle | e2e | | Eye inside the draggable list toggles (API + class), restored. |
| at-members-drag | e2e | | Drag a member; order checked after reload, via `/api/team/members`, and in the public menu; restored. |
| at-members-crud | e2e | | Create `QA-` member, edit, delete with confirm (cancel + accept). |
| at-assist-list | e2e | feature | Toggle, edit, delete-cancel in the UI; create + delete via feature test (C1). |

### Admin: Team member form

| Id | Layer | Secondary | Test |
|---|---|---|---|
| am-required | e2e | feature | Vorname, Name, Team missing → three messages, three fields marked. |
| am-fields | e2e | feature | Every field × DE/FR/EN on a `QA-` member; values via API after reload. **SEO Beschreibung is lost on create (F6).** |
| am-name-url | e2e | | Rename → new slug in the public URL and member menu; old slug still resolves (id-based). |
| am-images | e2e | | Desktop 10:12 / Mobile 3:2 crop ratio, single caption, publish, delete. |
| am-pub-create | e2e | feature | From the member form; Titel required (and Artikel, C3); Beschreibung/Artikel per language. |
| am-pub-edit | e2e | | Edit, toggle, delete with confirm; "back" returns to the member. |
| am-pub-drag | e2e | | Drag publications; check reload + API + public order. |

### Admin: Assistenz & Kontakt

| Id | Layer | Secondary | Test |
|---|---|---|---|
| aa-required | e2e | feature | Team required (and Beschreibung DE, C4); fields per language; publish. |
| aa-images | e2e | | Desktop/Mobile, caption, crop, delete. |
| ak-list | e2e | | Toggle, edit, delete with confirm on a `QA-` contact, create. |
| ak-fields | e2e | | Adresse, Impressum, Datenschutz, Maps URI per language → API + public page. |
| ak-privacy | manual | | Entering the client's real texts is content work. The FR/EN fallback is covered by `pp-privacy-lang`. |
| ak-images | e2e | | Desktop/Mobile, caption, crop, delete. |

### Admin: Image manager

Run fully on the Team member form; Home, Team, Assistenz and Kontakt get a
parameterised spot-check (upload, crop ratio, caption shape, delete).

| Id | Layer | Secondary | Test |
|---|---|---|---|
| im-upload | e2e | | png, jpg, jpeg one at a time via the file input; three at once; one via a synthetic `drop` with a `DataTransfer`. |
| im-reject | e2e | feature | gif, pdf, 9 MB jpg → Dropzone error toast with the reason; no POST sent. Server side: F8. |
| im-unsaved | e2e | | Create form: upload 2, reorder, save → images and order on the new record (API). |
| im-caption | e2e | | Pencil → overlay; single + DE/FR/EN captions; shown on preview; close by button, ✕, Escape; saved with the form. |
| im-device | e2e | | Switch Anwendung, save; public page shows it in the desktop/mobile slot. |
| im-crop-open | e2e | | Cropper's initial coordinates equal the saved coords (read from the stencil + "W x H px" label). |
| im-crop-ratio | e2e | | Stencil ratio ≈ 10/12, 3/2, 1, 16/10; Desktop/Mobile buttons switch it. |
| im-crop-save | e2e | | Move the stencil, save → new coords in API, new coords in the public URL. Abbrechen → coords unchanged. |
| im-toggle | e2e | | Eye → image URL disappears from the public page; restore. |
| im-delete | e2e | | Confirm cancel keeps, accept removes (UI + API). |
| im-view | e2e | | Image icon opens a popup whose URL is the crop URL, 200. |
| im-list | e2e | | Grid ↔ Listen toggle switches markup. |
| im-drag | e2e | | List view drag; reload + API + public swiper order. |

### Admin: Rich-text editor (Tiptap)

Driven by the toolbar `title` attributes; stored HTML asserted via the API.

| Id | Layer | Secondary | Test |
|---|---|---|---|
| ed-roundtrip | e2e | | Three longest texts (a Werdegang, Kontakt Datenschutz, Home text): open, save unchanged, public visible text + link hrefs identical to before; teardown restores the raw HTML. |
| ed-cleanup | e2e | | A stored text with inline `font-family` (picked by SQL in global setup): save unchanged → no `font-family`/`mso-` in API or public HTML. |
| ed-multi | e2e | | Member form: all editors mounted < 3 s; typing in one changes only that field (API diff). |
| ed-undo | e2e | | Type, Rückgängig, Wiederholen; disabled states. |
| ed-headings | e2e | | H1/H2/H3 and back to paragraph; `.is-active` follows. |
| ed-bold | e2e | | `<strong>`. |
| ed-lists | e2e | | `<ul>`, `<ol>`, toggle off; public list matches the site's list style (computed style). |
| ed-sup | e2e | | `<sup>`. |
| ed-small | e2e | | Stored `fs-sm` markup; public computed font-size smaller than body text. |
| ed-nowrap | e2e | | Stored no-wrap markup; public `white-space: nowrap`. |
| ed-clear | e2e | | Formatting removed, text kept. |
| ed-link-url | e2e | | `https://` + value, `target=_blank rel=noopener` only when ticked. |
| ed-link-mail | e2e | | `mailto:` href. |
| ed-link-tel | e2e | | `tel:` href, spaces stripped. |
| ed-link-file | e2e | | Pick an uploaded PDF; upload a PDF from inside the dialog (also appears under Dateien). |
| ed-link-edit | e2e | | Reopen a link: fields prefilled, change it, Entfernen removes it, Titel kept. |
| ed-paste | e2e | | Dispatch `paste` with `tests/e2e/fixtures/word-paste.html`: no `mso-*`, `<o:p>`, `font-family`, `class="Mso…"`; headings/lists/bold kept. |
| ed-keys | e2e | | Enter = new `<p>`, Shift+Enter = `<br>`, Ctrl/Cmd+B, Ctrl/Cmd+Z. |

### Admin: Dateien

| Id | Layer | Secondary | Test |
|---|---|---|---|
| md-upload | e2e | | Upload `QA-` PDF; in the list; link opens (200, `application/pdf`). |
| md-reject | e2e | feature | Non-PDF and 17 MB PDF rejected by Dropzone with a reason. Server side: F8. |
| md-delete | e2e | | Confirm → record gone (UI + API). The file staying on disk is expected and not asserted. |
| md-linkable | e2e | | New file shows in the editor link dialog's Datei list. |

### Edge cases

| Id | Layer | Secondary | Test |
|---|---|---|---|
| rb-double | e2e | | Double-click Save on a create form → exactly one `QA-` record (API). |
| rb-slow | e2e | | CDP throttling ("Slow 4G" profile): loading indicator visible, save still succeeds. |
| rb-server-error | feature | | `APP_DEBUG=false`, a test-only route that throws → styled 500 view, no stack trace or file paths. |
| rb-special | e2e | feature | `QA-Ümlaut «Guillemets» & Donaudampfschifffahrtsgesellschaft` in text + rich-text fields → API and public page show it unchanged. |

### Deploy & production smoke test

All `manual`: they run on the production server, which this suite never touches.

| Id | Layer | Why |
|---|---|---|
| dp-snapshot | manual | Server action |
| dp-php | manual | Server environment |
| dp-env | manual | Server `.env` |
| dp-pull | manual | Deploy step |
| dp-glide | manual | Server filesystem |
| dp-smoke-pub | manual | Against production after deploy (could reuse the visual crawl later, read-only) |
| dp-smoke-admin | manual | Writes to production |
| dp-logs | manual | Server logs |
| dp-cleanup | manual | Server filesystem |

---

## 2. Test data, users, cleanup

### Backend (`feature`)

- **Migrations from scratch run cleanly** (checked in a throwaway DB, then
  dropped), **but they don't reproduce the production schema**: the local
  `luvo` DB has `ON DELETE CASCADE` on 6 foreign keys (images → parents,
  publications → members, members → teams) and `double(16,12)` coords. The
  migrations have neither (F9). Without cascade, deleting a member with
  images 500s on a migrated DB.
- So `luvo_test` is loaded from a **structure-only dump of the local `luvo`**
  (`tests/fixtures/schema.sql`, `mysqldump --no-data`, committed, ~15 KB).
  `composer test:schema` regenerates it. It is loaded once per run by a small
  bootstrap if the schema hash changed; tests then use `DatabaseTransactions`.
  (Alternative: `php artisan schema:dump` into `database/schema/`, which
  `RefreshDatabase` picks up automatically. I didn't pick it because it also
  changes what `migrate` does on an empty DB.)
- `phpunit.xml`: drop the SQLite-in-memory block, set `DB_CONNECTION=mysql`,
  `DB_DATABASE=luvo_test` (with `force="true"`). `TestCase::setUp()` aborts
  unless the connection's database is exactly `luvo_test`.
- Data: small factory classes in `database/factories/` used via
  `XFactory::new()` (no `HasFactory` trait needed, so no model changes).
- Storage: `UploadController` and Glide use `storage_path()` directly, so
  `Storage::fake()` doesn't help. `TestCase` points the app at a per-test temp
  dir with `$app->useStoragePath()` and copies in the fixture images. The real
  `storage/` is never written by feature tests.

### E2E

- `scripts/e2e-db.sh` (re)creates `luvo_e2e` from `luvo`
  (`mysqldump luvo | mysql luvo_e2e`). It refuses any target named `luvo`, and
  only reads from `luvo`.
- `.env.e2e` (gitignored) + `.env.e2e.example` (committed): `APP_ENV=e2e`,
  `DB_DATABASE=luvo_e2e`, `APP_URL=http://127.0.0.1:8010`,
  `SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8010`, `SESSION_DOMAIN=null`,
  `SESSION_SECURE_COOKIE=false`, `MAIL_MAILER=log`, `APP_DEBUG=true`,
  `CACHE_STORE=array`, QA user credentials.
  - `CACHE_STORE=array` matters: the API is throttled to 200 requests/min per
    user and an E2E run exceeds that. With the array store the limiter resets
    on every request (built-in server). The throttle itself isn't under test.
  - `PHP_CLI_SERVER_WORKERS=4` so parallel workers don't queue on one PHP process.
- Global setup refuses to start if `bootstrap/cache/config.php` exists
  (a cached dev config would point the server at `luvo`). It also checks over
  HTTP that the served app reports `luvo_e2e` (`php artisan about --json` run
  with the e2e env before the server starts).
- **Records**: everything a test creates is named with the prefix `QA-` and
  deleted in its `afterEach` via the API. Global teardown then removes any
  leftover `QA-%` rows with SQL against `luvo_e2e` (safety net, logged as a
  warning so leaks are visible).
- **Existing records a test must change** (home text, member order, publish
  toggles, crops, contact privacy, the three round-trip texts): the test
  snapshots the record through the API in `beforeEach` and puts it back in
  `afterEach` (PUT / order endpoints). Wherever a `QA-` record does the job
  instead, it does (e.g. drag tests move a `QA-` member within team Luks).
- **Leak check**: global setup takes a checksum of every content table
  (ignoring `id`s of `QA-` rows, `updated_at`, `sessions`, `users`); global
  teardown compares and fails the run if anything changed. This is what makes
  "green twice in a row from a fresh clone" meaningful.
- **Concurrency**: public read-only specs run in parallel (4 workers). Admin
  specs run with 1 worker, because several mutate shared records (e.g. home
  text and the member order are visible on public pages). If Playwright 1.63's
  per-project worker limit isn't enough, `test:e2e` runs the two groups as two
  invocations and the results merge.

### Test users

`database/seeders/QaUserSeeder.php`, which throws unless `APP_ENV` is `e2e` or
`testing`. `updateOrCreate` by email; credentials from env
(`QA_ADMIN_EMAIL/PASSWORD`, `QA_USER_*`, `QA_UNVERIFIED_*`):

| User | role | email_verified_at |
|---|---|---|
| QA admin | `admin` | now |
| QA user | `editor` (anything but `admin`) | now |
| QA unverified admin | `admin` | null |

`users.role` defaults to `admin` and `firstname`/`name` aren't fillable, so the
seeder uses `forceFill`. Run in Playwright global setup with
`php artisan db:seed --class=QaUserSeeder --force` (env e2e). `auth-password`
re-runs it in teardown to restore the password. Feature tests create users
inline.

### Uploaded files

- Global setup writes the list of every file under `storage/app` (path +
  size) to `tests/.state/storage-before.txt`. Global teardown deletes only
  paths not in that list, prints them, and never touches anything that was
  there before. That covers uploads in `public/uploads`, `public/uploads/files`
  and new renders in `.glide-cache`.
- Caveat: a file created by you in the dev app *during* an E2E run would also
  count as new. Don't use the dev admin while the suite runs.

### Fixtures

Generated in global setup into `tests/.fixtures/` (gitignored), none committed
over 1 MB: small png/jpg/jpeg (ImageMagick, distinct colours so crops are
checkable), a gif, a 9 MB jpg (random noise so it doesn't compress), a small
pdf, a 17 MB pdf (padded), and `word-paste.html` (committed, a few KB, real
Word clipboard markup: `mso-*` styles, `<o:p>`, `MsoNormal`, conditional
comments, inline Segoe UI).

### Production (visual)

- Separate config `playwright.visual.config.ts`, 1 worker.
- Every request to `luvo.ch` goes through `context.route()`: non-GET is
  aborted (hard guard against writing), GETs pass through a semaphore of 2.
  That bounds production to 2 requests in flight, including sub-resources.
- Production image downloads are cached in `tests/.cache/prod/` (gitignored,
  24 h TTL), so repeated runs don't re-download ~60 MB of 2400 px JPEGs.
- **Expected differences**, kept in `tests/visual/expected-differences.ts`,
  each masked (Playwright `mask`, same boxes on both sides) with a reason:
  1. Home hero: local applies the saved crop, production doesn't.
  2. The 6 top-left crops production ignores: member images 24 (member 3) and
     63 (member 18, unpublished), team images 39, 40, 43 (team Vogt), contact
     image 14 (unpublished). Only 24, 39, 40, 43 are visible publicly.
  3. Member desktop image on the tall viewport (broken on production).
  4. Anything that differs because of data, discovered on the first run, is
     added here explicitly rather than by raising the tolerance.
- **Framing** (`img-framing`): for every crop URL a local page emits (jpg
  variant), fetch production's response for the *same path* (production's
  route also applies coords when given, so the 6 ignored crops can be checked
  too), downscale it to the local image's size with ImageMagick and require
  PSNR ≥ 30 dB (method from `05-image-pipeline.md`).

---

## 3. Results file

- Playwright: custom reporter `tests/e2e/reporters/qa-results.ts`; reads
  `annotations` of type `qa`.
- PHPUnit 12: a small extension `tests/Support/QaResults/Extension.php`
  (registered in `phpunit.xml`) that reads a `#[Qa('id', ...)]` attribute on
  each test method and writes `tests/.results/phpunit.json`.
- `scripts/qa-results.mjs` merges the partial files with the checklist and
  writes `tests/qa-results.json` in the requested shape. Every id appears; ids
  with no test → `manual`. Rules: any linked test failed → `fail` (first error
  line); a known finding (`test.fail()` / skip with `F<n>`) → `fail` with
  error `Known finding F<n>: …`; all passed → `pass`. When only one suite ran,
  the other suites' last partial files are reused and their age is printed.
- It also fails if an id in a test doesn't exist in the checklist (typos).

## 4. Dependencies to add

| Package | Why |
|---|---|
| `@playwright/test@1.63.0` (dev, exact) | E2E + visual runner. 1.63.0 is the version whose Chromium (rev 1243) is already in `~/Library/Caches/ms-playwright`; no browser download. No WebKit there, so no WebKit project. |
| `image-size` (dev) | Read width/height of avif/webp/jpeg responses in-process for `img-sizes` (pure JS, tiny). |

Nothing else: screenshot diffs and PSNR use the ImageMagick 7 already
installed (`magick compare`, AVIF/WebP/JPEG/PNG supported). If you prefer the
suite to be portable without ImageMagick, I'd add `pngjs` + `pixelmatch` +
`sharp` instead. No new Composer packages (PHPUnit 12 is already there).

## 5. Scripts

| Script | Does |
|---|---|
| `composer test` | `php artisan test` |
| `composer test:schema` | regenerate `tests/fixtures/schema.sql` from `luvo` (structure only) |
| `npm run test:e2e-db` | `scripts/e2e-db.sh` |
| `npm run test:e2e` | Playwright e2e projects (desktop, tall, phone) |
| `npm run test:visual` | Playwright visual config (production GETs) |
| `npm run test:all` | `composer test` → `test:e2e` → `test:visual` → `qa-results.mjs` (each step runs even if the previous one failed) |

Two `example` stubs in `tests/Unit` and `tests/Feature` get deleted.

---

## Findings from reading the code

Not fixed. Each will get a test that fails (marked as a known finding) once
written. Severity is my estimate.

| # | Severity | Finding | Where | Checklist |
|---|---|---|---|---|
| F1 | Medium | Member page lists **all** publications, including unpublished ones, whenever the member has at least one published. The `@if` checks `publishedPublications`, the `@foreach` loops `publications`. No member hits it today (member 2 has only unpublished ones, member 1 only published). | `member.blade.php`, `TeamMemberController::index` | pp-member-pubs |
| F2 | Medium | "Beschreibung" is only shown when "Info" (`credits`) is filled: the description block is wrapped in `@if ($data->credits)`. | `member.blade.php` | pp-member-sections |
| F3 | Medium | The API has no role check: any logged-in user, admin or not, verified or not, can read and write everything under `/api/*`. Only `/administration` has `role:admin` + `verified`. Registration is disabled, so today it only matters for existing accounts. | `routes/api.php` | auth-role |
| F4 | Low | Public pages ignore the record-level publish flag for Home, Team, Assistenz and Kontakt (Home and Kontakt take the first row, teams by slug). The flag only hides menu entries. So "eye icon toggles publish and the public site follows" holds for menus and images, not for the page itself. A new Home or Kontakt entry never shows publicly. Likely old behaviour. | `HomeController`, `ContactController`, `TeamController`, `AssistantController` | ah-list, ak-list |
| F5 | Low | The image route has **no whitelist**: any size 1–2400 and any `w,h,x,y` coords are rendered and cached, and an unknown `?fm=` silently becomes JPEG instead of being rejected. `05-image-pipeline.md` says the route was to be guarded by a whitelist. Cache-filling risk. | `ImageController` | img-guard |
| F6 | Low | Creating a member drops "SEO Beschreibung": `meta_description` is not in `$fillable`, and `store()` mass-assigns. Editing works (it uses `setTranslation`). | `TeamMember::$fillable` | am-fields |
| F7 | Low | Unpublished members' pages are publicly reachable by URL (200). Maybe intended (preview). | `TeamMemberController::index` | pp-member-all |
| F8 | Medium | Uploads have **no server-side validation**: type and size are only checked by Dropzone. Any file, including `.php` or `.html`, can be uploaded by a logged-in user into `storage/app/public/uploads`, which is web-served via `/storage`. With F3, that's any account. Whether a `.php` there executes depends on the server config. | `UploadController::image/file` | im-reject, md-reject |
| F9 | Medium | Migrations don't match the production schema (no `ON DELETE CASCADE` on 6 FKs, coords as `double` instead of `double(16,12)`). A fresh install from migrations 500s when deleting a member/team that has images or publications. | `database/migrations` | (setup) |
| F10 | Low | Deleting an image removes only the DB row; the upload stays on disk (same as for Dateien, but not documented for images). | `*ImageController::destroy` | im-delete |
| F11 | Low | Member menu never marks the current member as active: it reads `parameter('teamMember.id')`, which doesn't exist (the parameter is `teamMember`). Mobile menu same. | `menu/team-members.blade.php` | pg-member-menu |
| F12 | Low | A 419 (stale CSRF/XSRF) gets no message in the admin; the http interceptor handles 401/403/404/405/422/500 only. Saving after a long idle may fail silently. | `lib/http.js` | auth-expired |
| F13 | Info | Cropper: the Desktop/Mobile buttons change the image's `device` immediately, so "Abbrechen" discards the coords but not the device switch (it's saved with the next form save). | `ImageManager.vue` `switchDevice` | im-crop-save |

Also noted (data, not code): local `luvo` has a team member "Marcel
Stadelmann" (id 20, team Luks, published). If production doesn't, the visual
compare will show it in the Luks menu. Is that a test record?

## Checklist items that don't match the code

| # | Id | Issue | What the test will do |
|---|---|---|---|
| C1 | at-teams-list, at-assist-list | "Hinzufügen" is hidden when 2 teams / 2 assistants exist, which is the case. Deleting a real team cascades to its members and images. | UI: toggle, edit, delete → cancel. Create and delete-accept in a feature test on `luvo_test`. |
| C2 | ah-list | "The public site follows" isn't true for the Home record (F4). | Asserts the API state; public side marked as known finding. Your call whether that's expected. |
| C3 | am-pub-create | Server also requires `articles.de` (Artikel), not only Titel. | Asserts both messages. Checklist text should say so. |
| C4 | aa-required | Server also requires `description.de` (Beschreibung), not only Team. | Same. |
| C5 | ak-fields | Label says "Adresse (DE) / Text (FR, EN)"; the server requires `address.de`. | Tests the requirement as coded. |
| C6 | sh-unknown | "Note what happens" is an observation, not a pass/fail. Today: no catch-all route, so the shell renders with an empty content area. | Asserts no crash/console error; records the behaviour in the test title. Decide whether it should redirect to Not Found. |
| C7 | sh-errors | `/forbidden` and `/not-found` are Vue routes outside `/administration`, so loading them directly hits Laravel's 404. They're only reachable in-app (after an API 403/404). | Reaches them in-app. |
| C8 | auth-disabled | `/password/reset` is routed to the home page (200), not a 404. | Asserts there's no reset form and no `POST /password/email` route. |
| C9 | pp-member-crops | "Compare with production": production ignores these crops, so it will differ by design. | Compares against production's image requested *with* the coords (same geometry), see Framing. |
| C10 | im-reject, md-reject | "Rejected with a message" is client-side only (F8). | E2E asserts the Dropzone message; feature test documents the missing server check as a finding. |
| C11 | img-cache | "Serves quickly" isn't a stable assertion. | Asserts a cache hit (cache file exists, unchanged mtime, no re-render), not timing. |

## Decisions (2026-10-02)

1. Production for the visual tests is **luksundvogt.ch**.
2. ImageMagick CLI for screenshot diffs and PSNR; no image npm packages.
3. Schema: `tests/fixtures/schema.sql`.
4. F4 and F7 are findings: the publish flag should hide the page, and
   unpublished members should not be reachable. Their tests fail until fixed.
5. Team member 20 was a test record; deleted from local `luvo` (its two
   upload files remain on disk).

---

## Coverage (to be filled in after Phase 2)

_Final counts per layer, how to run, findings status, and what stayed manual._
