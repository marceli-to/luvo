import { test as base, expect, type APIRequestContext, type Page } from '@playwright/test';
import { baseURL, users } from './env';

export { expect };

/**
 * Links the running test to checklist ids (.rewrite/qa-checklist.json).
 */
export function qa(...ids: string[]) {
  for (const id of ids) {
    test.info().annotations.push({ type: 'qa', description: id });
  }
}

/**
 * Marks a test that checks a known finding (.rewrite/08-test-plan.md): it is
 * expected to fail until the finding is fixed.
 */
export function knownFinding(id: string, summary: string) {
  test.info().annotations.push({ type: 'finding', description: `${id}: ${summary}` });
  test.fail(true, `Known finding ${id}: ${summary}`);
}

type Strict = {
  /** Accept console errors / failed requests matching the pattern in this test. */
  allow(pattern: RegExp): void;
};

/** JSON API as the QA admin (Sanctum session + XSRF header). */
export class Api {
  constructor(public readonly request: APIRequestContext) {}

  private async headers() {
    const cookie = (await this.request.storageState()).cookies.find(c => c.name === 'XSRF-TOKEN');
    return cookie ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie.value) } : {};
  }

  async call(method: 'get' | 'post' | 'put' | 'delete', url: string, data?: unknown) {
    const response = await this.request.fetch(url, { method: method.toUpperCase(), data, headers: await this.headers() });
    if (!response.ok()) {
      throw new Error(`${method.toUpperCase()} ${url} → ${response.status()}: ${(await response.text()).slice(0, 300)}`);
    }
    const text = await response.text();
    return text ? JSON.parse(text) : null;
  }

  get = (url: string) => this.call('get', url);
  post = (url: string, data?: unknown) => this.call('post', url, data);
  put = (url: string, data?: unknown) => this.call('put', url, data);
  delete = (url: string) => this.call('delete', url);
}

export const test = base.extend<{ strict: Strict; api: Api }>({
  // Every test fails on console errors, uncaught page errors and responses
  // >= 400, unless it allows them (404 page, 401/422 checks).
  strict: [async ({ context }, use, testInfo) => {
    const problems: string[] = [];
    const allowed: RegExp[] = [];
    const watch = (page: Page) => {
      page.on('console', message => {
        if (message.type() === 'error') problems.push(`console.error: ${message.text()} @ ${message.location().url}`);
      });
      page.on('pageerror', error => problems.push(`page error: ${error.message}`));
      page.on('response', response => {
        if (response.status() >= 400) problems.push(`HTTP ${response.status()} ${response.request().method()} ${response.url()}`);
      });
    };
    context.pages().forEach(watch);
    context.on('page', watch);

    await use({ allow: pattern => allowed.push(pattern) });

    const unexpected = problems.filter(problem => !allowed.some(pattern => pattern.test(problem)));
    if (testInfo.status === 'passed') {
      expect(unexpected, 'console errors, page errors or failed requests').toEqual([]);
    }
  }, { auto: true }],

  api: async ({ playwright }, use) => {
    const request = await playwright.request.newContext({
      baseURL,
      storageState: users.admin.state,
      extraHTTPHeaders: { Accept: 'application/json', Referer: `${baseURL}/administration`, 'X-Requested-With': 'XMLHttpRequest' },
    });
    await use(new Api(request));
    await request.dispose();
  },
});
