import { defineConfig, devices } from '@playwright/test';
import { baseURL } from './tests/e2e/support/env';

/**
 * Local site vs production (https://luksundvogt.ch), read only: GET
 * requests only, at most two in flight (tests/visual/production.ts), one
 * worker. Same local server and setup as the E2E run.
 */
export default defineConfig({
  testDir: './tests/visual',
  globalSetup: './tests/e2e/global-setup.ts',
  globalTeardown: './tests/e2e/global-teardown.ts',
  outputDir: './test-results/visual-output',
  timeout: 90_000,
  workers: 1,
  retries: 0,
  reporter: [['list'], ['./tests/e2e/reporters/qa-results.ts', { output: 'tests/.results/visual.json', suite: 'visual' }]],
  use: { baseURL, locale: 'de-CH', screenshot: 'off', trace: 'off' },
  projects: [
    { name: 'visual-1280', use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 800 } } },
    { name: 'visual-375', use: { ...devices['Desktop Chrome'], viewport: { width: 375, height: 812 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 } },
  ],
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8010 --no-reload',
    url: `${baseURL}/login`,
    env: { APP_ENV: 'e2e', PHP_CLI_SERVER_WORKERS: '4' },
    reuseExistingServer: false,
    timeout: 60_000,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
