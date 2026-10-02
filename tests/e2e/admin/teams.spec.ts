import { test, expect, qa, type Api } from '../support/fixtures';
import { answerConfirm, dragAndDrop, editor, language, listItem, open, publishRadio, row, save, tab, toast, typeInto, heading } from '../support/admin';
import { snapshot, select } from '../support/db';
import { members } from '../support/site';

const ids = (sql: string) => select<{ id: number }>(sql).map(r => r.id);

async function createMember(api: Api, firstname: string, team = 1, publish = 1) {
  const { teamMemberId } = await api.post('/api/team/member', { firstname, name: 'QA-Test', team_id: team, publish, images: [] });
  return teamMemberId as number;
}

const createdMembers: number[] = [];
test.afterEach(async ({ api }) => {
  for (const id of createdMembers.splice(0)) await api.delete(`/api/team/member/${id}`).catch(() => {});
});

const teamsListing = (page: import('@playwright/test').Page) => page.locator('.listing:not(.is-grouped)').first();
const assistantsListing = (page: import('@playwright/test').Page) => page.locator('.listing:not(.is-grouped)').last();

test('teams list: toggle, edit, delete cancel; no create with two teams', async ({ page, api }) => {
  qa('at-teams-list');
  const restore = snapshot('teams', ids('SELECT id FROM teams'));
  try {
    await open(page, '/administration/teams');
    const luks = teamsListing(page).locator('.listing__item', { hasText: 'Team Luks' });
    const before = (await api.get('/api/team/1')).publish;

    await luks.locator('.listing__item-action a').first().click();
    await expect(toast(page, 'Status geändert')).toBeVisible();
    expect((await api.get('/api/team/1')).publish).toBe(before ? 0 : 1);

    const answered = answerConfirm(page, false);
    await luks.locator('.listing__item-action a').last().click();
    await answered;
    await expect(luks).toBeVisible();

    // "Hinzufügen" only for members (C1)
    await expect(page.locator('main.site header.content-header').first().getByRole('link', { name: 'Hinzufügen' })).toHaveCount(0);

    await luks.locator('.listing__item-action a[href*="/team/edit/"]').click();
    await expect(heading(page, 'Team bearbeiten')).toBeVisible();
  }
  finally {
    restore();
  }
});

test('team form: select, Titel and Text required in DE, FR / EN optional, publish', async ({ page, api, strict }) => {
  qa('at-team-form');
  strict.allow(/422/);
  const restore = snapshot('teams', [1]);
  try {
    await open(page, '/administration/team/edit/1');
    await expect(row(page, 'Team').locator('select option')).toHaveText(['Luks', 'Vogt']);

    await row(page, 'Titel').locator('input').fill('');
    await typeInto(editor(page, 'Text'), ' ');
    await editor(page, 'Text').press('ControlOrMeta+a');
    await editor(page, 'Text').press('Backspace');
    await save(page, 422);
    await expect(row(page, 'Titel')).toHaveClass(/has-error/);
    await expect(row(page, 'Text')).toHaveClass(/has-error/);

    await row(page, 'Titel').locator('input').fill('QA-Team Luks');
    await typeInto(editor(page, 'Text'), 'QA-Teamtext');
    await language(page, 'fr');
    await row(page, 'Titel').locator('input').fill('');
    await publishRadio(page, 1);
    await save(page);
    await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();

    const team = await api.get('/api/team/1');
    expect(team.title.de).toBe('QA-Team Luks');
    expect(team.text.de).toBe('<p>QA-Teamtext</p>');
    expect(team.publish).toBe(1);
    await page.goto('/de/team-luks');
    await expect(page.locator('article.rich-text h1')).toHaveText('QA-Team Luks');
  }
  finally {
    restore();
  }
});

