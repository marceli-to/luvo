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

### Not yet verified

- **Admin** (login, CRUD, upload, crop save, reorder): needs a login.
  The compiled Vue 2 bundle in `public/assets` still talks to the same API.

### Left for the user (deletion was blocked by the permission classifier)

Unreferenced now, safe to remove:

```
git rm -r app/Filters config/dompdf.php config/image.php config/image-cache.php \
  app/Http/Kernel.php app/Console/Kernel.php app/Exceptions/Handler.php \
  app/Providers/AuthServiceProvider.php app/Providers/BroadcastServiceProvider.php \
  app/Providers/EventServiceProvider.php app/Providers/RouteServiceProvider.php \
  app/Http/Middleware/CheckForMaintenanceMode.php app/Http/Middleware/EncryptCookies.php \
  app/Http/Middleware/TrimStrings.php app/Http/Middleware/TrustHosts.php \
  app/Http/Middleware/TrustProxies.php app/Http/Middleware/VerifyCsrfToken.php \
  app/Http/Middleware/Authenticate.php
```

After go-live: `storage/app/public/cache/` (old image-cache output) can go.

### Open

- Home hero ignores its saved crop (`home/index.blade.php` passes no coords).
  Not among the agreed fixes; client's call.

## Deploy notes (so far)

- `composer install --no-dev` on the server (vendor is not in git).
- Glide cache lives in `storage/app/.glide-cache` (writable, not backed up).
- First view of each image variant renders it (~0.2–0.7 s for AVIF).

## Frontend: next

Vue 2 → 3 + Mix → Vite. Reference for the Vite switch:
`github.com/marceli-to/generalplaner-ag.ch` (done there 2026-09-30).
