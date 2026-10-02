import { imageSize } from 'image-size';
import type { APIRequestContext, Page } from '@playwright/test';
import { test, expect, qa } from '../support/fixtures';
import { publicPages, members } from '../support/site';

/** Every image URL a page offers: all <source srcset> and <img src>. */
async function imageUrls(page: Page): Promise<string[]> {
  return page.evaluate(() => [
    ...[...document.querySelectorAll('picture source')].map(s => s.getAttribute('srcset')!),
    ...[...document.querySelectorAll('img[src]')].map(i => i.getAttribute('src')!),
  ].filter(url => url.startsWith('/img/')));
}

/**
 * The longer side Glide must deliver for /img/crop/{file}/{w}/{h}/{coords}:
 * the crop scaled down to the slot (never up); null without coords.
 */
function expectedBound(url: URL) {
  const [, , , , w, h, coords] = url.pathname.split('/');
  if (!coords) return null;
  const [cw, ch] = coords.split(',').map(Number);
  const portrait = ch > cw;
  return { portrait, size: portrait ? Math.min(Number(h), ch) : Math.min(Number(w), cw) };
}

async function checkImage(request: APIRequestContext, href: string) {
  const url = new URL(href, 'http://local');
  const response = await request.get(href);
  expect(response.status(), href).toBe(200);

  const format = url.searchParams.get('fm') ?? 'jpeg';
  expect(response.headers()['content-type'], href).toBe(`image/${format}`);
  const body = await response.body();
  const { width, height, type } = imageSize(body);
  expect(type === 'heif' ? 'avif' : type?.replace('jpg', 'jpeg'), href).toBe(format);

  if (url.pathname.startsWith('/img/crop/')) {
    const [, , , , w, h] = url.pathname.split('/');
    const bound = expectedBound(url);
    if (bound) {
      // ±1 px: Glide rounds the derived side
      expect(Math.abs((bound.portrait ? height! : width!) - bound.size), `${href} is ${width}×${height}`).toBeLessThanOrEqual(1);
    }
    else {
      expect(width! <= Number(w) || height! <= Number(h), `${href} is ${width}×${height}`).toBe(true);
    }
    // Not the old "always 2400 px" unless the slot asks for it
    if (Number(w) < 2400 && Number(h) < 2400) {
      expect(Math.max(width!, height!), href).toBeLessThan(2400);
    }
  }
}

const pages = publicPages().filter(p => p.locale === 'de');

for (const target of pages) {
  test(`images of ${target.name}: 200, type and size match the slot`, async ({ page, request }) => {
    qa('img-formats', 'img-sizes');
    test.setTimeout(120_000);
    await page.goto(target.url);
    const urls = [...new Set(await imageUrls(page))];
    if (target.type !== 'home') expect(urls.length).toBeGreaterThan(0);

    // Three at a time: the first request of a variant renders it
    for (let i = 0; i < urls.length; i += 3) {
      await Promise.all(urls.slice(i, i + 3).map(url => checkImage(request, url)));
    }
  });
}

test('each page offers AVIF, WebP and a JPEG fallback', async ({ page }) => {
  qa('img-formats');
  await page.goto('/de/team-luks');
  const types = await page.locator('picture source[type]').evaluateAll(sources => [...new Set(sources.map(s => s.getAttribute('type')))]);
  expect(types.sort()).toEqual(['image/avif', 'image/webp']);
  expect(await page.locator('picture source:not([type])').count()).toBeGreaterThan(0);
  // Chromium picks AVIF
  const current = await page.locator('picture img').first().evaluate((img: HTMLImageElement) => img.currentSrc);
  expect(current).toContain('fm=avif');
});

for (const [width, slot] of [[700, '900/560'], [1000, '1200/750'], [1300, '1600/1000'], [1700, '2400/1500']] as const) {
  test(`home hero at ${width} px loads the ${slot} AVIF`, async ({ page }) => {
    qa('pp-home-hero-sizes', 'pp-home-hero');
    await page.setViewportSize({ width, height: 900 });
    const response = page.waitForResponse(r => r.url().includes('/img/crop/'));
    await page.goto('/de/home');

    const img = page.locator('figure.visual-wide img');
    await expect.poll(() => img.evaluate((el: HTMLImageElement) => el.complete && el.naturalWidth)).toBeGreaterThan(0);
    const current = await img.evaluate((el: HTMLImageElement) => el.currentSrc);
    expect(current).toContain(`/${slot}`);
    expect(current).toContain('fm=avif');
    expect((await response).headers()['content-type']).toBe('image/avif');
  });
}

test('member image loads on a tall window', { tag: '@tall-only' }, async ({ page }) => {
  qa('pp-member-tall');
  for (const member of members().filter(m => m.publish)) {
    await page.goto(`/de/${member.path}`);
    const img = page.locator('figure.visual-desktop img');
    if (!(await img.count())) continue;
    await expect.poll(() => img.evaluate((el: HTMLImageElement) => el.complete && el.naturalWidth), { message: member.fullname }).toBeGreaterThan(0);
    expect(await img.evaluate((el: HTMLImageElement) => el.currentSrc), member.fullname).toContain('/1600/1920');
  }
});

test('original and thumbnail of an upload', async ({ page, request }) => {
  qa('img-original-thumb');
  await page.goto('/de/team-luks');
  const name = new URL((await imageUrls(page))[0], 'http://local').pathname.split('/')[3];

  const original = await request.get(`/img/original/${name}`);
  expect(original.status()).toBe(200);
  const thumbnail = await request.get(`/img/thumbnail/${name}`);
  expect(thumbnail.status()).toBe(200);
  expect(imageSize(await thumbnail.body())).toMatchObject({ width: 300, height: 300 });
});
