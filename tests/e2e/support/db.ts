import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { root, stateDir } from './env';

const helper = path.join(root, 'tests/e2e/support/db.php');

function run(...args: string[]): any {
  return JSON.parse(execFileSync('php', [helper, ...args], { cwd: root, encoding: 'utf8' }));
}

/** Rows of a SELECT against luvo_e2e. */
export function select<T = Record<string, any>>(sql: string): T[] {
  return run('select', sql);
}

export const checksum = () => run('checksum');
export const cleanupQaRecords = () => run('cleanup');

let counter = 0;

/**
 * Saves rows of a table; the returned function puts them back exactly
 * (all columns), re-inserting deleted ones. Use for existing records a test
 * has to change.
 */
export function snapshot(table: string, ids: number[]): () => void {
  const file = path.join(stateDir, 'snapshots', `${process.pid}-${Date.now()}-${counter++}-${table}.json`);
  run('snapshot', file, table, ids.join(','));
  return () => run('restore', file);
}
