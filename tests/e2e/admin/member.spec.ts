import { test, expect, qa, knownFinding, type Api } from '../support/fixtures';
import { answerConfirm, dragAndDrop, editor, heading, language, listItem, open, row, save, toast, typeInto } from '../support/admin';
import { members } from '../support/site';

const created: number[] = [];
test.afterEach(async ({ api }) => {
  for (const id of created.splice(0)) await api.delete(`/api/team/member/${id}`).catch(() => {});
});

async function createMember(api: Api, firstname = `QA-Person${Date.now()}`, publish = 1) {
  const { teamMemberId } = await api.post('/api/team/member', { firstname, name: 'QA-Muster', team_id: 1, publish, images: [] });
  created.push(teamMemberId);
  return { id: teamMemberId as number, firstname };
}

async function createPublication(api: Api, memberId: number, title: string, order = 0, publish = 1) {
  const { publicationId } = await api.post('/api/publication', {
    team_member_id: memberId, title: { de: title }, description: { de: `<p>${title} Beschreibung</p>` }, articles: { de: `<p>${title} Artikel</p>` }, publish,
  });
  await api.post('/api/publication/order', { publications: [{ id: publicationId, order }] });
  return publicationId as number;
}

const publicPath = (id: number) => members().find(m => m.id === id)!.path;

test('Vorname, Name required: messages and marked fields', async ({ page, strict }) => {
  qa('am-required');
  strict.allow(/422/);
  await open(page, '/administration/team/member/create');
  await save(page, 422);
  await expect(toast(page, 'Bitte alle mit * markierten Felder prüfen!', 'error')).toBeVisible();
  for (const label of ['Vorname', 'Name']) {
    await expect(row(page, label)).toHaveClass(/has-error/);
    await expect(row(page, label).locator('.is-required')).toHaveText('Pflichtfeld');
    await expect(row(page, label).locator('> label')).toHaveCSS('color', 'rgb(218, 44, 56)');
  }
  // Team has a default (Luks) in the select; its server-side rule is in ApiTest
  await expect(row(page, 'Team')).not.toHaveClass(/has-error/);
});

test('every field saves in DE / FR / EN', async ({ page, api }) => {
  qa('am-fields', 'ed-multi');
  const member = await createMember(api);
  const started = Date.now();
  await open(page, `/administration/team/member/edit/${member.id}`);
  await expect(page.locator('.ProseMirror')).toHaveCount(21);
  expect(Date.now() - started, 'member form with 21 editors loads within 5 s').toBeLessThan(5_000);

  const editors = ['Info', 'Beschreibung', 'Tätigkeitsgebiete', 'Sprachen', 'Werdegang', 'Mitgliedschaften', 'Publikationen (Liste)'];
  for (const locale of ['de', 'fr', 'en'] as const) {
    await language(page, locale);
    for (const label of editors) await typeInto(editor(page, label), `QA-${label} ${locale}`);
    await row(page, 'SEO Beschreibung').locator('textarea').fill(`QA-SEO ${locale}`);
  }
  await save(page);
  await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();

  const stored = await api.get(`/api/team/member/${member.id}`);
  const fields = { Info: 'credits', Beschreibung: 'description', Tätigkeitsgebiete: 'area', Sprachen: 'languages', Werdegang: 'biography', Mitgliedschaften: 'membership', 'Publikationen (Liste)': 'publication' };
  for (const [label, key] of Object.entries(fields)) {
    expect(stored[key], key).toEqual({ de: `<p>QA-${label} de</p>`, fr: `<p>QA-${label} fr</p>`, en: `<p>QA-${label} en</p>` });
  }
  expect(stored.meta_description).toEqual({ de: 'QA-SEO de', fr: 'QA-SEO fr', en: 'QA-SEO en' });

  // Each editor kept its own content after the reload
  await open(page, `/administration/team/member/edit/${member.id}`);
  await language(page, 'fr');
  await expect(editor(page, 'Werdegang')).toHaveText('QA-Werdegang fr');
  await expect(editor(page, 'Sprachen')).toHaveText('QA-Sprachen fr');
});

test('SEO Beschreibung is kept when creating a member', async ({ page, api }) => {
  qa('am-fields');
  knownFinding('F6', 'SEO Beschreibung is dropped on create');
  const firstname = `QA-SEO${Date.now()}`;
  await open(page, '/administration/team/member/create');
  await row(page, 'Vorname').locator('input').fill(firstname);
  await row(page, 'Name').locator('input').fill('QA-Muster');
  await row(page, 'SEO Beschreibung').locator('textarea').fill('QA-SEO beim Erfassen');
  await save(page);
  const member = (await api.get('/api/team/members')).data.find((m: any) => m.firstname === firstname);
  created.push(member.id);
  expect((await api.get(`/api/team/member/${member.id}`)).meta_description?.de).toBe('QA-SEO beim Erfassen');
});

test('changing the name updates the public URL and the member menu', async ({ page, api }) => {
  qa('am-name-url');
  const member = await createMember(api, `QA-Alt${Date.now()}`);
  const oldPath = publicPath(member.id);

  await open(page, `/administration/team/member/edit/${member.id}`);
  await row(page, 'Vorname').locator('input').fill(`QA-Neu${member.id}`);
  await save(page);

  const newPath = publicPath(member.id);
  expect(newPath).not.toBe(oldPath);
  await page.goto('/de/team-luks');
  await expect(page.locator('nav.menu-team a', { hasText: `QA-Neu${member.id} QA-Muster` })).toHaveAttribute('href', new RegExp(`/de/${newPath}$`));
  expect((await page.goto(`/de/${newPath}`))?.status()).toBe(200);
  // The id decides; the old slug still resolves
  expect((await page.goto(`/de/${oldPath}`))?.status()).toBe(200);
});

