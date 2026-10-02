import fs from 'node:fs';
import path from 'node:path';
import type { Locator, Page } from '@playwright/test';
import { test, expect, qa, type Api } from '../support/fixtures';
import { editor, language, open, row, save, toast } from '../support/admin';
import { fixture, root } from '../support/env';
import { snapshot, select } from '../support/db';
import { members } from '../support/site';

/**
 * The Tiptap editor, driven through its toolbar titles, on the real Home
 * text (restored after each test) so the public page shows the result.
 */

const text = (page: Page) => editor(page, 'Text');
const button = (page: Page, title: string) => row(page, 'Text').locator(`.editor__toolbar button[title="${title}"]`);
const storedHome = async (api: Api) => (await api.get('/api/home/1')).text.de as string;

let restore: () => void;
test.beforeEach(() => { restore = snapshot('home', [1]); });
test.afterEach(() => restore());

/** Empties the Home text (Tiptap's own command) and types `content`. */
async function freshText(page: Page, content: string) {
  await open(page, '/administration/home/edit/1');
  const area = text(page);
  await area.evaluate((el: any) => el.editor.commands.clearContent(true));
  await area.click();
  await area.pressSequentially(content);
  return area;
}

async function saveAndRead(page: Page, api: Api) {
  await save(page);
  await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();
  return storedHome(api);
}

test('undo and redo', async ({ page, api }) => {
  qa('ed-undo');
  const area = await freshText(page, 'QA eins');
  await expect(button(page, 'Wiederholen')).toBeDisabled();
  await button(page, 'Rückgängig').click();
  await expect(area).not.toContainText('QA eins');
  await expect(button(page, 'Wiederholen')).toBeEnabled();
  await button(page, 'Wiederholen').click();
  await expect(area).toContainText('QA eins');
  expect(await saveAndRead(page, api)).toBe('<p>QA eins</p>');
});

test('headings 1 to 3 and back to paragraph, with active state', async ({ page, api }) => {
  qa('ed-headings');
  const area = await freshText(page, 'QA Titel');
  for (const level of [1, 2, 3]) {
    await button(page, `Überschrift ${level}`).click();
    await expect(area.locator(`h${level}`)).toHaveText('QA Titel');
    await expect(button(page, `Überschrift ${level}`)).toHaveClass(/is-active/);
  }
  await button(page, 'Überschrift 3').click();
  await expect(area.locator('p', { hasText: 'QA Titel' })).toBeVisible();
  await expect(area.locator('h1, h2, h3')).toHaveCount(0);
  await expect(button(page, 'Überschrift 3')).not.toHaveClass(/is-active/);
  await button(page, 'Überschrift 2').click();
  expect(await saveAndRead(page, api)).toBe('<h2>QA Titel</h2>');
});

test('bold, superscript and remove formatting', async ({ page, api }) => {
  qa('ed-bold', 'ed-sup', 'ed-clear');
  const area = await freshText(page, 'QA fett2');
  await area.press('ControlOrMeta+a');
  await button(page, 'Fett').click();
  await expect(area.locator('strong')).toHaveText('QA fett2');
  // Select the last character (through Tiptap: arrow keys after a toolbar click are unreliable here)
  await area.evaluate((el: any) => {
    const end = el.editor.state.doc.content.size - 1;
    el.editor.commands.setTextSelection({ from: end - 1, to: end });
  });
  await button(page, 'Hochgestellt').click();
  await expect(area.locator('sup')).toHaveText('2');
  expect(await saveAndRead(page, api)).toMatch(/^<p><strong>QA fett(<sup>2<\/sup>|<\/strong><sup><strong>2<\/strong><\/sup><strong>)?<\/strong>(<sup><strong>2<\/strong><\/sup>)?<\/p>$/);

  await open(page, '/administration/home/edit/1');
  await text(page).click();
  await text(page).press('ControlOrMeta+a');
  await button(page, 'Überschrift 1').click();
  await button(page, 'Formatierung entfernen').click();
  expect(await saveAndRead(page, api)).toBe('<p>QA fett2</p>');
});

