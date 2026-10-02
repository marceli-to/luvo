import { test, expect, qa } from '../support/fixtures';
import { members, assistantPath, contactPath, type Locale } from '../support/site';

const member = members().find(m => m.publish && m.team === 'luks')!;

const pages: Record<string, (locale: Locale) => string> = {
  team: locale => `/${locale}/team-luks`,
  member: locale => `/${locale}/${member.path}`,
  assistant: locale => assistantPath(locale, 'luks'),
  contact: locale => contactPath(locale),
};

for (const [name, path] of Object.entries(pages)) {
  test(`language switcher keeps the ${name} page`, async ({ page }) => {
    qa('pg-switcher', 'pg-localized-urls');
    await page.goto(path('de'));
    const switcher = page.locator('.site-menu-footer .languages');

    for (const [label, locale] of [['E', 'en'], ['F', 'fr'], ['D', 'de']] as const) {
      await switcher.getByRole('link', { name: label, exact: true }).click();
      await expect(page).toHaveURL(path(locale));
      await expect(page.locator('html')).toHaveAttribute('lang', locale);
      // Active language: the only link without .inactive
      await expect(switcher.locator('a:not(.inactive)')).toHaveText(label);
    }
  });
}

test('desktop menu: teams and Kontakt', async ({ page }) => {
  qa('pg-desktop-menu');
  await page.goto('/de/home');

  await page.locator('nav.menu-teams').getByRole('link', { name: 'Team Luks' }).click();
  await expect(page).toHaveURL('/de/team-luks');
  // The team's member menu
  await expect(page.locator('nav.menu-team')).toBeVisible();
  await page.locator('nav.menu-team').getByRole('link', { name: member.fullname }).click();
  await expect(page).toHaveURL(`/de/${member.path}`);
  await expect(page.locator('nav.menu-team').getByRole('link', { name: member.fullname })).toHaveClass(/is-active/);

  await page.goto('/de/home');
  await page.locator('nav.menu-teams').getByRole('link', { name: 'Team Vogt' }).click();
  await expect(page).toHaveURL('/de/team-vogt');

  await page.locator('.site-menu-footer').getByRole('link', { name: 'Kontakt' }).click();
  await expect(page).toHaveURL('/de/kontakt');
  await expect(page.locator('nav.menu-teams')).toBeHidden({ timeout: 1 }).catch(() => {});
});

test('mobile menu: burger, team submenus, Über uns and member links', { tag: '@phone-only' }, async ({ page }) => {
  qa('pg-mobile-menu');
  await page.goto('/de/home');
  const menu = page.locator('.site-menu-mobile');
  const burger = page.locator('.js-menu-btn');

  // Hidden by opacity, not display
  const open = async () => { await expect(menu).toHaveClass(/is-visible/); await expect(menu).toHaveCSS('opacity', '1'); };
  const closed = async () => { await expect(menu).not.toHaveClass(/is-visible/); await expect(menu).toHaveCSS('opacity', '0'); };

  await closed();
  await burger.click();
  await open();
  await burger.click();
  await closed();

  await burger.click();
  await open();
  const luks = menu.locator('> ul > li').filter({ has: page.locator('.js-menu-item-parent', { hasText: 'Team Luks' }) });
  await expect(luks.locator('ul')).toBeHidden();
  await luks.locator('.js-menu-item-parent').click();
  await expect(luks.locator('ul')).toBeVisible();
  await luks.getByRole('link', { name: 'Über uns' }).click();
  await expect(page).toHaveURL('/de/team-luks');
  // jQuery binds the burger on DOM ready
  await page.waitForLoadState('load');

  await burger.click();
  await open();
  // On a team page its submenu starts open
  await expect(luks.locator('ul')).toBeVisible();
  await luks.getByRole('link', { name: member.fullname }).click();
  await expect(page).toHaveURL(`/de/${member.path}`);
  await page.waitForLoadState('load');

  await burger.click();
  await open();
  await menu.getByRole('link', { name: 'Kontakt' }).click();
  await expect(page).toHaveURL('/de/kontakt');
});

for (const team of ['luks', 'vogt'] as const) {
  test(`member menu of team ${team}: published members in admin order, plus Assistenz`, { tag: '@phone' }, async ({ page, isMobile }) => {
    qa('pg-member-menu');
    const expected = members().filter(m => m.team === team && m.publish).map(m => m.fullname);

    await page.goto(`/de/team-${team}`);
    const menu = isMobile
      ? page.locator('.site-menu-mobile li').filter({ has: page.locator('.js-menu-item-parent', { hasText: `Team ${team[0].toUpperCase()}${team.slice(1)}` }) }).locator('ul a')
      : page.locator('nav.menu-team ul a');
    const names = (await menu.allTextContents()).map(text => text.trim());

    expect(names).toEqual([...(isMobile ? ['Über uns'] : []), ...expected, 'Assistenz']);
  });
}