test('team images: Desktop / Mobile labels, translated captions, desktop info text', async ({ page, api }) => {
  qa('at-team-images');
  const images = ids('SELECT id FROM team_images WHERE team_id = 1');
  const restoreImages = snapshot('team_images', images);
  const restoreTeam = snapshot('teams', [1]);
  try {
    await open(page, '/administration/team/edit/1');
    await tab(page, 'Bilder');
    const items = page.locator('.upload-item');
    await expect(items.locator('.image-label').first()).toHaveText(/Desktop|Mobile/);

    const desktop = items.filter({ has: page.locator('.image-label', { hasText: 'Desktop' }) }).first();
    await desktop.locator('.upload__actions a').nth(1).click();
    const overlay = page.locator('.upload-overlay-edit');
    await expect(overlay).toHaveClass(/is-visible/);
    await expect(overlay).toContainText('Info: Die Bildlegenden für die Desktop-Version');
    await overlay.locator('a.btn-primary', { hasText: 'Schliessen' }).click();

    const mobile = items.filter({ has: page.locator('.image-label', { hasText: 'Mobile' }) }).first();
    await mobile.locator('.upload__actions a').nth(1).click();
    for (const [label, value] of [['Bildlegende', 'QA-Legende DE'], ['Bildlegende (FR)', 'QA-Légende FR'], ['Bildlegende (EN)', 'QA-Caption EN']]) {
      await overlay.locator('.form-row', { has: page.locator(`label:text-is("${label}")`) }).locator('input').fill(value);
    }
    await expect(overlay.locator('figcaption')).toHaveText('QA-Legende DE');
    await page.keyboard.press('Escape');
    await expect(overlay).not.toHaveClass(/is-visible/);

    await save(page);
    const stored = (await api.get('/api/team/1')).images.map((i: any) => i.caption);
    expect(stored).toContainEqual({ de: 'QA-Legende DE', fr: 'QA-Légende FR', en: 'QA-Caption EN' });
  }
  finally {
    restoreImages();
    restoreTeam();
  }
});

test('members are grouped by team in admin order', async ({ page }) => {
  qa('at-members-groups');
  await open(page, '/administration/teams');
  const groups = page.locator('.listing.is-grouped');
  await expect(groups).toHaveCount(2);

  for (const [index, team] of [[0, 'luks'], [1, 'vogt']] as const) {
    const expected = members().filter(m => m.team === team).map(m => m.fullname);
    const names = (await groups.nth(index).locator('.listing__item-body').allTextContents()).map(text => text.split('•')[0].replace(/\s+/g, ' ').trim());
    expect(names).toEqual(expected);
  }
});

test('eye toggle inside the draggable list', async ({ page, api }) => {
  qa('at-members-toggle');
  const id = await createMember(api, `QA-Toggle${Date.now()}`, 1, 1);
  createdMembers.push(id);
  await open(page, '/administration/teams');
  const item = listItem(page, 'QA-Toggle');

  await item.locator('.listing__item-action a').first().click();
  await expect(toast(page, 'Status geändert')).toBeVisible();
  await expect(item).toHaveClass(/is-disabled/);
  expect((await api.get(`/api/team/member/${id}`)).publish).toBe(0);

  await item.locator('.listing__item-action a').first().click();
  await expect(item).not.toHaveClass(/is-disabled/);
  expect((await api.get(`/api/team/member/${id}`)).publish).toBe(1);
});

