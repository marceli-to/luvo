import fs from 'node:fs';
import type { Locator, Page } from '@playwright/test';
import { test, expect, qa, knownFinding, type Api } from '../support/fixtures';
import { answerConfirm, dragAndDrop, heading, open, row, save, tab, toast, upload } from '../support/admin';
import { fixture } from '../support/env';
import { setColumn } from '../support/db';
import { members } from '../support/site';

/**
 * The image manager, run fully on a QA team member; spot checks for Home,
 * Team, Assistenz and Kontakt at the end.
 */

const created: { members: number[]; homes: number[]; assistants: number[]; contacts: number[] } = { members: [], homes: [], assistants: [], contacts: [] };
test.afterEach(async ({ api }) => {
  for (const id of created.members.splice(0)) await api.delete(`/api/team/member/${id}`).catch(() => {});
  for (const id of created.homes.splice(0)) await api.delete(`/api/home/${id}`).catch(() => {});
  // Their images first: deleting an Assistenz / Kontakt with images fails (F18)
  for (const [entity, ids] of [['assistant', created.assistants], ['contact', created.contacts]] as const) {
    for (const id of ids.splice(0)) {
      const record = await api.get(`/api/${entity}/${id}`).catch(() => null);
      for (const image of record?.images ?? []) await api.delete(`/api/${entity}/image/${image.name}`).catch(() => {});
      await api.delete(`/api/${entity}/${id}`).catch(() => {});
    }
  }
});

async function createMember(api: Api) {
  const { teamMemberId } = await api.post('/api/team/member', { firstname: `QA-Bild${Date.now()}`, name: 'QA-Muster', team_id: 1, publish: 1, images: [] });
  created.members.push(teamMemberId);
  return teamMemberId as number;
}

const items = (page: Page) => page.locator('.upload-item');
/** Image actions: 0 eye, 1 pencil, 2 view, 3 trash, 4 crop. */
const action = (item: Locator, index: number) => item.locator('.upload__actions a').nth(index);
const memberImages = async (api: Api, id: number) => (await api.get(`/api/team/member/${id}`)).images;
const publicPath = (id: number) => `/de/${members().find(m => m.id === id)!.path}`;

/** Uploads on an edit form and waits for each "Bild gespeichert!". */
async function uploadSaved(page: Page, files: string[]) {
  const before = await items(page).count();
  await upload(page, files.map(fixture));
  await expect(items(page)).toHaveCount(before + files.length, { timeout: 20_000 });
}

async function openMemberImages(page: Page, id: number) {
  await open(page, `/administration/team/member/edit/${id}`);
  await tab(page, 'Bilder');
}

/** Width / height of the cropper's stencil on screen. */
async function stencilRatio(page: Page) {
  const box = (await page.locator('.upload-overlay-cropper .vue-rectangle-stencil').boundingBox())!;
  return box.width / box.height;
}

/**
 * Drags the crop frame by (dx, dy) screen pixels and waits 700 ms: the
 * cropper reports a change only after its 500 ms debounce (F20).
 */
async function moveStencil(page: Page, dx: number, dy: number, settle = 700) {
  const box = (await page.locator('.upload-overlay-cropper .vue-rectangle-stencil').boundingBox())!;
  await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
  await page.mouse.down();
  await page.mouse.move(box.x + box.width / 2 + dx, box.y + box.height / 2 + dy, { steps: 8 });
  await page.mouse.up();
  await page.waitForTimeout(settle);
}

async function openCropper(page: Page, item: Locator) {
  await action(item, 4).click();
  await expect(page.locator('.upload-overlay-cropper')).toHaveClass(/is-visible/);
  await expect(page.locator('.upload-overlay-cropper .vue-rectangle-stencil')).toBeVisible();
  await expect(page.locator('.cropper-info')).toHaveText(/^\d+ x \d+px$/);
}

