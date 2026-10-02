import { defineConfig, devices } from '@playwright/test';
import { baseURL, users } from './tests/e2e/support/env';

/**
 * E2E run against a local server on luvo_e2e (.env.e2e). The visual
 * comparison with production has its own config: playwright.visual.config.ts.
 *
 * Projects: public pages in parallel at 1280×800, 1280×1000 (tall, member
 * image) and 375×812; admin tests afterwards, one at a time, because they
 * change records the public pages show.
 */
const desktop = { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 800 } };
const phone = { ...devices['Desktop Chrome'], viewport: { width: 375, height: 812 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 };

export default defineConfig({
  testDir: './tests/e2e',
  globalSetup: './tests/e2e/global-setup.ts',
  globalTeardown: './tests/e2e/global-teardown.ts',
  outputDir: './test-results/e2e',
  timeout: 60_000,
  expect: { timeout: 10_000 },
  workers: 4,
  retries: 0,
  reporter: [['list'], ['./tests/e2e/reporters/qa-results.ts', { output: 'tests/.results/e2e.json' }]],
  use: {
    baseURL,
    locale: 'de-CH',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'public-desktop', testDir: './tests/e2e/public', use: desktop, grepInvert: /@(phone|tall)-only/ },
    { name: 'public-tall', testDir: './tests/e2e/public', use: { ...desktop, viewport: { width: 1280, height: 1000 } }, grep: /@tall/ },
    { name: 'public-phone', testDir: './tests/e2e/public', use: phone, grep: /@phone/ },
    {
      name: 'admin',
      testDir: './tests/e2e/admin',
      use: { ...desktop, storageState: users.admin.state },
      grepInvert: /@phone-only/,
      workers: 1,
      dependencies: ['public-desktop', 'public-tall', 'public-phone'],
    },
    {
      name: 'admin-phone',
      testDir: './tests/e2e/admin',
      use: { ...phone, storageState: users.admin.state },
      grep: /@phone/,
      workers: 1,
      // Read only: needn't wait for (or be skipped by) the admin project
      dependencies: ['public-desktop', 'public-tall', 'public-phone'],
    },
  ],
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8010 --no-reload',
    url: `${baseURL}/login`,
    env: { APP_ENV: 'e2e', PHP_CLI_SERVER_WORKERS: '6' },
    reuseExistingServer: false,
    timeout: 60_000,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
