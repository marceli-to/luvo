import { test, expect, qa } from '../support/fixtures';
import { editor, open, row, save, toast, typeInto } from '../support/admin';
import { snapshot, select } from '../support/db';

test('double-clicking Save does not create duplicates', async ({ page, api }) => {
  qa('rb-double');
  const title = `QA-Doppelklick ${Date.now()}`;
  try {
    await open(page, '/administration/home/create');
    await row(page, 'Titel').locator('input').fill(title);
    await typeInto(editor(page, 'Text'), 'QA-Text');
    await page.locator('footer.module-footer button[type=submit]').dblclick();
    await expect(toast(page, 'Daten erfasst!').first()).toBeVisible();
    await page.waitForTimeout(1_000);
    expect(select(`SELECT id FROM home WHERE JSON_UNQUOTE(JSON_EXTRACT(title, '$.de')) = '${title}'`)).toHaveLength(1);
  }
  finally {
    for (const home of (await api.get('/api/home')).data.filter((h: any) => h.title.de === title)) {
      await api.delete(`/api/home/${home.id}`);
    }
  }
});

test('on a Slow 4G connection loading indicators show and saving works', async ({ page, api, context }) => {
  qa('rb-slow');
  test.setTimeout(120_000);
  const member = (await api.get('/api/team/members')).data[0];
  const restore = snapshot('team_members', [member.id]);
  try {
    const cdp = await context.newCDPSession(page);
    // Chrome DevTools' "Slow 4G" preset
    await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: 562.5, downloadThroughput: 1.44 * 1024 * 1024 / 8, uploadThroughput: 675 * 1024 / 8 });

    await page.goto(`/administration/team/member/edit/${member.id}`);
    await expect(page.locator('.loading-indicator')).toBeVisible();
    await expect(page.locator('.loading-indicator')).toHaveCount(0, { timeout: 60_000 });
    await expect(row(page, 'Vorname').locator('input')).toHaveValue(member.firstname);

    const submit = page.locator('footer.module-footer button[type=submit]');
    const response = page.waitForResponse(r => r.request().method() === 'PUT');
    await submit.click();
    await expect(page.locator('.loading-indicator')).toBeVisible();
    expect((await response).status()).toBe(200);
    await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible({ timeout: 30_000 });
  }
  finally {
    restore();
  }
});

test('umlauts, guillemets, & and long words save and display everywhere', async ({ page, api }) => {
  qa('rb-special');
  const special = 'QA-Ümläute «Guillemets» & Donaudampfschifffahrtsgesellschaftskapitänsmütze';
  const restore = snapshot('home', [1]);
  try {
    await open(page, '/administration/home/edit/1');
    await row(page, 'Titel').locator('input').fill(special);
    await typeInto(editor(page, 'Text'), special);
    await save(page);

    const stored = await api.get('/api/home/1');
    expect(stored.title.de).toBe(special);
    expect(stored.text.de).toBe(`<p>${special.replace('&', '&amp;')}</p>`);

    await open(page, '/administration/home/edit/1');
    await expect(row(page, 'Titel').locator('input')).toHaveValue(special);
    await expect(editor(page, 'Text')).toHaveText(special);
    await expect(page.locator('.listing__item, main.site').first()).toBeVisible();

    await page.goto('/de/home');
    await expect(page.locator('article.home h1')).toHaveText(special);
    await expect(page.locator('article.home p').first()).toHaveText(special);
    // The long word doesn't overflow the text column
    const overflow = await page.locator('article.home').evaluate(el => el.scrollWidth > el.clientWidth + 1);
    expect(overflow).toBe(false);
  }
  finally {
    restore();
  }
});