test('publication: create from the member form, required fields, per language', async ({ page, api, strict }) => {
  qa('am-pub-create');
  strict.allow(/422/);
  const member = await createMember(api);
  await open(page, `/administration/team/member/edit/${member.id}`);
  await page.locator('header.content-header', { hasText: 'Publikationen (Artikel)' }).getByRole('link', { name: 'Hinzufügen' }).click();
  await expect(heading(page, 'Publikation hinzufügen')).toBeVisible();

  await save(page, 422);
  // C3: Artikel is required as well
  await expect(row(page, 'Titel')).toHaveClass(/has-error/);
  await expect(row(page, 'Artikel')).toHaveClass(/has-error/);

  await row(page, 'Titel').locator('input').fill('QA-Publikation');
  await typeInto(editor(page, 'Beschreibung'), 'QA-Beschreibung de');
  await typeInto(editor(page, 'Artikel'), 'QA-Artikel de');
  await language(page, 'fr');
  await row(page, 'Titel').locator('input').fill('QA-Publication');
  await typeInto(editor(page, 'Artikel'), 'QA-Article fr');
  await save(page);
  await expect(heading(page, 'Mitarbeiter bearbeiten')).toBeVisible();
  await expect(page).toHaveURL(`/administration/team/member/edit/${member.id}`);

  const [publication] = (await api.get(`/api/team/member/${member.id}`)).publications;
  expect(publication.title).toMatchObject({ de: 'QA-Publikation', fr: 'QA-Publication' });
  expect(publication.description.de).toBe('<p>QA-Beschreibung de</p>');
  expect(publication.articles).toMatchObject({ de: '<p>QA-Artikel de</p>', fr: '<p>QA-Article fr</p>' });
});

test('publication: edit, toggle, delete with confirmation, back link', async ({ page, api }) => {
  qa('am-pub-edit');
  const member = await createMember(api);
  const id = await createPublication(api, member.id, 'QA-Pub Bearbeiten');
  await open(page, `/administration/team/member/edit/${member.id}`);
  const item = listItem(page, 'QA-Pub Bearbeiten');

  await item.locator('a[href*="/team/publication/edit/"]').click();
  await expect(heading(page, 'Publikation bearbeiten')).toBeVisible();
  await row(page, 'Titel').locator('input').fill('QA-Pub Geändert');
  await save(page);
  await expect(page).toHaveURL(`/administration/team/member/edit/${member.id}`);
  const changed = listItem(page, 'QA-Pub Geändert');

  await changed.locator('.listing__item-action a').first().click();
  await expect(toast(page, 'Status geändert')).toBeVisible();
  await expect(changed).toHaveClass(/is-disabled/);
  expect((await api.get(`/api/publication/${id}`)).publish).toBe(0);

  await changed.locator('a[href*="/team/publication/edit/"]').click();
  await page.getByRole('link', { name: 'Zurück' }).click();
  await expect(page).toHaveURL(`/administration/team/member/edit/${member.id}`);

  let answered = answerConfirm(page, false);
  await changed.locator('.listing__item-action a').last().click();
  await answered;
  await expect(changed).toBeVisible();
  answered = answerConfirm(page, true);
  await changed.locator('.listing__item-action a').last().click();
  await answered;
  await expect(changed).toHaveCount(0);
  expect((await api.get(`/api/team/member/${member.id}`)).publications).toEqual([]);
});

test('publications: drag into a new order; kept after reload, in the API and on the public page', async ({ page, api }) => {
  qa('am-pub-drag', 'pp-member-pubs');
  const member = await createMember(api);
  await createPublication(api, member.id, 'QA-Pub Eins', 0);
  await createPublication(api, member.id, 'QA-Pub Zwei', 1);
  await createPublication(api, member.id, 'QA-Pub Drei', 2);
  await open(page, `/administration/team/member/edit/${member.id}`);

  const titles = async () => (await page.locator('.listing .listing__item-body').allTextContents()).map(t => t.trim());
  expect(await titles()).toEqual(['QA-Pub Eins', 'QA-Pub Zwei', 'QA-Pub Drei']);

  const saved = page.waitForResponse(r => r.url().endsWith('/api/publication/order'));
  const strategy = await dragAndDrop(page, listItem(page, 'QA-Pub Drei'), listItem(page, 'QA-Pub Eins'), async () => (await titles())[0] === 'QA-Pub Drei');
  test.info().annotations.push({ type: 'drag', description: strategy });
  expect((await saved).status()).toBe(200);

  const expected = ['QA-Pub Drei', 'QA-Pub Eins', 'QA-Pub Zwei'];
  await page.reload();
  await expect(page.locator('.listing .listing__item-body').first()).toBeVisible();
  expect(await titles()).toEqual(expected);
  expect((await api.get(`/api/team/member/${member.id}`)).publications.map((p: any) => p.title.de)).toEqual(expected);

  await page.goto(`/de/${publicPath(member.id)}`);
  const pub = page.locator('.member__list').filter({ has: page.locator('.js-btn-member-sublist') });
  expect((await pub.locator('.js-btn-member-sublist span').allTextContents()).map(t => t.trim())).toEqual(expected);
});
