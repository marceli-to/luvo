import fs from 'node:fs';
import path from 'node:path';
import { test as base, expect, type Page } from '@playwright/test';
import { baseURL, root } from '../e2e/support/env';
import { publicPages } from '../e2e/support/site';
import { guard, production, productionGet } from './production';
import { masksFor } from './expected-differences';
import { differingShare, psnr } from './compare';

/**
 * Local (luvo_e2e) against production, read only: layout screenshots of
 * every public page at 375 and 1280 px, and the framing of every crop.
 */
const test = base.extend({
  context: async ({ context }, use) => {
    await guard(context);
    await use(context);
  },
});

const qa = (...ids: string[]) => ids.forEach(id => test.info().annotations.push({ type: 'qa', description: id }));
const out = (...parts: string[]) => {
  const file = path.join(root, 'test-results/visual', ...parts);
  fs.mkdirSync(path.dirname(file), { recursive: true });
  return file;
};

/**
 * Layout: share of pixels (differing by more than 8 %) allowed outside the
 * masks. The images' intrinsic heights differ by a fraction of a pixel
 * (slot-size AVIF vs production's floored 2400 px JPEG), so the browser
 * rounds single text lines below them 1 CSS px differently: a vertical shift
 * of 1 CSS px is tolerated per pixel (see differingShare).
 */
const LAYOUT_TOLERANCE: Record<number, number> = { 1280: 0.005, 375: 0.005 };
// Framing, compared at 300 px: same frame ~38 dB and up, 10 px off ~22 dB
const FRAMING_MIN_PSNR = 30;

async function capture(page: Page, url: string, file: string, width: number, parts?: string[]) {
  await page.goto(url, { waitUntil: 'load' });
  // Counts as "the visitor scrolled": stops the home page's auto-scroll after 3 s
  await page.evaluate(() => window.dispatchEvent(new Event('scroll')));
  await page.addStyleTag({ content: '*, *::before, *::after { transition: none !important; animation: none !important; caret-color: transparent !important; }' });
  await page.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 500) {
      window.scrollTo(0, y);
      await new Promise(resolve => setTimeout(resolve, 50));
    }
    window.scrollTo(0, 0);
    document.querySelectorAll<any>('.swiper-container').forEach(container => {
      const swiper = container.swiper;
      swiper?.autoplay?.stop();
      swiper?.slideToLoop?.(0, 0);
    });
    await document.fonts.ready;
    await Promise.all([...document.images].map(img => img.complete ? null : new Promise(resolve => { img.onload = img.onerror = resolve; })));
  });
  await page.waitForLoadState('networkidle');
  const masks = masksFor(url, width).map(difference => page.locator(difference.selector));
  if (!parts) {
    await page.screenshot({ path: file, fullPage: true, mask: masks, animations: 'disabled' });
    return [file];
  }
  const files = [];
  for (const [index, selector] of parts.entries()) {
    const part = file.replace(/\.png$/, `-part${index + 1}.png`);
    await page.locator(selector).first().screenshot({ path: part, mask: masks, animations: 'disabled' });
    files.push(part);
  }
  return files;
}

for (const target of publicPages()) {
  test(`layout: ${target.name}`, async ({ page }, testInfo) => {
    qa('pg-visual', 'pp-home-text');
    const width = testInfo.project.use.viewport!.width;
    const local = out(`${width}`, `${target.name}-local.png`);
    const remote = out(`${width}`, `${target.name}-production.png`);
    const diff = out(`${width}`, `${target.name}-diff.png`);

    const differences = masksFor(target.url, width);
    const parts = differences.find(difference => difference.parts)?.parts;
    const locals = await capture(page, `${baseURL}${target.url}`, local, width, parts);
    const remotes = await capture(page, `${production}${target.url}`, remote, width, parts);

    for (const difference of differences) {
      testInfo.annotations.push({ type: 'expected difference', description: difference.reason });
    }
    for (const [index, file] of locals.entries()) {
      const share = differingShare(file, remotes[index], diff.replace(/\.png$/, `-${index + 1}.png`), testInfo.project.use.deviceScaleFactor ?? 1);
      testInfo.annotations.push({ type: 'differing pixels', description: `${path.basename(file)}: ${(share * 100).toFixed(3)} %` });
      if (share > LAYOUT_TOLERANCE[width]) {
        await testInfo.attach('local', { path: file, contentType: 'image/png' });
        await testInfo.attach('production', { path: remotes[index], contentType: 'image/png' });
      }
      expect.soft(share, `${target.url} ${path.basename(file)}: ${(share * 100).toFixed(2)} % of the pixels differ`).toBeLessThanOrEqual(LAYOUT_TOLERANCE[width]);
    }
  });
}

test.describe('framing', () => {
  test.skip(({ viewport }) => viewport!.width !== 1280, 'once, not per viewport');

  for (const target of publicPages().filter(p => p.locale === 'de')) {
    test(`framing: crops of ${target.name} match production`, async ({ page, request }, testInfo) => {
      qa('img-framing', 'pp-member-crops', 'pp-home-hero');
      test.setTimeout(180_000);
      await page.goto(`${baseURL}${target.url}`);
      // The JPEG variants (no type attribute) and <img> fallbacks
      const urls = [...new Set(await page.evaluate(() => [
        ...[...document.querySelectorAll('picture source:not([type])')].map(s => s.getAttribute('srcset')!),
        ...[...document.querySelectorAll('picture img')].map(i => i.getAttribute('src')!),
      ]))];

      const results: string[] = [];
      for (const url of urls) {
        const [, , , file, w, h, coords] = url.split('/');
        const name = `${file}-${w}x${h}${coords ? `-${coords}` : ''}`;
        const local = out('framing', `${name}-local.jpg`);
        fs.writeFileSync(local, await (await request.get(url)).body());

        // Production ignores the requested size (always 2400 px) but rejects
        // heights over 1600 (bug 1); the crop geometry is what's compared.
        const remoteUrl = `/img/crop/${file}/${w}/${Math.min(Number(h), 1600)}${coords ? `/${coords}` : ''}`;
        const remote = await productionGet(`${production}${remoteUrl}`);
        expect(remote.status, `production ${remoteUrl}`).toBe(200);
        const remoteFile = out('framing', `${name}-production.jpg`);
        fs.writeFileSync(remoteFile, remote.body);

        const value = psnr(local, remoteFile, out('framing', `${name}-300px.png`));
        results.push(`${url}: ${value.toFixed(1)} dB`);
        expect.soft(value, `${url} framing vs production`).toBeGreaterThanOrEqual(FRAMING_MIN_PSNR);
      }
      testInfo.annotations.push({ type: 'psnr', description: results.join('\n') });
    });
  }
});
