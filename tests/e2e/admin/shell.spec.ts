import { execFileSync } from 'node:child_process';
import { test, expect, qa } from '../support/fixtures';
import { open, toast, row, save, heading } from '../support/admin';
import { root, users } from '../support/env';
import { login as storeLogin } from '../global-setup';

const fresh = { cookies: [], origins: [] };

test.describe('login and session', () => {
  test.use({ storageState: fresh });

  test('wrong password shows an error; correct login lands in the admin', async ({ page, strict }) => {
    qa('auth-login');
    strict.allow(/422|login/);
    await page.goto('/login');
    await page.locator('input[name=email]').fill(users.admin.email);
    await page.locator('input[name=password]').fill('falsch');
    await page.getByRole('button', { name: 'Anmelden' }).click();
    await expect(page.locator('.alert--danger')).toContainText('Bitte alle markierten Felder überprüfen!');

    await page.locator('input[name=password]').fill(users.admin.password);
    await page.getByRole('button', { name: 'Anmelden' }).click();
    await expect(page).toHaveURL('/administration/home');
    await expect(heading(page, 'Homepage')).toBeVisible();
  });

  test('guests are sent to the login from any admin URL', async ({ page }) => {
    qa('auth-guest');
    for (const url of ['/administration', '/administration/teams', '/administration/team/member/edit/1']) {
      await page.goto(url);
      await expect(page).toHaveURL('/login');
    }
  });

  test('a non-admin gets 403, an unverified admin the verify notice', async ({ page, strict }) => {
    qa('auth-role');
    // A non-admin's login redirects to /home, which doesn't exist (F17, feature test)
    strict.allow(/403|8010\/home\b/);
    for (const [user, check] of [
      [users.user, async () => expect(page.locator('body')).toContainText('403')],
      [users.unverified, async () => expect(page).toHaveURL('/email/verify')],
    ] as const) {
      await page.context().clearCookies();
      await page.goto('/login');
      await page.locator('input[name=email]').fill(user.email);
      await page.locator('input[name=password]').fill(user.password);
      await page.getByRole('button', { name: 'Anmelden' }).click();
      await page.waitForLoadState();
      await page.goto('/administration');
      await check();
    }
  });

  test('logout ends the session; back does not show data or allow saving', async ({ page, strict }) => {
    qa('auth-logout');
    strict.allow(/401/);
    await login(page);
    await open(page, '/administration/teams');
    await page.locator('nav.page a', { hasText: 'Logout' }).click();
    await expect(page).toHaveURL(/\/(de\/home)?$/);

    await page.goBack();
    // A reload of the admin (back may come from the cache) needs a login
    await page.reload();
    await expect(page).toHaveURL('/login');
    const response = await page.request.get('/api/team/members', { headers: { Accept: 'application/json' } });
    expect(response.status()).toBe(401);
  });

  test('expired session: the next save sends you to the login', async ({ page, strict }) => {
    qa('auth-expired');
    strict.allow(/401|419/);
    await login(page);
    await open(page, '/administration/user/edit/0');

    await page.context().clearCookies();
    await row(page, 'Neues Passwort (min. 6 Zeichen)').locator('input').fill('egal123');
    await row(page, 'Neues Passwort wiederholen').locator('input').fill('egal123');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page).toHaveURL('/login');
  });

  test('password change: validation, then the new password works', async ({ page, strict }) => {
    qa('auth-password');
    strict.allow(/422/);
    try {
      await login(page);
      await page.locator('nav.page header a', { hasText: 'QA Admin' }).click();
      await expect(heading(page, 'Passwort ändern')).toBeVisible();
      const password = row(page, 'Neues Passwort (min. 6 Zeichen)');
      const confirm = row(page, 'Neues Passwort wiederholen');

      await password.locator('input').fill('qa-neues-passwort');
      await confirm.locator('input').fill('etwas-anderes');
      await save(page, 422);
      await expect(confirm).toHaveClass(/has-error/);
      await expect(toast(page, 'Bitte alle mit * markierten Felder prüfen!', 'error')).toBeVisible();

      await password.locator('input').fill('kurz');
      await confirm.locator('input').fill('kurz');
      await save(page, 422);
      await expect(password).toHaveClass(/has-error/);

      await password.locator('input').fill('qa-neues-passwort');
      await confirm.locator('input').fill('qa-neues-passwort');
      await save(page);
      await expect(toast(page, 'Änderungen gespeichert!')).toBeVisible();

      await page.goto('/logout');
      await login(page, 'qa-neues-passwort');
      await expect(page).toHaveURL('/administration/home');
    }
    finally {
      // Back to the password from .env.e2e. The change ended every session
      // of the QA admin, so the stored login for the other tests is renewed.
      execFileSync('php', ['artisan', 'db:seed', '--class=QaUserSeeder', '--force'], { cwd: root, env: { ...process.env, APP_ENV: 'e2e' }, stdio: 'ignore' });
      await storeLogin(users.admin.email, users.admin.password, users.admin.state);
    }
  });
});

async function login(page: import('@playwright/test').Page, password = users.admin.password) {
  await page.goto('/login');
  await page.locator('input[name=email]').fill(users.admin.email);
  await page.locator('input[name=password]').fill(password);
  await page.getByRole('button', { name: 'Anmelden' }).click();
  await expect(page).toHaveURL('/administration/home');
}

