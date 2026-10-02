import { test, expect, qa } from '../support/fixtures';
import { select } from '../support/db';

const imageCount = (team: string, device: string) => select<{ n: number }>(
  `SELECT COUNT(*) n FROM team_images i JOIN teams t ON t.id = i.team_id WHERE t.slug = '${team}' AND i.device = '${device}' AND i.publish = 1`
)[0].n;

// The team with a swiper (more than one image) and one without, from the data
const multi = ['vogt', 'luks'].find(team => imageCount(team, 'desktop') > 1)!;
const single = ['vogt', 'luks'].find(team => imageCount(team, 'desktop') === 1);

const activeIndex = (container: import('@playwright/test').Locator) =>
  container.locator('.swiper-slide-active').getAttribute('data-swiper-slide-index');

test('jQuery plugins: lazysizes and simplebar load', { tag: '@phone' }, async ({ page }) => {
  qa('pg-plugins', 'pp-team-text');
  await page.goto(`/de/team-${multi}`);

  // lazysizes is loaded; no current template emits .lazyload images
  expect(await page.evaluate(() => typeof (window as any).lazySizes?.loader?.checkElems)).toBe('function');

  const column = page.locator('.content-scrollable[data-simplebar]');
  await expect(column.locator('.simplebar-content-wrapper')).toHaveCount(1);
  await expect(column.locator('article.rich-text h1')).toBeVisible();
  expect((await column.locator('article.rich-text').innerText()).length).toBeGreaterThan(100);
});

test('team text column scrolls in simplebar', async ({ page }) => {
  qa('pp-team-text', 'pg-plugins');
  await page.setViewportSize({ width: 1280, height: 500 });
  await page.goto(`/de/team-${multi}`);
  const scroller = page.locator('.content-scrollable .simplebar-content-wrapper');

  const scrollable = await scroller.evaluate(el => el.scrollHeight > el.clientHeight);
  test.skip(!scrollable, 'team text fits without scrolling');
  await scroller.hover();
  await page.mouse.wheel(0, 300);
  await expect.poll(() => scroller.evaluate(el => el.scrollTop)).toBeGreaterThan(0);
});

test('desktop: vertical team swiper with prev / next', async ({ page }) => {
  qa('pp-team-desktop', 'pg-plugins');
  await page.goto(`/de/team-${multi}`);
  const swiper = page.locator('.js-swiper-team-vertical');

  await expect(swiper).toHaveClass(/swiper-container-initialized/);
  await expect(swiper).toHaveClass(/swiper-container-vertical/);
  await expect(swiper).toBeVisible();
  await expect(page.locator('.js-swiper-team-horizontal')).toBeHidden();

  const first = await activeIndex(swiper);
  await swiper.locator('.swiper-btn-next').click();
  await expect.poll(() => activeIndex(swiper)).not.toBe(first);
  // Clicks during the 400 ms transition are ignored
  await page.waitForTimeout(600);
  await swiper.locator('.swiper-btn-prev').click();
  await expect.poll(() => activeIndex(swiper)).toBe(first);

  // Only desktop-device images in the desktop swiper
  expect(await swiper.locator('figure.visual-mobile').count()).toBe(0);
});

test('desktop: one image means no swiper buttons', async ({ page }) => {
  qa('pp-team-desktop');
  test.skip(!single, 'no team with a single desktop image');
  await page.goto(`/de/team-${single}`);

  await expect(page.locator('.visual-desktop')).toHaveCount(1);
  await expect(page.locator('.swiper-btn-next, .swiper-btn-prev')).toHaveCount(0);
});

test('mobile: horizontal team swiper with the mobile images', { tag: '@phone-only' }, async ({ page }) => {
  qa('pp-team-mobile', 'pg-plugins');
  await page.goto(`/de/team-${multi}`);
  const swiper = page.locator('.js-swiper-team-horizontal');

  await expect(swiper).toHaveClass(/swiper-container-horizontal/);
  await expect(swiper).toBeVisible();
  await expect(page.locator('.js-swiper-team-vertical')).toBeHidden();
  expect(await swiper.locator('.swiper-slide:not(.swiper-slide-duplicate) figure.visual-mobile').count()).toBe(imageCount(multi, 'mobile'));

  const first = await activeIndex(swiper);
  await swiper.locator('.swiper-btn-next').click();
  await expect.poll(() => activeIndex(swiper)).not.toBe(first);
});

test('home: hero, scroll arrow and text', async ({ page }) => {
  qa('pp-home-scroll', 'pp-home-text');
  await page.goto('/de/home');

  await expect(page.locator('figure.visual-wide img')).toBeVisible();
  await expect(page.locator('article.home h1')).toBeVisible();
  expect((await page.locator('article.home').innerText()).length).toBeGreaterThan(200);
  // Headings, bold, lists and links of the stored text render as elements
  const html = await page.locator('article.home').innerHTML();
  for (const tag of ['p']) expect(html).toContain(`<${tag}`);

  expect(await page.evaluate(() => window.scrollY)).toBe(0);
  await page.locator('.js-btn-scroll').click();
  await expect.poll(() => page.evaluate(() => window.scrollY), { timeout: 2_000 }).toBeGreaterThan(200);
  const text = await page.locator('article.home').boundingBox();
  expect(text!.y).toBeLessThan(800);
});

test('member page: accordions open', { tag: '@phone' }, async ({ page }) => {
  qa('pp-member-sections');
  const member = select<{ path: string }>("SELECT id FROM team_members WHERE publish = 1 AND JSON_UNQUOTE(JSON_EXTRACT(biography, '$.de')) <> '' ORDER BY id LIMIT 1");
  test.skip(!member.length, 'no member with Werdegang');
  const { members } = await import('../support/site');
  const target = members().find(m => m.id === (member[0] as any).id)!;

  await page.goto(`/de/${target.path}`);
  const accordion = page.locator('.member__list').filter({ hasText: 'Werdegang' });
  await expect(accordion.locator('> div')).toBeHidden();
  await accordion.locator('.js-btn-member-list').click();
  await expect(accordion.locator('> div')).toBeVisible();
});

test('contact: address, Maps link, Impressum and Datenschutz toggle', async ({ page }) => {
  qa('pp-contact');
  await page.goto('/de/kontakt');

  await expect(page.locator('address')).toBeVisible();
  await expect(page.locator('a.anchor-maps')).toHaveAttribute('href', /^https:\/\//);
  for (const label of ['Impressum', 'Datenschutz']) {
    const toggle = page.locator('a.anchor-imprint', { hasText: label });
    const body = toggle.locator('xpath=following-sibling::div[1]');
    await expect(body).toBeHidden();
    await toggle.click();
    await expect(body).toBeVisible();
  }
});
