# Frontend: Vue 2.7 → Vue 3

Scope is `resources/js/dashboard/` only. The public site (`resources/js/web/`)
is plain JS + jQuery + Bootstrap 4 and stays as it is.

## The code itself is clean

Scanned all 51 components. **Zero hits** for every expensive Vue 2 pattern:

| Pattern | Hits |
|---|---|
| `$listeners` | 0 |
| `$children` | 0 |
| `$scopedSlots` | 0 |
| `slot=` / `slot-scope` | 0 |
| `filters:` option blocks | 0 |
| event bus (`$on(` / `$off(` / `$root.$emit`) | 0 |
| `$set(` / `$delete(` | 0 |
| functional components | 0 |

What *does* need touching:

- **6 × `.sync`** → `v-model:publish`. All the same prop, all in `*/Form.vue`:
  `assistant:93`, `home:82`, `team_member:264`, `contact:110`,
  `publication:34`, `team:89`
- **3 × template filter pipes** → methods. All `capitalizeFirst()` in
  `views/team/Index.vue` lines 21, 57, 90
- 94 `v-model` uses — mostly fine; custom-component ones need the
  `value`/`input` → `modelValue`/`update:modelValue` rename
- 23 `mixins:` declarations — **Vue 3 still supports mixins in Options API**.
  Keep them. Do not refactor to composables.

## Dependency migration table

| Package | Current | Move to | Files | Effort |
|---|---|---|---|---|
| **`vue2-dropzone`** | ^3.6.0 | **no Vue 3 port — rewrite** | 2 | **highest risk** |
| `vue-feather-icons` | ^5.1.0 | `lucide-vue-next` | **24** | mechanical, wide |
| `vuedraggable` | ^2.24.3 | `vuedraggable@^4` | 9 | slot API changed |
| `@tinymce/tinymce-vue` | ^3.2.8 | `^6` | 8 | easy |
| `vue-advanced-cropper` | ^0.16.5 | latest (**has Vue 3 support**) | 6 | easy |
| `vue-notification` | ^1.3.20 | `@kyvg/vue3-notification` | 1 (`app.js`) | drop-in |
| `vue-router` | ^3.6.5 | `^4` | `config/routes.js`, 174 LOC | mechanical |
| `vuex` | ^3.6.2 | **delete** | `config/store.js` | one state field (`user`) |
| `vue-moment` | ^4.1.0 | **delete** — use `dayjs` | `app.js` | free |
| `vue-axios` + `vue-axios-interceptors` | — | **delete** — plain axios interceptors | `app.js` | free |
| `cleave.js` | ^1.6.0 | **delete** — `v-cleave` used **0 times** | `app.js` | free |
| `vuejs-datepicker` | ^1.6.2 | **delete** — unused | — | free |
| `vue-the-mask` | ^0.11.1 | **delete** — unused | — | free |
| `laravel-mix` | ^6.0.49 | **Vite** | `webpack.mix.js`, blade | 0.5 day |

### Exact file lists

**`vue2-dropzone`** (2): `components/files/Upload.vue`,
`components/images/Upload.vue`

**`vue-feather-icons`** (24): `App.vue`, `components/ui/ListActions.vue`,
`components/ui/LoadingIndicator.vue`, `components/images/Actions.vue`,
`components/images/Edit.vue`, `components/files/Actions.vue`,
`views/layout/PageHeader.vue`, and the Form/Index/images-Edit files under
`views/{assistant,home,team,team_member,contact,publication,user}/`

**`vuedraggable`** (9): `views/assistant/Form.vue`,
`views/assistant/images/Edit.vue`, `views/home/images/Edit.vue`,
`views/team_member/Index.vue`, `views/team_member/Form.vue`,
`views/team_member/images/Edit.vue`, `views/contact/images/Edit.vue`,
`views/team/Index.vue`, `views/team/images/Edit.vue`

**TinyMCE** (8): `config/tiny.js`, `config/tiny-small.js`, and
`views/{assistant,home,team_member,contact,publication,team}/Form.vue`

**`vue-advanced-cropper`** (6): `components/images/Edit.vue` +
`views/{assistant,home,team_member,contact,team}/images/Edit.vue`

## Shortcut: the near-duplicates

Six `views/*/images/Edit.vue` files are ~300 LOC near-copies of each other
(assistant 297, contact 297, team_member 298, team 306, home 263) and all share
the same three mixins (`ImageUtils`, `ImageEdit`, `ImageCrop`).

**Port one carefully, then replicate across the other five.** Same for the
12-line `Create.vue` / `Edit.vue` stubs — there are 13 of them and they're
near-identical.

## Vite migration notes

Current `webpack.mix.js` produces four bundles:

| Source | Output |
|---|---|
| `resources/sass/web/app.scss` | `public/assets/css` |
| `resources/js/web/app.js` | `public/assets/js` |
| `resources/js/dashboard/app.js` | `public/assets/dashboard/js/bundle.administration.js` |
| `resources/sass/dashboard/app.scss` | `public/assets/dashboard/css` |

Carry over: the `@` → `resources/js/dashboard/` alias, `processCssUrls: false`
(→ Vite `css.preprocessorOptions` / asset handling), and `mix.version()` in
prod (→ Vite manifest).

Blade `mix()` calls to convert to `@vite([...])` — exactly 4:

| File | Line | Asset |
|---|---|---|
| `web/partials/header.blade.php` | 21 | `assets/css/app.css` |
| `web/partials/footer.blade.php` | 11 | `assets/js/app.js` |
| `dashboards/administration/app.blade.php` | 8 | `assets/dashboard/css/app.css` |
| `dashboards/administration/app.blade.php` | 15 | `assets/dashboard/js/bundle.administration.js` |

The SPA mounts on `<div id="app-administration">` in
`resources/views/dashboards/administration/app.blade.php:12`.

Watch out: `resources/js/web/vendor/swiper.js` is an 8.7k-LOC vendored copy —
leave it as-is, just make sure Vite doesn't try to optimise it.

## `app.js` rewrite

Current `resources/js/dashboard/app.js` (75 LOC) is nearly all global plugin
registration, most of which disappears. Target shape:

- `createApp(App)` instead of `new Vue({}).$mount('#app-administration')`
- `createRouter({ history: createWebHistory(), routes })`
- axios: plain instance + interceptors, `withCredentials: true`, drop
  `vue-axios` / `vue-axios-interceptors`
- keep the two global components: `LoadingIndicator`, `Separator`
- drop: Vuex, vue-moment, cleave directive
- replace: vue-notification → `@kyvg/vue3-notification`

## Admin screens to click through for QA

dashboard · users (index/form) · media · home (index/form/images) ·
team (index/form/images) · team members (index/form/images) ·
assistant (form/images) · contact (form/images) · publication (index/form) ·
error pages (403/404)

Per screen: list, create, edit, delete, reorder (draggable), file upload,
image upload + crop, TinyMCE content, language tabs (de/fr/en), validation errors.
