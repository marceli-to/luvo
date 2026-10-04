# Deploy checklist

Written 2026-10-02. State: everything is on `master` (`19f56ad` and later), the
automated QA suite passes (`npm run test:all`: 123 checklist ids pass, 0 fail,
12 manual), and the local site matches production wherever no change was
intended. What's left can only be done on the server or by hand.

The ids in brackets are the matching items in `qa-checklist.json`.

## Before the deploy

1. **Rollback point:** snapshot the production database and `storage/`
   (`dp-snapshot`).
2. **Server requirements:** PHP 8.3+ for both the command line and the web
   server; Imagick with AVIF and WebP (`dp-php`).
3. **Production `.env`:** check `APP_URL`, `SANCTUM_STATEFUL_DOMAINS`,
   `SESSION_SECURE_COOKIE`, `APP_DEBUG=false` and the mail settings (`dp-env`).
   Rename `MAIL_DRIVER` to `MAIL_MAILER` (the old name is no longer read) and
   drop `LUVO_*` and `BROADCAST_DRIVER`, which nothing reads any more.
4. **Real browsers:** a quick pass in Safari, Firefox, iOS Safari and one
   Android browser (`setup-browsers`). The automated tests only run in Chrome.

## Deploy

```sh
git pull
composer install --no-dev     # needed on the first deploy on Laravel 13
php artisan optimize:clear
```

- Make sure `storage/app/.glide-cache` exists and the web server can write to
  it (`dp-glide`).
- There are **no migrations to run**. The edited migrations only matter for
  fresh installs.
- The built assets are committed in `public/build`; nothing to build on the
  server.

## After the deploy

- **Quick check (`dp-smoke-pub`, `dp-smoke-admin`):**
  - Home, both teams, one member, Assistenz and Kontakt in de / fr / en, with
    images.
  - Admin login, one text edit, one image upload with crop, then revert.
- **Logs:** `storage/logs` shows no new errors after the check (`dp-logs`).
- **Read-only comparison:** run `npm run test:visual` locally. It compares the
  local copy with the new production site (GET requests only). It should now
  pass even without the masks for the hero and the top-left crops.
- **Once it's stable:** remove `storage/app/public/cache/`, the old image
  cache (`dp-cleanup`).

## Content (can follow the deploy)

- **French meta description:** `config/seo.php` → `description_fr` is a
  translation of the German text and needs the client's approval.
- **Datenschutz in FR and EN:** still empty, so those pages show the German
  text until the client's translations are entered in the admin (Kontakt →
  Datenschutz, FR / EN tabs) (`ak-privacy`).

## Behaviour changes to tell the editors

- **Deleting an image** in the admin now also deletes the file from the server
  (including when its member or team is deleted). This can't be undone.
  Files under Dateien are still kept, because texts may link to them.
- **Unpublished pages** (Home, Team, Assistenz, Kontakt, team members) now
  answer 404 instead of staying reachable by URL.
- **Uploads** are checked on the server too: images jpg / png up to 8 MB,
  files pdf up to 16 MB.

## Background

- `08-test-plan.md`: the QA suite, how to run it, findings F1–F21 and their
  fixes.
- `06-progress.md`: what the upgrade changed and earlier deploy notes.
