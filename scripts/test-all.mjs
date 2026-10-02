#!/usr/bin/env node
/**
 * npm run test:all: PHPUnit, E2E, visual, then tests/qa-results.json.
 * Every step runs even if an earlier one failed; the exit code is the
 * worst of them.
 */
import { spawnSync } from 'node:child_process';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const steps = [
  ['composer', ['test']],
  ['npx', ['playwright', 'test']],
  ['npx', ['playwright', 'test', '--config', 'playwright.visual.config.ts']],
  ['node', ['scripts/qa-results.mjs']],
];

let exit = 0;
for (const [command, args] of steps) {
  console.log(`\n▶ ${command} ${args.join(' ')}`);
  const { status } = spawnSync(command, args, { cwd: root, stdio: 'inherit' });
  exit = Math.max(exit, status ?? 1);
}
process.exit(exit);
