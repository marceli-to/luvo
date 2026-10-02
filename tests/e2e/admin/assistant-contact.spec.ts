import { test, expect, qa, type Api } from '../support/fixtures';
import { answerConfirm, editor, heading, language, listItem, open, publishRadio, row, save, toast, typeInto } from '../support/admin';
import { snapshot } from '../support/db';

const assistants: number[] = [];
const contacts: number[] = [];
test.afterEach(async ({ api }) => {
  for (const id of assistants.splice(0)) await api.delete(`/api/assistant/${id}`).catch(() => {});
  for (const id of contacts.splice(0)) await api.delete(`/api/contact/${id}`).catch(() => {});
});

test('Assistenz: Beschreibung (C4) required; fields per language; publish', async ({ page, api, strict }) => {
  qa('aa-required');
  strict.allow(/422/);
  await open(page, '/administration/assistant/create');
  await save(page, 422);
  await expect(row(page, 'Beschreibung')).toHaveClass(/has-error/);
  await expect(toast(page, 'Bitte alle mit * markierten Felder prüfen!', 'error')).toBeVisible();

  await row(page, 'Team').locator('select').selectOption({ label: 'Vogt' });
  for (const locale of ['de', 'fr', 'en'] as const) {
    await language(page, locale);
    await typeInto(editor(page, 'Beschreibung'), `QA-Assistenz ${locale}`);
    await typeInto(editor(page, 'Assistenten'), `QA-Assistenten ${locale}`);
  }
  await publishRadio(page, 0);
  await save(page);
  await expect(toast(page, 'Daten erfasst!')).toBeVisible();

  const assistant = (await api.get('/api/assistants')).data.find((a: any) => a.description.de === '<p>QA-Assistenz de</p>');
  assistants.push(assistant.id);
  expect(assistant.team_id).toBe(2);
  expect(assistant.publish).toBe(0);
  expect(assistant.assistants).toEqual({ de: '<p>QA-Assistenten de</p>', fr: '<p>QA-Assistenten fr</p>', en: '<p>QA-Assistenten en</p>' });
});

async function createContact(api: Api) {
  const { contactId } = await api.post('/api/contact', { address: { de: '<p>QA-Adresse</p>' }, imprint: {}, privacy: {}, map_uri: null, publish: 1, images: [] });
  contacts.push(contactId);
  return contactId as number;
}

test('Kontakt list: toggle, edit, delete with confirmation, create', async ({ page, api, strict }) => {
  qa('ak-list');
  strict.allow(/422/);
  // Create through the form (the list only offers "Hinzufügen" while empty, C12)
  await open(page, '/administration/contact/create');
  await save(page, 422);
  await expect(row(page, 'Adresse')).toHaveClass(/has-error/);
  await typeInto(editor(page, 'Adresse'), 'QA-Neue Adresse');
  await save(page);
  await expect(toast(page, 'Daten erfasst!')).toBeVisible();
  await expect(page).toHaveURL('/administration/contact');
  const created = (await api.get('/api/contact')).data.find((c: any) => c.address.de === '<p>QA-Neue Adresse</p>');
  contacts.push(created.id);

  const item = listItem(page, 'QA-Neue Adresse');
  await item.locator('.listing__item-action a').first().click();
  await expect(toast(page, 'Status geändert')).toBeVisible();
  expect((await api.get(`/api/contact/${created.id}`)).publish).toBe(0);

  await item.locator('a[href*="/contact/edit/"]').click();
  await expect(heading(page, 'Kontakt bearbeiten')).toBeVisible();
  await page.getByRole('link', { name: 'Zurück' }).click();

  let answered = answerConfirm(page, false);
  await item.locator('.listing__item-action a').last().click();
  await answered;
  await expect(item).toBeVisible();
  answered = answerConfirm(page, true);
  await item.locator('.listing__item-action a').last().click();
  await answered;
  await expect(item).toHaveCount(0);
});

test('Kontakt fields save per language and show on the public page', async ({ page, api }) => {
  qa('ak-fields', 'pp-privacy-lang');
  const restore = snapshot('contacts', [1]);
  try {
    await open(page, '/administration/contact/edit/1');
    for (const locale of ['de', 'fr', 'en'] as const) {
      await language(page, locale);
      await typeInto(editor(page, locale === 'de' ? 'Adresse' : 'Text'), `QA-Adresse ${locale}`);
      await typeInto(editor(page, 'Impressum'), `QA-Impressum ${locale}`);
      await typeInto(editor(page, 'Datenschutz'), `QA-Datenschutz ${locale}`);
      // One Maps URI for all languages, shown on the DE tab only (C5)
      if (locale === 'de') await row(page, 'Google Maps Uri').locator('input').fill('https://maps.example.com/qa');
      else await expect(row(page, 'Google Maps Uri')).toHaveCount(0);
    }
    await save(page);
    await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();

    const contact = await api.get('/api/contact/1');
    for (const key of ['address', 'imprint', 'privacy']) {
      const label = { address: 'Adresse', imprint: 'Impressum', privacy: 'Datenschutz' }[key];
      expect(contact[key]).toEqual({ de: `<p>QA-${label} de</p>`, fr: `<p>QA-${label} fr</p>`, en: `<p>QA-${label} en</p>` });
    }
    expect(contact.map_uri).toBe('https://maps.example.com/qa');

    for (const [locale, path] of [['de', '/de/kontakt'], ['fr', '/fr/contacter'], ['en', '/en/contact']]) {
      await page.goto(path);
      await expect(page.locator('address')).toContainText(`QA-Adresse ${locale}`);
      await page.locator('a.anchor-imprint', { hasText: /Datenschutz|Protection|Privacy|données/i }).click();
      await expect(page.locator('.contact__imprint', { hasText: `QA-Datenschutz ${locale}` })).toBeVisible();
      await expect(page.locator('a.anchor-maps')).toHaveAttribute('href', 'https://maps.example.com/qa');
    }
  }
  finally {
    restore();
  }
});
