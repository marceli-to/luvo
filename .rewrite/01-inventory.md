# Inventory (measured 2026-09-17, commit b2cbcf7)

## Size

| Area | Measured |
|---|---|
| `app/` PHP | 5,678 LOC across 102 files |
| Vue components | **51** `.vue` files, 5,911 LOC |
| Dashboard JS (excl. `.vue`) | ~1,300 LOC |
| Vendored `resources/js/web/vendor/swiper.js` | 8,708 LOC (leave alone) |
| Blade | 39 files, 862 LOC |
| Sass | 8,740 LOC |
| Tests | 2 example stubs, 71 LOC total — **effectively none** |

## `app/` breakdown

| Dir | Files | LOC |
|---|---|---|
| `Http/Controllers/Api` | 14 | 1,997 |
| `Observers` | 10 | 740 |
| `Models` | 14 | 488 |
| `Http/Requests` | 7 | 372 |
| `Http/Controllers` | 8 | 256 |
| `Helpers` | 4 | 90 |
| `Mail` | 1 | 55 |
| `Services` | 1 | 19 |
| `Http/Resources` | 1 | 17 |
| `Http/Middleware` | 9 | — |

Models: Assistant, AssistantImage, Base, Contact, ContactImage, File, Home,
HomeImage, Publication, Team, TeamImage, TeamMember, TeamMemberImage, User.

**No raw SQL anywhere** (`DB::raw` / `->raw(` → 0 hits).
**Carbon appears in only 2 places** in `app/`.

## Dead code to delete

`app/Filters/Image/Template/**` — 6 classes, 235 LOC:
`Cache.php`, `Thumbnail.php`, `Large.php`, `Small.php`, `Shop/Preview.php`,
`Shop/Large.php`.

They use the **Intervention Image v2** API (`FilterInterface`, `fit()`,
`resize()` with constraint closures), all removed in v3. They are **referenced
nowhere** — grep for `App\Filters` / `Filters\Image` outside `app/Filters/`
returns zero hits. The live image pipeline is `marceli-to/image-cache`, whose
own `src/Templates/` (Crop, Huge, Large, Medium, Small, Thumbnail, XLarge,
XSmall, XXLarge) is already on Intervention v3.

Also unused and removable: `vuejs-datepicker`, `vue-the-mask`, `cleave.js`
(`v-cleave` used 0 times), `symfony/polyfill-php72`.

## Routing

7 `Route::multilingual()` routes in `routes/web.php` (home, 2 teams, team
member, 2 assistants, contact) + `Auth::routes()` from `laravel/ui` + a
catch-all `administration/{any?}` behind `auth:sanctum` + `verified` + `role:admin`.
`localized_route()` used across ~11 blade spots (menus, header).

Locales: de / fr / en.

## Frontend structure

- `resources/js/web/` — public site. Plain JS, jQuery, Bootstrap 4, swiper,
  lazysizes, simplebar, fancyapps. **No Vue.** Out of scope.
- `resources/js/dashboard/` — the Vue 2 SPA. Everything in scope lives here.
  - `app.js` (75 LOC) — global plugin registration, the main rewrite target
  - `config/routes.js` (174 LOC) — vue-router 3
  - `config/store.js` (14 LOC) — Vuex with **one** state field (`user`)
  - `mixins/` — DateTime, ErrorHandling, Filters, Helpers
  - `components/images/mixins/` — crop, edit, utils
  - `components/files/mixins/` — edit, utils
