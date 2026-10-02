import fs from 'node:fs';
import path from 'node:path';
import { root } from './env';

const storageApp = path.join(root, 'storage/app');

export type StorageSnapshot = { files: string[]; dirs: string[] };

/** Every file and directory below storage/app, as relative paths. */
export function snapshotStorage(dir = storageApp, snapshot: StorageSnapshot = { files: [], dirs: [] }): StorageSnapshot {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isSymbolicLink()) continue;
    if (entry.isDirectory()) {
      snapshot.dirs.push(path.relative(storageApp, full));
      snapshotStorage(full, snapshot);
    }
    else {
      snapshot.files.push(path.relative(storageApp, full));
    }
  }
  return snapshot;
}

/**
 * Deletes the files and directories below storage/app that weren't there
 * before. Nothing that existed before the run is touched.
 */
export function removeNewFiles(before: StorageSnapshot): string[] {
  const now = snapshotStorage();
  const files = new Set(before.files);
  const dirs = new Set(before.dirs);

  const removed = now.files.filter(file => !files.has(file));
  removed.forEach(file => fs.unlinkSync(path.join(storageApp, file)));

  // Deepest first, and only if empty (a file we keep may sit in a new dir)
  now.dirs.filter(dir => !dirs.has(dir)).sort((a, b) => b.length - a.length).forEach(dir => {
    const full = path.join(storageApp, dir);
    if (fs.readdirSync(full).length === 0) fs.rmdirSync(full);
  });

  return removed;
}