test('drag a member to a new position: kept after reload, in the API and the public menu', async ({ page, api }) => {
  qa('at-members-drag');
  const restore = snapshot('team_members', ids('SELECT id FROM team_members'));
  try {
    const stamp = Date.now();
    createdMembers.push(await createMember(api, `QA-Erste${stamp}`));
    createdMembers.push(await createMember(api, `QA-Zweite${stamp}`));
    // Put both at the end of team Luks
    const luks = (await api.get('/api/team/members')).data.filter((m: any) => m.team_id === 1);
    const ordered = [...luks.filter((m: any) => !m.firstname.startsWith('QA-')), ...createdMembers.map(id => luks.find((m: any) => m.id === id))];
    await api.post('/api/team/member/order', { members: ordered.map((m: any, order: number) => ({ id: m.id, order })) });

    await open(page, '/administration/teams');
    const group = page.locator('.listing.is-grouped').first();
    const names = async () => (await group.locator('.listing__item-body').allTextContents()).map(t => t.trim());
    const second = group.locator('.listing__item', { hasText: `QA-Zweite${stamp}` });
    const first = group.locator('.listing__item', { hasText: `QA-Erste${stamp}` });

    const saved = page.waitForResponse(r => r.url().endsWith('/api/team/member/order'));
    const strategy = await dragAndDrop(page, second, first, async () => {
      const list = await names();
      return list.findIndex(n => n.includes('QA-Zweite')) < list.findIndex(n => n.includes('QA-Erste'));
    });
    test.info().annotations.push({ type: 'drag', description: strategy });
    expect((await saved).status()).toBe(200);
    await expect(toast(page, 'Reihenfolge angepasst')).toBeVisible();

    await page.reload();
    await expect(group.locator('.listing__item').first()).toBeVisible();
    const after = await names();
    expect(after.findIndex(n => n.includes('QA-Zweite'))).toBeLessThan(after.findIndex(n => n.includes('QA-Erste')));

    const api_ = (await api.get('/api/team/members')).data.filter((m: any) => m.team_id === 1);
    expect(api_.findIndex((m: any) => m.firstname === `QA-Zweite${stamp}`)).toBeLessThan(api_.findIndex((m: any) => m.firstname === `QA-Erste${stamp}`));

    await page.goto('/de/team-luks');
    const menu = (await page.locator('nav.menu-team ul a').allTextContents()).map(t => t.trim());
    expect(menu.findIndex(n => n.startsWith('QA-Zweite'))).toBeLessThan(menu.findIndex(n => n.startsWith('QA-Erste')));
  }
  finally {
    for (const id of createdMembers.splice(0)) await api.delete(`/api/team/member/${id}`).catch(() => {});
    restore();
  }
});

test('member: create, edit, delete with confirmation', async ({ page, api }) => {
  qa('at-members-crud');
  const firstname = `QA-Neu${Date.now()}`;
  await open(page, '/administration/teams');
  await page.locator('header.content-header', { hasText: 'Mitarbeiter' }).getByRole('link', { name: 'Hinzufügen' }).click();
  await expect(heading(page, 'Mitarbeiter hinzufügen')).toBeVisible();
  await row(page, 'Team').locator('select').selectOption({ label: 'Vogt' });
  await row(page, 'Vorname').locator('input').fill(firstname);
  await row(page, 'Name').locator('input').fill('QA-Muster');
  await save(page);
  await expect(toast(page, 'Daten erfasst!')).toBeVisible();

  const member = (await api.get('/api/team/members')).data.find((m: any) => m.firstname === firstname);
  createdMembers.push(member.id);
  expect(member.team_id).toBe(2);

  const item = listItem(page, firstname);
  await item.locator('a[href*="/team/member/edit/"]').click();
  await row(page, 'Name').locator('input').fill('QA-Geändert');
  await save(page);
  await expect(listItem(page, `${firstname} QA-Geändert`)).toBeVisible();

  let answered = answerConfirm(page, false);
  await listItem(page, firstname).locator('.listing__item-action a').last().click();
  await answered;
  await expect(listItem(page, firstname)).toBeVisible();
  answered = answerConfirm(page, true);
  await listItem(page, firstname).locator('.listing__item-action a').last().click();
  await answered;
  await expect(listItem(page, firstname)).toHaveCount(0);
});

test('assistant list: toggle, edit, delete cancel; no create with two', async ({ page, api }) => {
  qa('at-assist-list');
  const restore = snapshot('assistants', ids('SELECT id FROM assistants'));
  try {
    await open(page, '/administration/teams');
    const item = assistantsListing(page).locator('.listing__item').first();
    const id = Number((await item.locator('a[href*="/assistant/edit/"]').getAttribute('href'))!.split('/').pop());
    const before = (await api.get(`/api/assistant/${id}`)).publish;

    await item.locator('.listing__item-action a').first().click();
    await expect(toast(page, 'Status geändert')).toBeVisible();
    expect((await api.get(`/api/assistant/${id}`)).publish).toBe(before ? 0 : 1);

    const answered = answerConfirm(page, false);
    await item.locator('.listing__item-action a').last().click();
    await answered;
    await expect(item).toBeVisible();
    await expect(page.locator('header.content-header', { hasText: 'Assistenz' }).getByRole('link', { name: 'Hinzufügen' })).toHaveCount(0);

    await item.locator('a[href*="/assistant/edit/"]').click();
    await expect(heading(page, 'Assistenten bearbeiten')).toBeVisible();
  }
  finally {
    restore();
  }
});
