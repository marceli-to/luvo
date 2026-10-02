import type { Locator, Page } from '@playwright/test';
import { expect } from './fixtures';

/**
 * Answers the next window.confirm (the app's delete confirmation) and checks
 * its text. Returns a promise that resolves once the dialog was handled.
 */
export function answerConfirm(page: Page, accept: boolean): Promise<void> {
  return new Promise(resolve => {
    page.once('dialog', async dialog => {
      expect(dialog.type()).toBe('confirm');
      expect(dialog.message()).toBe('Bitte löschen bestätigen!');
      await (accept ? dialog.accept() : dialog.dismiss());
      resolve();
    });
  });
}

/** A toast with the given text (success or error). */
export function toast(page: Page, text: string | RegExp, type: 'success' | 'error' = 'success'): Locator {
  return page.locator(`.notification.${type}`).filter({ hasText: text });
}

/** Opens an admin URL and waits until the screen has loaded its data. */
export async function open(page: Page, url: string) {
  await page.goto(url);
  await expect(page.locator('.loading-indicator')).toHaveCount(0);
  await expect(page.locator('main.site h1').first()).toBeVisible();
}

/** The visible form row whose label is `label` (a trailing * is ignored). */
export function row(page: Page, label: string): Locator {
  const escaped = label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  return page.locator('.form-row:visible').filter({
    has: page.locator('> label', { hasText: new RegExp(`^\\s*${escaped}\\s*\\*?\\s*$`) }),
  });
}

/** The Tiptap editor (contenteditable) in the visible row `label`. */
export const editor = (page: Page, label: string) => row(page, label).locator('.ProseMirror');

/** Replaces an editor's content by typing. */
export async function typeInto(editorLocator: Locator, text: string) {
  await editorLocator.click();
  await editorLocator.press('ControlOrMeta+a');
  await editorLocator.press('Backspace');
  await editorLocator.pressSequentially(text);
}

export async function tab(page: Page, label: 'Daten' | 'Bilder' | 'Einstellungen') {
  await page.locator('nav.tabs a', { hasText: label }).click();
}

export async function language(page: Page, locale: 'de' | 'fr' | 'en') {
  await page.locator('nav.language a', { hasText: locale.toUpperCase() }).click();
}

export async function publishRadio(page: Page, value: 0 | 1) {
  await tab(page, 'Einstellungen');
  await page.locator(`label[for="publish_${value}"]`).click();
}

/** Clicks Speichern and waits for the API answer. */
export async function save(page: Page, expectStatus = 200) {
  const response = page.waitForResponse(r => /\/api\//.test(r.url()) && ['POST', 'PUT'].includes(r.request().method()));
  await page.locator('footer.module-footer button[type=submit]', { hasText: 'Speichern' }).click();
  expect((await response).status()).toBe(expectStatus);
}

/** Uploads files through the Dropzone on the current screen. */
export async function upload(page: Page, files: string | string[], scope: Locator | Page = page) {
  await scope.locator('input.dz-hidden-input').first().setInputFiles(files);
}

/** A listing row containing the text. */
export const listItem = (page: Page, text: string | RegExp) => page.locator('.listing__item').filter({ hasText: text });

/**
 * Drags `source` onto `target` in a vuedraggable / SortableJS list (native
 * HTML5 drag and drop). Tries locator.dragTo() first, then page.mouse with
 * small steps, then synthetic dragstart / dragover / drop events, until
 * `moved()` reports the new order. Returns the strategy that worked.
 */
export async function dragAndDrop(page: Page, source: Locator, target: Locator, moved: () => Promise<boolean>): Promise<string> {
  const strategies: Record<string, () => Promise<void>> = {
    dragTo: () => source.dragTo(target, { targetPosition: { x: 20, y: 5 } }),
    mouse: async () => {
      const from = (await source.boundingBox())!;
      const to = (await target.boundingBox())!;
      await page.mouse.move(from.x + 20, from.y + from.height / 2);
      await page.mouse.down();
      for (let i = 1; i <= 15; i++) {
        await page.mouse.move(from.x + 20, from.y + from.height / 2 + ((to.y + 5) - (from.y + from.height / 2)) * i / 15);
        await page.waitForTimeout(20);
      }
      await page.mouse.up();
    },
    events: async () => {
      const handle = await target.elementHandle();
      await source.evaluate(async (el, targetEl) => {
        const data = new DataTransfer();
        const rect = targetEl!.getBoundingClientRect();
        const at = { clientX: rect.left + 20, clientY: rect.top + 5, bubbles: true, cancelable: true, dataTransfer: data };
        el.dispatchEvent(new DragEvent('dragstart', at));
        await new Promise(resolve => setTimeout(resolve, 50));
        targetEl!.dispatchEvent(new DragEvent('dragenter', at));
        targetEl!.dispatchEvent(new DragEvent('dragover', at));
        await new Promise(resolve => setTimeout(resolve, 50));
        targetEl!.dispatchEvent(new DragEvent('drop', at));
        el.dispatchEvent(new DragEvent('dragend', at));
      }, handle);
    },
  };
  for (const [name, run] of Object.entries(strategies)) {
    await run();
    if (await expect.poll(moved, { timeout: 2_000 }).toBe(true).then(() => true, () => false)) {
      return name;
    }
  }
  throw new Error('Drag and drop: no strategy changed the order');
}

/** The screen's heading with that text (list screens have several h1). */
export const heading = (page: Page, text: string | RegExp) =>
  page.locator('main.site h1', { hasText: typeof text === 'string' ? new RegExp(`^\\s*${text}\\s*$`) : text });