test('lists: bullet and numbered, toggling off; public list style', async ({ page, api }) => {
  qa('ed-lists');
  const area = await freshText(page, 'QA eins');
  await button(page, 'Liste').click();
  await expect(area.locator('ul li')).toHaveText('QA eins');
  await button(page, 'Liste').click();
  await expect(area.locator('ul')).toHaveCount(0);
  await button(page, 'Nummerierte Liste').click();
  await area.press('End');
  await area.press('Enter');
  await area.pressSequentially('QA zwei');
  await expect(area.locator('ol li')).toHaveCount(2);
  expect(await saveAndRead(page, api)).toBe('<ol><li>QA eins</li><li>QA zwei</li></ol>');

  await page.goto('/de/home');
  const list = page.locator('article.home ol');
  await expect(list.locator('li')).toHaveCount(2);
  // The site's list style, not the browser default
  expect(await list.evaluate(el => getComputedStyle(el).listStyleType)).not.toBe('');
  expect(await list.evaluate(el => getComputedStyle(el).paddingLeft)).not.toBe('40px');
});

async function smallText(page: Page, api: Api) {
  await freshText(page, 'QA normal');
  await text(page).press('Enter');
  await text(page).pressSequentially('QA klein');
  await button(page, 'Kleine Schrift').click();
  await expect(button(page, 'Kleine Schrift')).toHaveClass(/is-active/);
  expect(await saveAndRead(page, api)).toBe('<p>QA normal</p><p class="fs-sm">QA klein</p>');
}

test('Kleine Schrift is stored as class fs-sm', async ({ page, api }) => {
  qa('ed-small');
  await smallText(page, api);
});

test('Worttrennung deaktivieren keeps the words together', async ({ page, api }) => {
  qa('ed-nowrap');
  const area = await freshText(page, 'QA Donaudampfschifffahrt');
  await area.press('ControlOrMeta+a');
  await button(page, 'Worttrennung deaktivieren').click();
  expect(await saveAndRead(page, api)).toBe('<p><span style="white-space: nowrap;">QA Donaudampfschifffahrt</span></p>');

  await page.goto('/de/home');
  const span = page.locator('article.home span', { hasText: 'QA Donaudampfschifffahrt' });
  expect(await span.evaluate(el => getComputedStyle(el).whiteSpace)).toBe('nowrap');
});

// One dialog per editor; the open one
const dialog = (page: Page) => page.locator('dialog.editor-dialog[open]');

async function linkWord(page: Page, word: string) {
  const area = await freshText(page, `QA ${word}`);
  await area.press('End');
  for (let i = 0; i < word.length; i++) await area.press('Shift+ArrowLeft');
  await button(page, 'Link').click();
  await expect(dialog(page)).toBeVisible();
  return area;
}

test('link: URL, new tab only when ticked', async ({ page, api }) => {
  qa('ed-link-url');
  await linkWord(page, 'Webseite');
  await dialog(page).locator('input[placeholder="www.example.com"]').fill('www.example.com/qa');
  await dialog(page).getByLabel('In neuem Fenster öffnen').check();
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();
  await expect(dialog(page)).toBeHidden();
  expect(await saveAndRead(page, api)).toBe('<p>QA <a target="_blank" rel="noopener" href="https://www.example.com/qa">Webseite</a></p>'.replace(/<a ([^>]*)>/, (_, a) => `<a ${a}>`));
});

test('link: E-Mail and Telefon', async ({ page, api }) => {
  qa('ed-link-mail', 'ed-link-tel');
  await linkWord(page, 'Mail');
  await dialog(page).locator('select').first().selectOption('email');
  await dialog(page).locator('input[placeholder="info@luksundvogt.ch"]').fill('qa@luvo.test');
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();
  let html = await saveAndRead(page, api);
  expect(html).toContain('href="mailto:qa@luvo.test"');
  expect(html).not.toContain('target=');

  await linkWord(page, 'Telefon');
  await dialog(page).locator('select').first().selectOption('tel');
  await dialog(page).locator('input[placeholder="+41 44 000 00 00"]').fill('+41 44 123 45 67');
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();
  html = await saveAndRead(page, api);
  expect(html).toContain('href="tel:+41441234567"');
});

