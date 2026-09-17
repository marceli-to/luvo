# Backend: Laravel 11 → 13

## Target

`laravel/framework` **v13.32.0** was latest at survey time.
Requires **PHP ^8.3** and **`nesbot/carbon` ^3.8.4**.

Currently: `composer.json` declares `"php": "^8.1"`, `"nesbot/carbon": "^2.37"`
(locked at 2.73.0). Local CLI is PHP 8.4.22, so local dev is fine.

## Dependency audit — verified against Packagist 2026-09-17

| Package | Locked now | L13-ready release | Notes |
|---|---|---|---|
| `chinleung/laravel-locales` | v2.2.0 | **v3.0.0** | major bump |
| `chinleung/laravel-multilingual-routes` | v4.2.1 | **v6.0.1** | **v4→v6, two majors — main unknown** |
| `spatie/laravel-translatable` | 6.11.4 | **6.14.1** | minor, painless |
| `intervention/image-laravel` | 1.5.5 | **4.1.1** | big renumber but v3 image API already in use |
| `laravel/sanctum` | v4.0.8 | **v4.3.3** | minor |
| `laravel/ui` | v4.6.1 | **v4.6.3** | minor; only used for `Auth::routes()` |
| `laravel/tinker` | 2.x | **v3.0.2** | major |
| `intervention/image` | 3.11.2 | already v3 ✅ | pinned as `"*"` — tighten to `^3.11` |
| **`marceli-to/image-cache`** | v1.4.3 | **none — caps at `illuminate ^10\|^11`** | **BLOCKER** |

### The one blocker

`marceli-to/image-cache` v1.4.3 requires `illuminate/filesystem ^10.0|^11.0`
and `illuminate/support ^10.0|^11.0`. It's **your own package** — bump the
constraint to `^12.0|^13.0`, smoke-test against L13, tag v1.5.0. Its
`intervention/image ^3.0` requirement is already correct.

### Good news found during the survey

The image pipeline is **already on Intervention v3**. The v2-API filter classes
in `app/Filters/` are dead code (see `01-inventory.md`). No image rewrite needed.

## Structural item: legacy skeleton

The app runs Laravel 11 with the **Laravel 8-era skeleton**:

- `bootstrap/app.php` — old style, manually binds `App\Http\Kernel`,
  `App\Console\Kernel`, `App\Exceptions\Handler`
- `app/Http/Kernel.php` — 9 middleware, all thin
- `app/Providers/` — App, Auth, Broadcast, Event, Route service providers
- `app/Exceptions/Handler.php`

Migrate to the slim `bootstrap/app.php` (`Application::configure()->withRouting()
->withMiddleware()->withExceptions()`). It's ~half a day given how thin the
middleware is, and it de-risks future upgrades rather than betting on how long
backwards compat survives.

Middleware to carry over: `TrustProxies`, `CheckForMaintenanceMode`,
`TrimStrings`, `EncryptCookies`, `VerifyCsrfToken`, `Authenticate` (`auth`),
`RedirectIfAuthenticated` (`guest`), **`CheckRole` (`role`)** ← the custom one,
used by the `administration/{any?}` route. `TrustHosts` is commented out.

## Step plan

1. **Verify PHP 8.3+ on the production host.** Hard gate. Do this first.
2. Delete `app/Filters/`. Drop `symfony/polyfill-php72`.
3. Bump + retag `marceli-to/image-cache` for `illuminate ^12|^13`.
4. `composer.json`: `php: ^8.3`, `laravel/framework: ^13.0`,
   `nesbot/carbon: ^3.0`, tighten `intervention/image: ^3.11`, bump the rest
   per the table. Resolve whatever falls out.
5. Migrate the skeleton to `bootstrap/app.php`.
6. Diff `config/*` against a fresh L13 skeleton. Custom configs to preserve:
   `luvo.php`, `seo.php`, `image-cache.php`, `laravel-multilingual-routes.php`,
   `locales.php`, `translatable.php`.
   Note: `config/dompdf.php` is an **orphan** — dompdf is not in `composer.lock`
   at all. Delete it.
7. `chinleung/laravel-multilingual-routes` v4 → v6: read its upgrade guide.
   Surface is small — 7 `Route::multilingual()` calls + ~11 `localized_route()`
   uses in blades.
8. Carbon 2 → 3: only 2 call sites in `app/`, but check vendor fallout.
9. Smoke test: every public route in all 3 locales, login, and every admin
   CRUD path (see `03-frontend-vue3.md` for the screen list).