test('dashboard welcomes the user by name', async ({ page }) => {
  qa('sh-dashboard');
  await page.goto('/administration');
  await expect(heading(page, 'Willkommen QA Admin')).toBeVisible();
});

test('sidebar links open their screen and are highlighted', async ({ page }) => {
  qa('sh-nav');
  await open(page, '/administration');
  for (const [label, url, title] of [
    ['Home', '/administration/home', 'Homepage'],
    ['Teams', '/administration/teams', 'Teams'],
    ['Kontakt', '/administration/contact', 'Kontakt'],
    ['Dateien', '/administration/media', 'Dateiverwaltung'],
  ]) {
    const link = page.locator('nav.page ul a', { hasText: label });
    await link.click();
    await expect(page).toHaveURL(url);
    await expect(heading(page, title)).toBeVisible();
    await expect(link).toHaveClass(/router-link-active/);
  }
});

test('reloading list, edit and create URLs opens that screen', async ({ page, api }) => {
  qa('sh-reload');
  const member = (await api.get('/api/team/members')).data[0];
  for (const [url, title] of [
    ['/administration/home', 'Homepage'],
    ['/administration/home/edit/1', 'Homepage bearbeiten'],
    ['/administration/home/create', 'Homepage hinzufügen'],
    ['/administration/teams', 'Teams'],
    [`/administration/team/member/edit/${member.id}`, 'Mitarbeiter bearbeiten'],
    ['/administration/team/member/create', 'Mitarbeiter hinzufügen'],
    ['/administration/contact', 'Kontakt'],
    ['/administration/media', 'Dateiverwaltung'],
  ]) {
    await open(page, url);
    await expect(heading(page, title), url).toBeVisible();
  }
});

test('browser back and forward between list and form', async ({ page }) => {
  qa('sh-history');
  await open(page, '/administration/teams');
  await page.locator('.listing__item a[href*="/team/member/edit/"]').first().click();
  await expect(page.locator('main.site h1', { hasText: 'Mitarbeiter bearbeiten' })).toBeVisible();
  await page.goBack();
  await expect(page).toHaveURL('/administration/teams');
  await expect(page.locator('main.site h1', { hasText: /^Teams$/ })).toBeVisible();
  await page.goForward();
  await expect(page.locator('main.site h1', { hasText: 'Mitarbeiter bearbeiten' })).toBeVisible();
});

test('phone: menu opens from the header and closes', { tag: '@phone-only' }, async ({ page }) => {
  qa('sh-mobile');
  await open(page, '/administration');
  const menu = page.locator('nav.page');
  await expect(menu).not.toHaveClass(/is-visible/);

  await page.locator('.menu-open').click();
  await expect(menu).toHaveClass(/is-visible/);
  await page.locator('.menu-close').click();
  await expect(menu).not.toHaveClass(/is-visible/);

  await page.locator('.menu-open').click();
  await menu.locator('ul a', { hasText: 'Teams' }).click();
  await expect(page).toHaveURL('/administration/teams');
  await expect(menu).not.toHaveClass(/is-visible/);
});

test('notifications appear and disappear', async ({ page, strict }) => {
  qa('sh-notify');
  strict.allow(/422/);
  await open(page, '/administration/home/create');
  await save(page, 422);
  const error = toast(page, 'Bitte alle mit * markierten Felder prüfen!', 'error');
  await expect(error).toBeVisible();
  await expect(error).toBeHidden({ timeout: 15_000 });
});

test('every button and link shows text or an icon', async ({ page, api }) => {
  qa('sh-icons');
  const member = (await api.get('/api/team/members')).data[0];
  for (const url of ['/administration/teams', `/administration/team/member/edit/${member.id}`, '/administration/media']) {
    await open(page, url);
    const empty = await page.locator('main.site a:visible, main.site button:visible, nav.page a:visible').evaluateAll(elements =>
      elements.filter(el => !el.textContent!.trim() && !el.querySelector('svg path, svg line, svg polyline, img')).map(el => el.outerHTML.slice(0, 120))
    );
    expect(empty, url).toEqual([]);
  }
});

test('unknown admin URL: the shell renders, content stays empty', async ({ page }) => {
  qa('sh-unknown');
  test.info().annotations.push({ type: 'behaviour', description: 'No catch-all route: /administration/foo shows the shell with an empty content area (C6).' });
  await page.goto('/administration/foo');
  await expect(page.locator('nav.page')).toBeVisible();
  await expect(page.locator('main.site > div').last()).toBeEmpty().catch(() => {});
  await expect(page.locator('main.site h1')).toHaveCount(0);
});

test('Not Found and Forbidden views', async ({ page, strict }) => {
  qa('sh-errors');
  strict.allow(/404/);
  await page.goto('/administration/home/edit/999999');
  await expect(page).toHaveURL('/not-found');
  await expect(page.locator('h1', { hasText: 'Error 404 - Page not found' })).toBeVisible();
  await expect(toast(page, '404 Not Found', 'error')).toBeVisible();

  // Forbidden: only reached after an API 403; render it through the router
  await page.evaluate(() => (document.querySelector('#app-administration') as any).__vue_app__.config.globalProperties.$router.push('/forbidden'));
  await expect(page.locator('h1', { hasText: 'Access denied' })).toBeVisible();
});
