# Image pipeline: image-cache → Glide (evaluated 2026-09-30)

Question: can the Glide pipeline from strut.ch (`league/glide` 4.1,
`intervention/image` 4) replace `marceli-to/image-cache` with crops applied 1:1?

**Answer: yes.** Verified against production data.

## Method

1. Loaded the production DB + `storage/app` locally.
2. Crawled every public page (de) and collected all 60 `/img/crop/...` URLs the
   live site actually emits.
3. Fetched each from the local site (old pipeline) **and** from production.
   Local and production outputs match in size for all 55 that succeed, so local
   is a valid reference.
4. Rendered the same 55 through Glide (GD driver, q=75 like Intervention's
   default) and compared with ImageMagick.

## Result

| | |
|---|---|
| Same output dimensions | **55 / 55** |
| Pixel-identical | **27 / 55** |
| Rest | PSNR ≥ 52.5 dB (JPEG noise). A 1 px shift scores ~35 dB, so the crop geometry is identical. |

Harness: `.rewrite/tools/glide-render.php` (paths point at a scratch dir; adjust).

## Rules to port exactly

The old `/img/crop/{file}/{maxW}/{maxH}/{coords?}` route behaves like this.
The replacement route must keep the URL shape and reproduce:

1. **Crop** with `coords` = `w,h,x,y` (ints). Skip if empty or `0,0,0,0`.
   Glide's `crop=w,h,x,y` has the same order and also int-casts.
2. **Scale**: the requested `maxW`/`maxH` are **ignored**. `Crop::$maxSize`
   defaults to 2400 and wins, so the longer side of the (cropped) image is
   scaled *down* to 2400. Glide equivalent:
   `fit=max`, longer side = 2400, **other side = 99999**. Passing only one side
   makes Glide floor the derived dimension and fit inside it → 1 px short
   (hit 7 of 55 images before the fix).
3. Both pipelines auto-orient from EXIF before cropping. No difference.

## Existing bugs found (live on production today)

1. **Broken image on team member pages on tall screens.**
   `member.blade.php:25` requests `/1600/1920/...` for `min-height: 900px`.
   image-cache rejects `maxHeight > 1600` with **HTTP 400** (confirmed on
   production for all 5 member pages). Browsers pick that `<source>` and show
   a broken image; there is no fallback.
2. **Every crop is served at up to 2400 px**, whatever size the `<picture>`
   asks for (900×600 slots get 2400×1600). Consequence of rule 2 above.
3. **6 saved crops are ignored** (1 contact, 3 team, 2 team member images).
   `App\View\Components\Picture` only emits coords when `x` or `y` is non-zero,
   so a crop anchored at the top-left edge is dropped.
4. Coords from the DB are doubles; the route only accepts `\d+`. No fractional
   values exist in the data today, but one would 400.

A strict 1:1 port keeps 2–4. Bug 1 should be fixed regardless.

## Open decisions

- Fix 2 (serve requested sizes + WebP/AVIF) and 3 (honour all saved crops)?
  Both change what visitors see/download, so they are the client's call.
- **Server driver:** strut uses Imagick. luvo's host is unverified. GD works
  (that's what was tested). AVIF/WebP need GD/Imagick built with them; check
  on the server before promising modern formats.

## Decisions (2026-09-30)

- **Replace image-cache with Glide.** This removes the only backend blocker:
  `marceli-to/image-cache` no longer needs an L13 release.
- **Fix bug 2:** serve the size the `<picture>` asks for, plus WebP/AVIF.
- **Fix bug 3:** honour every saved crop, including ones anchored at x=0,y=0.
- Bug 1 goes away with the new route (no arbitrary 1600 height cap).
- **Driver:** Imagick. Production is the same server as strut.ch, whose
  deployment requires Imagick with AVIF + WebP.
- Guard the new route: only whitelisted sizes/formats, so arbitrary query
  params can't fill the cache (the old route had no such risk beyond coords).
