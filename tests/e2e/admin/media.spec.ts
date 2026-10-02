import { test, expect, qa } from '../support/fixtures';
import { answerConfirm, editor, listItem, open, toast, upload } from '../support/admin';
import { fixture } from '../support/env';
import { snapshot } from '../support/db';

test.afterEach(async ({ api }) => {
  for (const file of (await api.get('/api/files/fetch')).data) {
    if (file.name.includes('qa-')) await api.delete(`/api/file/${file.id}`);
  }
});

test('upload a PDF: listed, opens; delete asks first and removes the entry', async ({ page, api, request }) => {
  qa('md-upload', 'md-delete');
  await open(page, '/administration/media');
  await upload(page, fixture('qa-merkblatt.pdf'));
  await expect(toast(page, 'Datei gespeichert!')).toBeVisible();

  const item = listItem(page, 'qa-merkblatt.pdf');
  await expect(item).toBeVisible();
  await expect(item).toContainText('pdf');
  const href = (await item.locator('a[target=_blank]').getAttribute('href'))!;
  const pdf = await request.get(href);
  expect(pdf.status()).toBe(200);
  expect(pdf.headers()['content-type']).toBe('application/pdf');

  let answered = answerConfirm(page, false);
  await item.locator('.listing__item-action a').last().click();
  await answered;
  await expect(item).toBeVisible();
  answered = answerConfirm(page, true);
  await item.locator('.listing__item-action a').last().click();
  await answered;
  await expect(item).toHaveCount(0);
  expect((await api.get('/api/files/fetch')).data.some((f: any) => href.endsWith(f.name))).toBe(false);
  // Known: the file itself stays on disk (not asserted; removed by the run's teardown)
});

test('non-PDF files and PDFs over 16 MB are rejected with the reason', async ({ page }) => {
  qa('md-reject');
  await open(page, '/administration/media');
  let uploads = 0;
  page.on('request', request => { if (request.url().endsWith('/api/file/upload')) uploads++; });

  await upload(page, fixture('qa-notiz.txt'));
  await expect(toast(page, /«qa-notiz\.txt»: Dateityp nicht erlaubt \(erlaubt: pdf\)/, 'error')).toBeVisible();
  await upload(page, fixture('qa-landscape.jpg'));
  await expect(toast(page, /«qa-landscape\.jpg»: Dateityp nicht erlaubt/, 'error')).toBeVisible();
  await upload(page, fixture('qa-big.pdf'));
  await expect(toast(page, /«qa-big\.pdf»: Datei ist zu gross \([\d.]+ MB, erlaubt: max\. 16 MB\)/, 'error')).toBeVisible();
  expect(uploads).toBe(0);
});

test('a new file is offered in the editor link dialog under Datei', async ({ page }) => {
  qa('md-linkable', 'ed-link-file');
  await open(page, '/administration/media');
  await upload(page, fixture('qa-merkblatt.pdf'));
  await expect(toast(page, 'Datei gespeichert!')).toBeVisible();
  const name = (await listItem(page, 'qa-merkblatt.pdf').locator('a[target=_blank]').textContent())!.trim();

  const restore = snapshot('home', [1]);
  try {
    await open(page, '/administration/home/edit/1');
    await editor(page, 'Text').click();
    await page.locator('.form-row:visible .editor__toolbar button[title="Link"]').first().click();
    const dialog = page.locator('dialog.editor-dialog[open]');
    await dialog.locator('select').first().selectOption('file');
    await expect(dialog.locator('.form-row', { has: page.locator('label:text-is("Datei")') }).locator('option', { hasText: name })).toHaveCount(1);
    await dialog.getByRole('link', { name: 'Abbrechen' }).click();
  }
  finally {
    restore();
  }
});
