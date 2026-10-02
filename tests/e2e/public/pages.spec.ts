import { test, expect, qa } from '../support/fixtures';
import { publicPages } from '../support/site';

/**
 * Every public page in de / fr / en, at 1280 and 375 px: loads without
 * console errors or failed requests (strict fixture), right <html lang>,
 * logo links to the home page of the current language.
 */
for (const page of publicPages()) {
  test(`${page.name} loads cleanly`, { tag: '@phone' }, async ({ page: browser }) => {
    qa('pg-console', 'pg-lang-attr', 'pg-logo', 'pg-home-urls');
    if (page.type === 'member') qa('pp-member-all');

    const response = await browser.goto(page.url, { waitUntil: 'networkidle' });
    expect(response?.status()).toBe(200);
    await expect(browser.locator('html')).toHaveAttribute('lang', page.locale);

    const logos = browser.locator('a.logo');
    expect(await logos.count()).toBeGreaterThan(0);
    for (const href of await logos.evaluateAll(links => links.map(a => (a as HTMLAnchorElement).pathname))) {
      expect(href).toBe(`/${page.locale}/home`);
    }

    // Scroll through to load everything a visitor would load
    await browser.evaluate(async () => {
      for (let y = 0; y < document.body.scrollHeight; y += 400) {
        window.scrollTo(0, y);
        await new Promise(resolve => setTimeout(resolve, 30));
      }
    });
    await browser.waitForLoadState('networkidle');
  });
}

test('home URLs without a language load', async ({ page }) => {
  qa('pg-home-urls');
  for (const url of ['/', '/de', '/fr', '/en']) {
    expect((await page.goto(url))?.status(), url).toBe(200);
    await expect(page.locator('article.home h1')).toBeVisible();
  }
});

test('unknown URL shows the styled 404 page', { tag: '@phone' }, async ({ page, strict }) => {
  qa('pg-404');
  strict.allow(/404/);

  const response = await page.goto('/de/gibt-es-nicht');
  expect(response?.status()).toBe(404);
  await expect(page.locator('h1')).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Symfony');
  // Styled: the site's stylesheet is applied
  expect(await page.locator('link[rel=stylesheet][href*="/build/"]').count()).toBeGreaterThan(0);
});

test('favicons, touch icon and manifest load', async ({ page, request }) => {
  qa('pg-favicons');
  await page.goto('/de/home');

  const hrefs = await page.locator('link[rel~=icon], link[rel=apple-touch-icon], link[rel=manifest], link[rel=mask-icon]')
    .evaluateAll(links => links.map(link => (link as HTMLLinkElement).href));
  expect(hrefs.length).toBeGreaterThanOrEqual(4);

  const manifest = await (await request.get('/site.webmanifest')).json();
  for (const icon of manifest.icons ?? []) hrefs.push(new URL(icon.src, page.url()).href);
  hrefs.push(new URL('/favicon.ico', page.url()).href);

  for (const href of hrefs) {
    const response = await request.get(href);
    expect(response.status(), href).toBe(200);
  }
});
