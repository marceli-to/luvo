import { test, expect, qa, type Api } from '../support/fixtures';
import { answerConfirm, editor, language, listItem, open, publishRadio, row, save, tab, toast, typeInto, heading } from '../support/admin';
import { snapshot, select } from '../support/db';

const t = (text: string) => ({ de: `${text} DE`, fr: `${text} FR`, en: `${text} EN` });

async function createHome(api: Api, title = `QA-Home ${Date.now()}`) {
  const { homeId } = await api.post('/api/home', { title: t(title), text: t('<p>QA-Text</p>'), publish: 1, images: [] });
  return { id: homeId as number, title };
}

const created: number[] = [];
test.afterEach(async ({ api }) => {
  for (const id of created.splice(0)) await api.delete(`/api/home/${id}`).catch(() => {});
});

test('list loads; eye toggles publish', async ({ page, api }) => {
  qa('ah-list');
  const restore = snapshot('home', [1]);
  try {
    await open(page, '/administration/home');
    const item = page.locator('.listing__item').first();
    const before = (await api.get('/api/home/1')).publish;

    await item.locator('.listing__item-action a').first().click();
    await expect(toast(page, 'Status geändert')).toBeVisible();
    expect((await api.get('/api/home/1')).publish).toBe(before ? 0 : 1);
    await expect(item).toHaveClass(before ? /is-disabled/ : /^((?!is-disabled).)*$/);
  }
  finally {
    restore();
  }
});

test('public home follows the publish flag', async ({ page, strict }) => {
  qa('ah-list');
  strict.allow(/404/);
  const restore = snapshot('home', [1]);
  try {
    const title = select<{ t: string }>("SELECT JSON_UNQUOTE(JSON_EXTRACT(title, '$.de')) t FROM home WHERE id = 1")[0].t;
    await open(page, '/administration/home');
    await page.locator('.listing__item').first().locator('.listing__item-action a').first().click();
    await expect(toast(page, 'Status geändert')).toBeVisible();
    expect((await page.goto('/de/home'))?.status()).toBe(404);
    await expect(page.locator('article.home h1')).toHaveCount(0);
    expect(title).toBeTruthy();
  }
  finally {
    restore();
  }
});

test('create a new entry and find it in the list', async ({ page, api }) => {
  qa('ah-create');
  const title = `QA-Home ${Date.now()}`;
  await open(page, '/administration/home');
  // "Hinzufügen" only shows while the list is empty (C12); the create form has its own URL
  await expect(page.getByRole('link', { name: 'Hinzufügen' })).toHaveCount(0);
  await open(page, '/administration/home/create');
  await expect(heading(page, 'Homepage hinzufügen')).toBeVisible();

  await row(page, 'Titel').locator('input').fill(title);
  await typeInto(editor(page, 'Text'), 'QA-Text');
  await save(page);
  await expect(toast(page, 'Daten erfasst!')).toBeVisible();
  await expect(page).toHaveURL('/administration/home');
  await expect(listItem(page, title)).toBeVisible();

  const home = (await api.get('/api/home')).data.find((h: any) => h.title.de === title);
  created.push(home.id);
  expect(home.text.de).toBe('<p>QA-Text</p>');
});

test('delete asks for confirmation: cancel keeps, confirm removes', async ({ page, api }) => {
  qa('ah-delete');
  const home = await createHome(api);
  created.push(home.id);
  await open(page, '/administration/home');
  const item = listItem(page, home.title);
  const trash = item.locator('.listing__item-action a').last();

  let answered = answerConfirm(page, false);
  await trash.click();
  await answered;
  await expect(item).toBeVisible();
  expect((await api.get(`/api/home/${home.id}`)).id).toBe(home.id);

  answered = answerConfirm(page, true);
  await trash.click();
  await answered;
  await expect(item).toHaveCount(0);
  expect((await api.get('/api/home')).data.map((h: any) => h.id)).not.toContain(home.id);
});

test('saving without Titel shows the message and marks the field', async ({ page, strict }) => {
  qa('ah-required');
  strict.allow(/422/);
  await open(page, '/administration/home/create');
  await typeInto(editor(page, 'Text'), 'QA-Text');
  await save(page, 422);
  await expect(toast(page, 'Bitte alle mit * markierten Felder prüfen!', 'error')).toBeVisible();
  await expect(row(page, 'Titel')).toHaveClass(/has-error/);
});

test('saving without Text marks the Text field', async ({ page, strict }) => {
  qa('ah-required');
  strict.allow(/422/);
  await open(page, '/administration/home/create');
  await row(page, 'Titel').locator('input').fill('QA-ohne Text');
  await save(page, 422);
  await expect(row(page, 'Text')).toHaveClass(/has-error/);
});

test('language tabs: each language saves separately; publish radio saves', async ({ page, api }) => {
  qa('ah-langs', 'ah-publish');
  const home = await createHome(api);
  created.push(home.id);
  await open(page, `/administration/home/edit/${home.id}`);

  for (const locale of ['de', 'fr', 'en'] as const) {
    await language(page, locale);
    await row(page, 'Titel').locator('input').fill(`QA-Titel ${locale}`);
    await typeInto(editor(page, 'Text'), `QA-Text ${locale}`);
  }
  await language(page, 'de');
  await expect(row(page, 'Titel').locator('input')).toHaveValue('QA-Titel de');

  await publishRadio(page, 0);
  await save(page);
  await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();

  const stored = await api.get(`/api/home/${home.id}`);
  expect(stored.title).toEqual({ de: 'QA-Titel de', fr: 'QA-Titel fr', en: 'QA-Titel en' });
  expect(stored.text).toEqual({ de: '<p>QA-Text de</p>', fr: '<p>QA-Text fr</p>', en: '<p>QA-Text en</p>' });
  expect(stored.publish).toBe(0);
});

test('save: notification, reload shows it, public page updated', async ({ page, api }) => {
  qa('ah-save');
  const restore = snapshot('home', [1]);
  try {
    await open(page, '/administration/home/edit/1');
    await row(page, 'Titel').locator('input').fill('QA-Leitgedanke');
    await save(page);
    await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();
    await expect(page).toHaveURL('/administration/home');

    await open(page, '/administration/home/edit/1');
    await expect(row(page, 'Titel').locator('input')).toHaveValue('QA-Leitgedanke');
    expect((await api.get('/api/home/1')).title.de).toBe('QA-Leitgedanke');

    await page.goto('/de/home');
    await expect(page.locator('article.home h1')).toHaveText('QA-Leitgedanke');
  }
  finally {
    restore();
  }
});