test('link: Datei from the list, and a PDF uploaded inside the dialog', async ({ page, api }) => {
  qa('ed-link-file', 'md-linkable');
  const existing = (await api.get('/api/files'))[0];
  await linkWord(page, 'Datei');
  await dialog(page).locator('select').first().selectOption('file');
  const files = dialog(page).locator('.form-row', { has: page.locator('label:text-is("Datei")') }).locator('select');
  await files.selectOption(existing.value);
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();
  expect(await saveAndRead(page, api)).toContain(`href="${existing.value}"`);

  await linkWord(page, 'Neu');
  await dialog(page).locator('select').first().selectOption('file');
  await dialog(page).locator('input.dz-hidden-input').setInputFiles(fixture('qa-dialog.pdf'));
  await expect(toast(page, 'Datei gespeichert!')).toBeVisible();
  await expect(files).toHaveValue(/qa-dialog\.pdf$/);
  const href = await files.inputValue();
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();
  expect(await saveAndRead(page, api)).toContain(`href="${href}"`);

  // Also listed under Dateien; the record is removed again (the file goes in teardown)
  const file = (await api.get('/api/files/fetch')).data.find((f: any) => href.endsWith(f.name));
  expect(file).toBeTruthy();
  await api.delete(`/api/file/${file.id}`);
});

test('link: edit and remove; Titel is kept', async ({ page, api }) => {
  qa('ed-link-edit');
  const area = await linkWord(page, 'Link');
  await dialog(page).locator('input[placeholder="www.example.com"]').fill('www.example.com/alt');
  await dialog(page).locator('.form-row', { hasText: 'Titel (optional)' }).locator('input').fill('QA-Titel');
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();

  await area.locator('a').click();
  await button(page, 'Link').click();
  await expect(dialog(page).locator('input[placeholder="www.example.com"]')).toHaveValue('www.example.com/alt');
  await expect(dialog(page).locator('.form-row', { hasText: 'Titel (optional)' }).locator('input')).toHaveValue('QA-Titel');
  await dialog(page).locator('input[placeholder="www.example.com"]').fill('www.example.com/neu');
  await dialog(page).getByRole('button', { name: 'Übernehmen' }).click();
  let html = await saveAndRead(page, api);
  expect(html).toContain('href="https://www.example.com/neu"');
  expect(html).toContain('title="QA-Titel"');

  await open(page, '/administration/home/edit/1');
  await text(page).locator('a').click();
  await button(page, 'Link').click();
  await dialog(page).getByRole('link', { name: 'Entfernen' }).click();
  html = await saveAndRead(page, api);
  expect(html).toBe('<p>QA Link</p>');
});

test('paste from Word drops the junk and keeps the structure', async ({ page, api }) => {
  qa('ed-paste');
  const area = await freshText(page, 'QA');
  await area.press('ControlOrMeta+a');
  await area.press('Backspace');
  const html = fs.readFileSync(path.join(root, 'tests/e2e/fixtures/word-paste.html'), 'utf8');
  await area.evaluate((el, data) => {
    const transfer = new DataTransfer();
    transfer.setData('text/html', data);
    transfer.setData('text/plain', 'QA');
    el.dispatchEvent(new ClipboardEvent('paste', { clipboardData: transfer, bubbles: true, cancelable: true }));
  }, html);
  await expect(area).toContainText('QA-Titel aus Word');

  const stored = await saveAndRead(page, api);
  for (const junk of ['mso-', 'font-family', 'Segoe', '<o:p', 'MsoNormal', 'class="Mso', 'style=', '<!--', 'color:']) {
    expect(stored, junk).not.toContain(junk);
  }
  expect(stored).toContain('<h1>QA-Titel aus Word</h1>');
  expect(stored).toContain('<strong>fettem Text</strong>');
  expect(stored).toContain('äöü');
  expect(stored).toContain('<li>QA-Listenpunkt</li>');
  expect(stored).toContain('href="https://www.example.com/qa"');
});