test('upload png, jpg and jpeg one by one, several at once and by drop', async ({ page, api }) => {
  qa('im-upload', 'am-images');
  const id = await createMember(api);
  await openMemberImages(page, id);

  for (const file of ['qa-square.png', 'qa-landscape.jpg', 'qa-portrait.jpeg']) {
    await uploadSaved(page, [file]);
    await expect(toast(page, 'Bild gespeichert!').first()).toBeVisible();
  }
  await uploadSaved(page, ['qa-second.jpg', 'qa-third.jpg', 'qa-landscape.jpg']);

  // Drag and drop from the desktop: a drop event with files on the zone
  const bytes = fs.readFileSync(fixture('qa-second.jpg')).toString('base64');
  const before = await items(page).count();
  await page.locator('.vue-dropzone').evaluate((zone, data) => {
    const file = new File([Uint8Array.from(atob(data), c => c.charCodeAt(0))], 'qa-dropped.jpg', { type: 'image/jpeg' });
    const transfer = new DataTransfer();
    transfer.items.add(file);
    for (const type of ['dragenter', 'dragover', 'drop']) {
      zone.dispatchEvent(new DragEvent(type, { bubbles: true, cancelable: true, dataTransfer: transfer }));
    }
  }, bytes);
  await expect(items(page)).toHaveCount(before + 1, { timeout: 20_000 });

  const stored = await memberImages(api, id);
  expect(stored).toHaveLength(7);
  expect(stored.map((i: any) => i.name.replace(/^luksundvogt-[0-9a-f]+_/, ''))).toEqual(expect.arrayContaining(['qa-square.png', 'qa-portrait.jpeg', 'qa-dropped.jpg']));
  expect(stored.find((i: any) => i.name.endsWith('qa-portrait.jpeg')).orientation).toBe('p');
  expect(stored.every((i: any) => i.device === 'desktop')).toBe(true);
});

test('gif, pdf and files over 8 MB are rejected with the reason', async ({ page, api }) => {
  qa('im-reject');
  const id = await createMember(api);
  await openMemberImages(page, id);
  let uploads = 0;
  page.on('request', request => { if (request.url().endsWith('/api/image/upload')) uploads++; });

  await upload(page, fixture('qa-anim.gif'));
  await expect(toast(page, /«qa-anim\.gif»: Dateityp nicht erlaubt \(erlaubt: jpg, png\)/, 'error')).toBeVisible();
  await upload(page, fixture('qa-merkblatt.pdf'));
  await expect(toast(page, /«qa-merkblatt\.pdf»: Dateityp nicht erlaubt/, 'error')).toBeVisible();
  await upload(page, fixture('qa-big.jpg'));
  await expect(toast(page, /«qa-big\.jpg»: Datei ist zu gross \([\d.]+ MB, erlaubt: max\. 8 MB\)/, 'error')).toBeVisible();

  expect(uploads).toBe(0);
  await expect(items(page)).toHaveCount(0);
  expect(await memberImages(api, id)).toEqual([]);
});

test('unsaved record: upload first, then save; images and order stay with it', async ({ page, api }) => {
  qa('im-unsaved', 'im-list');
  const firstname = `QA-Neu${Date.now()}`;
  await open(page, '/administration/team/member/create');
  await row(page, 'Vorname').locator('input').fill(firstname);
  await row(page, 'Name').locator('input').fill('QA-Muster');
  await tab(page, 'Bilder');
  await upload(page, [fixture('qa-landscape.jpg')]);
  await expect(items(page)).toHaveCount(1);
  await upload(page, [fixture('qa-portrait.jpeg')]);
  await expect(items(page)).toHaveCount(2);

  // Reorder in the list view: portrait first
  await page.locator('a.icon-view').click();
  const rows = page.locator('.upload-item-row');
  const order = async () => rows.locator('img').evaluateAll(imgs => imgs.map(img => (img as HTMLImageElement).src.split('_').pop()));
  await dragAndDrop(page, rows.nth(1), rows.nth(0), async () => (await order())[0] === 'qa-portrait.jpeg');
  await page.locator('a.icon-view').click();

  await save(page);
  await expect(toast(page, 'Daten erfasst!')).toBeVisible();
  const member = (await api.get('/api/team/members')).data.find((m: any) => m.firstname === firstname);
  created.members.push(member.id);
  const stored = await memberImages(api, member.id);
  expect(stored.map((i: any) => i.name.split('_').pop())).toEqual(['qa-portrait.jpeg', 'qa-landscape.jpg']);
});

