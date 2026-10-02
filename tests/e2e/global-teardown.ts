import fs from 'node:fs';
import path from 'node:path';
import { stateDir } from './support/env';
import { checksum, cleanupQaRecords } from './support/db';
import { removeNewFiles } from './support/storage';

/**
 * After the E2E run: delete leftover QA- records (a warning: a test didn't
 * clean up), delete files that are new in storage/app, and fail the run if
 * the content tables differ from before.
 */
export default async function globalTeardown() {
  const leftovers = cleanupQaRecords();
  if (Object.keys(leftovers).length) {
    console.warn('[qa] QA- records left behind by tests, now deleted:', leftovers);
  }

  const before = JSON.parse(fs.readFileSync(path.join(stateDir, 'storage-before.json'), 'utf8'));
  const removed = removeNewFiles(before);
  console.log(`[qa] removed ${removed.length} new files from storage/app`);

  const expected = JSON.parse(fs.readFileSync(path.join(stateDir, 'checksum-before.json'), 'utf8'));
  const actual = checksum();
  const changed = Object.keys(expected).filter(table => JSON.stringify(expected[table]) !== JSON.stringify(actual[table]));
  fs.rmSync(path.join(stateDir, 'snapshots'), { recursive: true, force: true });
  if (changed.length) {
    throw new Error(`[qa] luvo_e2e changed during the run (cleanup missing): ${changed.map(t => `${t} ${JSON.stringify(expected[t])} → ${JSON.stringify(actual[t])}`).join('; ')}`);
  }
}