test('keyboard: Enter, Shift+Enter, bold and undo shortcuts', async ({ page, api }) => {
  qa('ed-keys');
  const area = await freshText(page, 'QA eins');
  await area.press('Enter');
  await area.pressSequentially('QA zwei');
  await area.press('Shift+Enter');
  await area.pressSequentially('QA drei');
  await area.press('ControlOrMeta+b');
  await area.pressSequentially(' fett');
  await area.press('ControlOrMeta+b');
  // Typing within 500 ms is one undo step; pause so Cmd/Ctrl+Z undoes only " weg"
  await page.waitForTimeout(700);
  await area.pressSequentially(' weg');
  await area.press('ControlOrMeta+z');
  expect(await saveAndRead(page, api)).toBe('<p>QA eins</p><p>QA zwei<br>QA drei<strong> fett</strong></p>');
});

/**
 * Text and link targets of an element. Block boundaries count as a space,
 * so whitespace between tags in the stored HTML doesn't matter (it doesn't
 * show either).
 */
async function rendering(page: Page, url: string, selector: string) {
  await page.goto(url);
  const element = page.locator(selector).first();
  return {
    text: (await element.evaluate(el => {
      const clone = el.cloneNode(true) as HTMLElement;
      clone.querySelectorAll('p, li, h1, h2, h3, h4, div, br, ul, ol, address').forEach(block => {
        block.before(' ');
        block.after(' ');
      });
      return clone.textContent ?? '';
    })).replace(/\s+/g, ' ').trim(),
    links: await element.locator('a').evaluateAll(links => links.map(a => a.getAttribute('href'))),
    headings: await element.locator('h1, h2, h3, strong, li, .fs-sm').count(),
  };
}

const longest = {
  member: select<{ id: number }>("SELECT id FROM team_members WHERE publish = 1 ORDER BY LENGTH(JSON_UNQUOTE(JSON_EXTRACT(biography, '$.de'))) DESC LIMIT 1")[0].id,
};

for (const target of [
  { name: 'longest Werdegang', admin: () => `/administration/team/member/edit/${longest.member}`, label: 'Werdegang', table: 'team_members', id: () => longest.member,
    page: () => `/de/${members().find(m => m.id === longest.member)!.path}`, selector: 'article.member' },
  { name: 'Kontakt Datenschutz', admin: () => '/administration/contact/edit/1', label: 'Datenschutz', table: 'contacts', id: () => 1, page: () => '/de/kontakt', selector: 'article.contact' },
  { name: 'Home text', admin: () => '/administration/home/edit/1', label: 'Text', table: 'home', id: () => 1, page: () => '/de/home', selector: 'article.home' },
]) {
  test(`round trip: ${target.name} saved through the editor looks the same`, async ({ page }) => {
    qa('ed-roundtrip');
    const restoreRecord = snapshot(target.table, [target.id()]);
    try {
      const before = await rendering(page, target.page(), target.selector);
      await open(page, target.admin());
      // Touch the text (type and delete) so the editor serialises it
      const area = editor(page, target.label);
      await area.click();
      await area.press('ControlOrMeta+End');
      await area.pressSequentially('x');
      await area.press('Backspace');
      await save(page);
      const after = await rendering(page, target.page(), target.selector);
      expect(after).toEqual(before);
    }
    finally {
      restoreRecord();
    }
  });
}

test('old Word paste: inline fonts are gone after saving', async ({ page, api }) => {
  qa('ed-cleanup');
  const restoreTeam = snapshot('teams', [1]);
  try {
    expect((await api.get('/api/team/1')).text.de).toContain('font-family');
    await open(page, '/administration/team/edit/1');
    const area = editor(page, 'Text');
    await area.click();
    await area.press('ControlOrMeta+End');
    await area.pressSequentially('x');
    await area.press('Backspace');
    await save(page);

    const stored = (await api.get('/api/team/1')).text.de;
    expect(stored).not.toContain('font-family');
    await page.goto('/de/team-luks');
    const inline = await page.locator('article.rich-text [style*="font"]').count();
    expect(inline).toBe(0);
  }
  finally {
    restoreTeam();
  }
});