test('caption overlay: pencil opens, caption saves and shows; closes by button, ✕ and Escape', async ({ page, api }) => {
  qa('im-caption');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  const item = items(page).first();
  const overlay = page.locator('.upload-overlay-edit');

  // Desktop images: info text instead of a caption field; Mobile has the field
  await action(item, 1).click();
  await expect(overlay).toHaveClass(/is-visible/);
  await expect(overlay).toContainText('Info: Die Bildlegenden für die Desktop-Version');
  await overlay.locator('select[name=device]').selectOption('mobile');
  await overlay.locator('.form-row', { has: page.locator('label:text-is("Bildlegende")') }).locator('input').fill('QA-Legende');
  await expect(overlay.locator('figcaption')).toHaveText('QA-Legende');
  await overlay.locator('a.btn-primary', { hasText: 'Schliessen' }).click();
  await expect(overlay).not.toHaveClass(/is-visible/);

  await action(item, 1).click();
  await overlay.locator('a.upload-overlay__close').click();
  await expect(overlay).not.toHaveClass(/is-visible/);
  await action(item, 1).click();
  await page.keyboard.press('Escape');
  await expect(overlay).not.toHaveClass(/is-visible/);

  await save(page);
  const [image] = await memberImages(api, id);
  expect(image.caption).toBe('QA-Legende');
  expect(image.device).toBe('mobile');
});

test('Anwendung puts the image in the right slot on the public page', async ({ page, api }) => {
  qa('im-device');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  await uploadSaved(page, ['qa-portrait.jpeg']);
  const names = (await memberImages(api, id)).map((i: any) => i.name);
  const landscape = names.find((n: string) => n.endsWith('qa-landscape.jpg'));
  const portrait = names.find((n: string) => n.endsWith('qa-portrait.jpeg'));

  await action(items(page).filter({ has: page.locator(`img[src*="${portrait}"]`) }), 1).click();
  await page.locator('.upload-overlay-edit select[name=device]').selectOption('mobile');
  await page.keyboard.press('Escape');
  await save(page);

  await page.goto(publicPath(id));
  await expect(page.locator(`figure.visual-desktop img[src*="${landscape}"]`)).toHaveCount(1);
  await expect(page.locator(`figure.visual-mobile img[src*="${portrait}"]`)).toHaveCount(1);
});

test('cropper: ratio per device, opens on the saved crop, save and cancel', async ({ page, api }) => {
  qa('im-crop-ratio', 'im-crop-open', 'im-crop-save', 'am-images');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  const item = items(page).first();

  await openCropper(page, item);
  expect(await stencilRatio(page)).toBeCloseTo(10 / 12, 1);
  await page.locator('.btn-cropper-format', { hasText: 'Mobile' }).click();
  await expect.poll(() => stencilRatio(page)).toBeCloseTo(3 / 2, 1);
  await page.locator('.btn-cropper-format', { hasText: 'Desktop' }).click();
  await expect.poll(() => stencilRatio(page)).toBeCloseTo(10 / 12, 1);

  // Move the frame, save: new coords in the API and the public URL
  await moveStencil(page, 60, 10);
  const size = await page.locator('.cropper-info').textContent();
  const put = page.waitForResponse(r => r.request().method() === 'PUT' && /\/api\/team\/member\/image\/\d+$/.test(r.url()));
  await page.locator('.upload-overlay-cropper a.btn-primary', { hasText: 'Speichern' }).click();
  expect((await put).status()).toBe(200);
  await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();
  await expect(page.locator('.upload-overlay-cropper')).not.toHaveClass(/is-visible/);

  const [saved] = await memberImages(api, id);
  expect(saved.coords_x).toBeGreaterThan(0);
  expect(`${Math.floor(saved.coords_w)} x ${Math.floor(saved.coords_h)}px`).toBe(size);
  const coords = [saved.coords_w, saved.coords_h, saved.coords_x ?? 0, saved.coords_y ?? 0].map(v => Math.trunc(v)).join(',');

  await page.goto(publicPath(id));
  await expect(page.locator(`figure.visual-desktop source[srcset*="/${coords}"]`).first()).toBeAttached();

  // Reopens on the saved crop; Abbrechen keeps it
  await openMemberImages(page, id);
  await openCropper(page, items(page).first());
  await expect(page.locator('.cropper-info')).toHaveText(size!);
  await moveStencil(page, -40, 0);
  let puts = 0;
  page.on('request', r => { if (r.method() === 'PUT') puts++; });
  await page.locator('.upload-overlay-cropper a.btn-secondary', { hasText: 'Abbrechen' }).click();
  await expect(page.locator('.upload-overlay-cropper')).not.toHaveClass(/is-visible/);
  expect(puts).toBe(0);
  const [after] = await memberImages(api, id);
  expect([after.coords_w, after.coords_h, after.coords_x, after.coords_y]).toEqual([saved.coords_w, saved.coords_h, saved.coords_x, saved.coords_y]);

  // Escape closes the cropper too
  await openCropper(page, items(page).first());
  await page.keyboard.press('Escape');
  await expect(page.locator('.upload-overlay-cropper')).not.toHaveClass(/is-visible/);
});

