import fs from 'node:fs';
import path from 'node:path';

export const root = path.resolve(import.meta.dirname, '../../..');
export const stateDir = path.join(root, 'tests/.state');
export const fixturesDir = path.join(root, 'tests/.fixtures');
export const baseURL = 'http://127.0.0.1:8010';

// .env.e2e into process.env (QA_* credentials, DB name)
if (fs.existsSync(path.join(root, '.env.e2e'))) {
  process.loadEnvFile(path.join(root, '.env.e2e'));
}

export const users = {
  admin: { email: process.env.QA_ADMIN_EMAIL!, password: process.env.QA_ADMIN_PASSWORD!, state: path.join(stateDir, 'admin.json') },
  user: { email: process.env.QA_USER_EMAIL!, password: process.env.QA_USER_PASSWORD!, state: path.join(stateDir, 'user.json') },
  unverified: { email: process.env.QA_UNVERIFIED_EMAIL!, password: process.env.QA_UNVERIFIED_PASSWORD!, state: path.join(stateDir, 'unverified.json') },
};

export const fixture = (name: string) => path.join(fixturesDir, name);
