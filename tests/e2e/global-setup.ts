import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { request } from '@playwright/test';
import { baseURL, fixture, fixturesDir, root, stateDir, users } from './support/env';
import { checksum, select } from './support/db';
import { snapshotStorage } from './support/storage';

/**
 * Before the E2E run (the server is already up): refuse a wrong database,
 * seed the QA users, generate upload fixtures, record storage files and a
 * checksum of the content tables (compared in global teardown), log in.
 */
export default async function globalSetup() {
  if (!process.env.DB_DATABASE?.endsWith('_e2e')) {
    throw new Error('.env.e2e must set DB_DATABASE=luvo_e2e. See .env.e2e.example.');
  }
  if (fs.existsSync(path.join(root, 'bootstrap/cache/config.php'))) {
    throw new Error('bootstrap/cache/config.php exists: the server would use the cached (dev) config. Run php artisan config:clear.');
  }
  // The running server must use luvo_e2e: the QA admin exists only there after seeding
  fs.mkdirSync(stateDir, { recursive: true });
  execFileSync('php', ['artisan', 'db:seed', '--class=QaUserSeeder', '--force'], { cwd: root, env: { ...process.env, APP_ENV: 'e2e' }, stdio: 'ignore' });
  if (select("SELECT id FROM users WHERE email = '" + users.admin.email.replace(/'/g, '') + "'").length !== 1) {
    throw new Error('QA admin missing in luvo_e2e. Run npm run test:e2e-db.');
  }

  generateFixtures();

  fs.writeFileSync(path.join(stateDir, 'storage-before.json'), JSON.stringify(snapshotStorage()));
  fs.writeFileSync(path.join(stateDir, 'checksum-before.json'), JSON.stringify(checksum()));

  for (const user of Object.values(users)) {
    await login(user.email, user.password, user.state);
  }
}

export async function login(email: string, password: string, state: string) {
  const context = await request.newContext({ baseURL });
  const form = await (await context.get('/login')).text();
  const token = form.match(/name="_token" value="([^"]+)"/)?.[1];
  const response = await context.post('/login', { form: { _token: token!, email, password }, maxRedirects: 0 });
  if (response.status() !== 302 || response.headers().location?.includes('/login')) {
    throw new Error(`Login failed for ${email} (${response.status()})`);
  }
  await context.storageState({ path: state });
  await context.dispose();
}

function magick(...args: string[]) {
  execFileSync('magick', args, { stdio: 'ignore' });
}

/** Upload fixtures, generated once (nothing over 1 MB is committed). */
function generateFixtures() {
  fs.mkdirSync(fixturesDir, { recursive: true });
  const quadrants = (w: number, h: number, file: string, colours = ['red', 'green', 'blue', 'yellow']) => {
    if (fs.existsSync(fixture(file))) return;
    const [a, b, c, d] = colours;
    magick('-size', `${w / 2}x${h / 2}`, `xc:${a}`, `xc:${b}`, '+append', '(', '-size', `${w / 2}x${h / 2}`, `xc:${c}`, `xc:${d}`, '+append', ')', '-append', fixture(file));
  };
  quadrants(1600, 1000, 'qa-landscape.jpg');
  quadrants(1000, 1500, 'qa-portrait.jpeg', ['orange', 'purple', 'cyan', 'magenta']);
  quadrants(1200, 1200, 'qa-square.png', ['navy', 'olive', 'teal', 'maroon']);
  quadrants(1600, 1000, 'qa-second.jpg', ['black', 'white', 'gray', 'pink']);
  quadrants(1600, 1000, 'qa-third.jpg', ['brown', 'gold', 'lime', 'silver']);
  if (!fs.existsSync(fixture('qa-anim.gif'))) magick('-size', '200x200', 'xc:red', fixture('qa-anim.gif'));
  if (!fs.existsSync(fixture('qa-big.jpg'))) {
    magick('-size', '3200x3200', 'xc:gray', '+noise', 'Random', '-quality', '100', fixture('qa-big.jpg'));
  }
  if (fs.statSync(fixture('qa-big.jpg')).size <= 8.5 * 1024 * 1024) throw new Error('qa-big.jpg must be over 8 MB');

  fs.writeFileSync(fixture('qa-merkblatt.pdf'), pdf('QA Merkblatt'));
  fs.writeFileSync(fixture('qa-dialog.pdf'), pdf('QA Dialog'));
  // 17 MB: a valid PDF followed by padding (Dropzone only looks at the size)
  if (!fs.existsSync(fixture('qa-big.pdf'))) {
    fs.writeFileSync(fixture('qa-big.pdf'), Buffer.concat([pdf('QA gross'), Buffer.alloc(17 * 1024 * 1024, 0x20)]));
  }
  fs.writeFileSync(fixture('qa-notiz.txt'), 'QA text file\n');
}

function pdf(text: string): Buffer {
  const objects = [
    '<< /Type /Catalog /Pages 2 0 R >>',
    '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
    '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
    null,
    '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
  ];
  const stream = `BT /F1 24 Tf 72 760 Td (${text}) Tj ET`;
  objects[3] = `<< /Length ${stream.length} >>\nstream\n${stream}\nendstream`;
  let body = '%PDF-1.4\n';
  const offsets: number[] = [];
  objects.forEach((object, i) => {
    offsets.push(body.length);
    body += `${i + 1} 0 obj\n${object}\nendobj\n`;
  });
  const xref = body.length;
  body += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
  body += offsets.map(o => `${String(o).padStart(10, '0')} 00000 n \n`).join('');
  body += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF\n`;
  return Buffer.from(body, 'latin1');
}