test('saving right after moving the crop frame keeps the new frame', async ({ page, api }) => {
  qa('im-crop-save');
  knownFinding('F20', 'Speichern within 0.5 s of moving the frame saves the previous frame (cropper change debounce)');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  await openCropper(page, items(page).first());
  await moveStencil(page, 60, 10, 100);
  const put = page.waitForRequest(r => r.method() === 'PUT');
  await page.locator('.upload-overlay-cropper a.btn-primary', { hasText: 'Speichern' }).click();
  expect(JSON.parse((await put).postData()!).coords_x).toBeGreaterThan(0);
});

test('Abbrechen in the cropper also discards a Desktop / Mobile switch', async ({ page, api }) => {
  qa('im-crop-save');
  knownFinding('F13', 'the Desktop / Mobile buttons change the device at once; Abbrechen keeps it');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  const item = items(page).first();
  await openCropper(page, item);
  await page.locator('.btn-cropper-format', { hasText: 'Mobile' }).click();
  await page.locator('.upload-overlay-cropper a.btn-secondary', { hasText: 'Abbrechen' }).click();
  await expect(item.locator('.image-label')).toHaveText('Desktop');
});

test('eye hides the image on the public page; view opens the crop; delete asks first', async ({ page, api, context }) => {
  qa('im-toggle', 'im-view', 'im-delete');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  const [image] = await memberImages(api, id);
  const item = items(page).first();

  await action(item, 0).click();
  await expect(item).toHaveClass(/is-disabled/);
  expect((await memberImages(api, id))[0].publish).toBe(0);
  const visitor = await context.newPage();
  await visitor.goto(publicPath(id));
  await expect(visitor.locator(`img[src*="${image.name}"]`)).toHaveCount(0);
  await visitor.close();
  await action(item, 0).click();
  await expect(item).not.toHaveClass(/is-disabled/);

  const popup = page.waitForEvent('popup');
  await action(item, 2).click();
  const view = await popup;
  await view.waitForLoadState();
  expect(view.url()).toContain(`/img/crop/${image.name}`);
  await view.close();

  let answered = answerConfirm(page, false);
  await action(item, 3).click();
  await answered;
  await expect(items(page)).toHaveCount(1);
  answered = answerConfirm(page, true);
  await action(item, 3).click();
  await answered;
  await expect(items(page)).toHaveCount(0);
  expect(await memberImages(api, id)).toEqual([]);
});

