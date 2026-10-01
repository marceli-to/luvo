# Progress

Branch: `upgrade/laravel-13`. Local DB + `storage/app` = production copy of
2026-09-30 (backup of the previous local state in
`~/backups/luvo-local-before-prod-2026-09-30/`).

## Backend: done (2026-09-30)

| Step | Commit |
|---|---|
| Laravel 13.34, PHP ^8.3, Carbon 3, Intervention 4, Glide 4, multilingual-routes 6 | `c6bca43` |
| Slim skeleton (`bootstrap/app.php`, `bootstrap/providers.php`, lean `config/app.php`) | `2665a80` |
| Glide image routes, requested sizes, AVIF/WebP, crop + media-query fixes | `c100fe0` |

multilingual-routes v4 → v6 needed **no code changes**.

### Verified

- Same 111 routes. Every public page 200 in de/fr/en, `<html lang>` switches.
- Links and visible text of 14 pages (3 locales) identical to production.
- 404 → 404 (was 500 after the dependency bump; fixed null-safe menus).
- `api/*` → 401 JSON unauthenticated; session + XSRF cookies set.
- Images: all 180 variants (jpg/webp/avif) the pages emit return 200.
  Same framing as production for all 55 crops. Weight for those 55:
  20 MB (old, always 2400 px) → 6.1 MB jpg / 4.5 MB webp / 4.4 MB avif.

Later backend fixes found while testing the admin:

| Fix | Commit |
|---|---|
| Legacy skeleton files removed (by the user) | `c4396d8` |
| Sanctum 4 config (old one pointed at the deleted CSRF middleware → every api call 500) | `c4ba6a6` |
| Validation messages: L12+ requires strings → `BaseFormRequest`, same 422 shape | `30baa00` |

After go-live: `storage/app/public/cache/` (old image-cache output) can go.

### Open

- Home hero ignores its saved crop (`home/index.blade.php` passes no coords).
  Not among the agreed fixes; client's call.

## Deploy notes (so far)

- `composer install --no-dev` on the server (vendor is not in git).
- Glide cache lives in `storage/app/.glide-cache` (writable, not backed up).
- First view of each image variant renders it (~0.2–0.7 s for AVIF).

## Frontend: done (2026-10-01)

| Step | Commit |
|---|---|
| Public site on Vite (pattern from generalplaner-ag.ch) | `6d145f0` |
| Admin on Vue 3 + Vite | `2d7787e` |

Build: `npm run build` → `public/build` (committed). No more Mix.

### Verified in the browser (local admin user `claude@luvo.test`)

- Public site: pixel-identical to production, no console errors; jQuery
  plugins, lazysizes, simplebar load; tall screens now get the 1600×1920 AVIF.
- Admin: login, all 13 screens load without errors (lists, forms, 3–21
  TinyMCE editors, tabs, dropzones); save (PUT 200 + notification);
  validation (422 → message + field marked); publish toggle inside a
  draggable list (`$parent.$parent` chain OK); cropper opens on the saved
  crop and saves the same coords; image upload → store → delete; file
  upload → store → delete.

### Still to check by hand

- **Drag-and-drop reorder** (Sortable uses native HTML5 DnD, which the
  browser automation can't drive). One drag per list type: team members,
  publications, images.
- Icons are lucide now (same names as feather); a few glyphs differ slightly.

### Known, unchanged behaviour

- Deleting a file in "Dateien" removes the record only, not the file.
- `api/publications` is dead and broken (missing import); unused by the admin.
- `public/assets/js/shop.js` is unreferenced Mix output from elsewhere.

## Admin rewrite: done (2026-10-01, branch `rewrite/vue-setup`)

`fc4583f`: `<script setup>` + composables, ~6.9k → ~2.2k LOC, 75 → 37 files.

```
resources/js/dashboard/
  app.js, App.vue, router.js      lazy routes, forms get type=create|edit as prop
  lib/http.js                     axios instance + app-wide error handling
  lib/images.js, lib/utils.js
  composables/                    useResourceForm, useImages, useListing,
                                  useOrder, useTinyConfig, useUser, useEscape
  components/images/ImageManager  one editor for all image variants
  components/ui/                  Tabs, LanguageTabs, RadioButton, ListActions,
                                  Uploader (Dropzone 6), LoadingIndicator, ...
  views/<entity>/Form.vue|Index.vue
```

Verified in the browser: all 14 screens (no errors/warnings), toggles in
lists and draggable groups, save, 422 validation, publication toggle,
image upload/crop (same geometry)/caption overlay/delete for all three
image variants, Desktop/Mobile ratios, Escape, list view, file upload/delete.
Initial admin JS ~216 KB (was 639 KB).

Still by hand: drag-and-drop reorder (see above).

## Editor: TinyMCE → Tiptap (2026-10-01, `3bf1067`)

Tiptap 3 as in rework.strut.ch / forrerzimmermann.ch, in
`components/ui/editor/` (Editor, Toolbar, LinkDialog, smallText).
Link dialog: URL / E-Mail / Telefon / **Datei** (uploaded files from
`/api/files`). TinyMCE and its 7.5 MB of self-hosted assets are gone.

All 197 stored rich-text values round-trip with the same visible text,
links, headings, fs-sm, lists and bold (`.rewrite/tools/tiptap-roundtrip.mjs`;
needs `@tiptap/html`, `happy-dom` and a `content.json` export). Saving a text
drops Word/Outlook paste junk (246 KB → 85 KB overall); pasted inline fonts
(Segoe UI, 12pt) disappear, so those texts use the site font again.

## Next

- Privacy policy (Datenschutz) in EN + FR: **content only**. The admin's
  Kontakt form already has FR/EN Datenschutz fields; they're empty, so the
  FR/EN pages fall back to the German text. Enter the client's texts there.
- Deploy: `git pull`, `composer install --no-dev`, `php artisan optimize:clear`,
  make `storage/app/.glide-cache` writable. Snapshot DB + storage first.
