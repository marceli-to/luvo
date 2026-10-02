import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { request } from '@playwright/test';
import { test, expect, qa } from '../support/fixtures';
import { baseURL, root, users } from '../support/env';

const hashes = (dir: string) => Object.fromEntries(
  (fs.readdirSync(dir, { recursive: true, withFileTypes: true }) as fs.Dirent[])
    .filter(entry => entry.isFile())
    .map(entry => {
      const file = path.join(entry.parentPath, entry.name);
      return [path.relative(dir, file), createHash('md5').update(fs.readFileSync(file)).digest('hex')];
    })
);

test('public/build is current and committed', { tag: '@preflight' }, async () => {
  qa('setup-build');
  test.setTimeout(180_000);

  const out = fs.mkdtempSync(path.join(os.tmpdir(), 'luvo-build-'));
  try {
    execFileSync('npx', ['vite', 'build', '--outDir', out, '--emptyOutDir', '--logLevel', 'error'], { cwd: root, stdio: 'pipe' });
    expect(hashes(out), 'a fresh build differs from public/build: run npm run build and commit').toEqual(hashes(path.join(root, 'public/build')));
  }
  finally {
    fs.rmSync(out, { recursive: true, force: true });
  }
  expect(execFileSync('git', ['status', '--porcelain', 'public/build'], { cwd: root, encoding: 'utf8' }), 'uncommitted changes in public/build').toBe('');
});

test('the three QA users get the expected access', async () => {
  qa('setup-users');

  const expectations = [
    { user: users.admin, status: 200, url: '/administration' },
    { user: users.user, status: 403, url: '/administration' },
    { user: users.unverified, status: 302, url: '/email/verify' },
  ];
  for (const { user, status, url } of expectations) {
    const context = await request.newContext({ baseURL, storageState: user.state });
    const response = await context.get('/administration', { maxRedirects: 0 });
    expect(response.status(), user.email).toBe(status);
    if (status === 302) expect(response.headers().location).toContain(url);
    await context.dispose();
  }
});