test('list view: drag images into a new order; kept after reload and on the public page', async ({ page, api }) => {
  qa('im-drag', 'im-list');
  const id = await createMember(api);
  await openMemberImages(page, id);
  await uploadSaved(page, ['qa-landscape.jpg']);
  await uploadSaved(page, ['qa-second.jpg']);
  await uploadSaved(page, ['qa-third.jpg']);

  const toggle = page.locator('a.icon-view');
  await expect(toggle).toHaveText('Grid Ansicht');
  await toggle.click();
  await expect(toggle).toHaveText('Listen Ansicht');
  const rows = page.locator('.upload-item-row');
  await expect(rows).toHaveCount(3);
  const order = async () => rows.locator('img').evaluateAll(imgs => imgs.map(img => (img as HTMLImageElement).src.split('_').pop()));

  const saved = page.waitForResponse(r => r.url().endsWith('/api/team/member/image/order'));
  await dragAndDrop(page, rows.nth(2), rows.nth(0), async () => (await order())[0] === 'qa-third.jpg');
  expect((await saved).status()).toBe(200);

  const expected = ['qa-third.jpg', 'qa-landscape.jpg', 'qa-second.jpg'];
  expect((await memberImages(api, id)).map((i: any) => i.name.split('_').pop())).toEqual(expected);
  await openMemberImages(page, id);
  await expect(items(page)).toHaveCount(3);
  expect(await items(page).locator('img').evaluateAll(imgs => imgs.map(img => (img as HTMLImageElement).src.split('_').pop()))).toEqual(expected);

  await page.goto(publicPath(id));
  const publicOrder = await page.locator('figure.visual-desktop img').evaluateAll(imgs => imgs.map(img => img.getAttribute('src')!.split('/')[3].split('_').pop()));
  expect(publicOrder).toEqual(expected);
});

// Spot checks per form

test('Home images: Vorschau label and 1:1, default 16:10, DE / FR / EN captions', async ({ page, api }) => {
  qa('ah-images', 'im-crop-ratio');
  const { homeId } = await api.post('/api/home', { title: { de: `QA-Home Bilder ${Date.now()}` }, text: { de: '<p>QA</p>' }, publish: 1, images: [] });
  created.homes.push(homeId);
  await open(page, `/administration/home/edit/${homeId}`);
  await tab(page, 'Bilder');
  await uploadSaved(page, ['qa-landscape.jpg', 'qa-second.jpg']);

  await openCropper(page, items(page).first());
  expect(await stencilRatio(page)).toBeCloseTo(16 / 10, 1);
  await page.keyboard.press('Escape');

  // No control in the admin sets "Vorschau" (C13): set it on the QA image directly
  const [image] = (await api.get(`/api/home/${homeId}`)).images;
  setColumn('home_images', image.id, 'preview', 1);
  await open(page, `/administration/home/edit/${homeId}`);
  await tab(page, 'Bilder');
  const preview = items(page).filter({ has: page.locator('.image-label', { hasText: 'Vorschau' }) });
  await expect(preview).toHaveCount(1);
  await openCropper(page, preview);
  expect(await stencilRatio(page)).toBeCloseTo(1, 1);
  await page.keyboard.press('Escape');

  const overlay = page.locator('.upload-overlay-edit');
  await action(preview, 1).click();
  for (const [label, value] of [['Bildlegende', 'QA-Legende'], ['Bildlegende (FR)', 'QA-Légende'], ['Bildlegende (EN)', 'QA-Caption']]) {
    await overlay.locator('.form-row', { has: page.locator(`label:text-is("${label}")`) }).locator('input').fill(value);
  }
  await page.keyboard.press('Escape');
  await save(page);
  expect((await api.get(`/api/home/${homeId}`)).images.find((i: any) => i.id === image.id).caption).toEqual({ de: 'QA-Legende', fr: 'QA-Légende', en: 'QA-Caption' });
});

