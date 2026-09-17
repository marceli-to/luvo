# Open questions — answer before starting

## 1. PHP version on production — BLOCKING

Laravel 13 requires **PHP ^8.3**. `composer.json` currently declares `^8.1`.
Local CLI is 8.4.22, but that says nothing about the host.

**If the server can't do 8.3+, the whole project stops here.** Check first.

## 2. `marceli-to/image-cache` — who bumps it?

Your own package, caps at `illuminate ^10|^11`. Needs `^12|^13` + a new tag.
Trivial change, but it's a separate repo — not in this working tree. Confirm
that repo is available and that retagging is fine.

## 3. `chinleung/laravel-multilingual-routes` v4 → v6

Two majors. The only backend item with real unknowns. Surface is small (7
routes + ~11 blade `localized_route()` calls), so worst case is a fork — but
read the upgrade guide before committing to the 2–2.5 day backend figure.

## 4. Dropzone replacement — which way?

No Vue 3 port of `vue2-dropzone`. Options:
- thin wrapper around Dropzone v6
- a maintained Vue 3 dropzone package
- plain `<input type="file">` + drag-drop handlers (least dependency risk)

Depends on how much of the current UX must survive. Affects
`components/files/Upload.vue` and `components/images/Upload.vue`, plus their
`mixins/edit.js` and the `Api/UploadController` / `Api/FileController` contract.

## 5. Laravel 12 stopover, or straight to 13?

Every third-party dep has both L12 and L13 releases, so a direct 11→13 jump
looks fine and is what the estimate assumes. Going via 12 adds maybe half a day
and gives a working checkpoint. Worth it only if the multilingual-routes jump
turns messy.

## 6. Backend and frontend together, or sequenced?

The estimate assumes sequential (backend first, then frontend) so each half can
be smoke-tested against a known-good other half. Doing them in one branch is
slightly faster but makes debugging much worse given there are no tests.

## 7. Deployment / rollback plan

No CI, no tests, and `INSTALL.TXT` is gitignored. How does this deploy today,
and what's the rollback if the upgraded build misbehaves in production?

## 8. Anything scheduled on the public site?

The estimate treats `resources/js/web/` (jQuery + Bootstrap 4 + vendored
swiper) as untouched. If a public-site refresh is coming anyway, doing it in
the same Vite migration is much cheaper than a second pass later.