for (const entity of ['assistant', 'contact'] as const) {
  test(`${entity === 'assistant' ? 'Assistenz' : 'Kontakt'} images: Desktop / Mobile, caption, crop, delete`, async ({ page, api }) => {
    qa(entity === 'assistant' ? 'aa-images' : 'ak-images');
    let id: number;
    if (entity === 'assistant') {
      ({ assistantId: id } = await api.post('/api/assistant', { team_id: 2, description: { de: '<p>QA-Assistenz Bilder</p>' }, assistants: {}, publish: 0, images: [] }));
      created.assistants.push(id);
    }
    else {
      ({ contactId: id } = await api.post('/api/contact', { address: { de: '<p>QA-Kontakt Bilder</p>' }, imprint: {}, privacy: {}, map_uri: null, publish: 0, images: [] }));
      created.contacts.push(id);
    }
    await open(page, `/administration/${entity}/edit/${id}`);
    await tab(page, 'Bilder');
    await uploadSaved(page, ['qa-landscape.jpg']);
    const item = items(page).first();
    await expect(item.locator('.image-label')).toHaveText('Desktop');

    await openCropper(page, item);
    expect(await stencilRatio(page)).toBeCloseTo(10 / 12, 1);
    await page.locator('.btn-cropper-format', { hasText: 'Mobile' }).click();
    await expect.poll(() => stencilRatio(page)).toBeCloseTo(3 / 2, 1);
    await page.locator('.upload-overlay-cropper a.btn-primary', { hasText: 'Speichern' }).click();
    await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();
    await expect(item.locator('.image-label')).toHaveText('Mobile');

    await save(page);
    const stored = (await api.get(`/api/${entity}/${id}`)).images[0];
    expect(stored).toMatchObject({ device: 'mobile' });
    expect(stored.coords_w).toBeGreaterThan(0);

    await open(page, `/administration/${entity}/edit/${id}`);
    await tab(page, 'Bilder');
    const answered = answerConfirm(page, true);
    await action(items(page).first(), 3).click();
    await answered;
    await expect(items(page)).toHaveCount(0);
    expect((await api.get(`/api/${entity}/${id}`)).images).toEqual([]);
  });
}

for (const entity of ['assistant', 'contact'] as const) {
  const name = entity === 'assistant' ? 'Assistenz' : 'Kontakt';

  test(`${name}: an image caption saves with the form`, async ({ page, api }) => {
    qa(entity === 'assistant' ? 'aa-images' : 'ak-images', 'im-caption');
    knownFinding('F19', 'saving fails (500) once an image has a caption: round() on the caption');
    const id = await createRecord(api, entity);
    await open(page, `/administration/${entity}/edit/${id}`);
    await tab(page, 'Bilder');
    await uploadSaved(page, ['qa-landscape.jpg']);
    await action(items(page).first(), 1).click();
    await page.locator('.upload-overlay-edit select[name=device]').selectOption('mobile');
    await page.locator('.upload-overlay-edit .form-row', { has: page.locator('label:text-is("Bildlegende")') }).locator('input').fill('QA-Legende');
    await page.keyboard.press('Escape');
    await save(page);
    expect((await api.get(`/api/${entity}/${id}`)).images[0].caption).toBe('QA-Legende');
  });

  test(`${name}: a record with images can be deleted`, async ({ page, api }) => {
    qa(entity === 'assistant' ? 'at-assist-list' : 'ak-list');
    knownFinding('F18', `deleting ${entity === 'assistant' ? 'an' : 'a'} ${name} that has images fails (500): no ON DELETE CASCADE`);
    const id = await createRecord(api, entity);
    await open(page, `/administration/${entity}/edit/${id}`);
    await tab(page, 'Bilder');
    await uploadSaved(page, ['qa-landscape.jpg']);
    const response = await api.request.delete(`/api/${entity}/${id}`, { headers: { 'X-XSRF-TOKEN': decodeURIComponent((await api.request.storageState()).cookies.find(c => c.name === 'XSRF-TOKEN')!.value) } });
    expect(response.status()).toBe(200);
  });
}

async function createRecord(api: Api, entity: 'assistant' | 'contact') {
  if (entity === 'assistant') {
    const { assistantId } = await api.post('/api/assistant', { team_id: 2, description: { de: '<p>QA-Assistenz Bilder</p>' }, assistants: {}, publish: 0, images: [] });
    created.assistants.push(assistantId);
    return assistantId as number;
  }
  const { contactId } = await api.post('/api/contact', { address: { de: '<p>QA-Kontakt Bilder</p>' }, imprint: {}, privacy: {}, map_uri: null, publish: 0, images: [] });
  created.contacts.push(contactId);
  return contactId as number;
}
