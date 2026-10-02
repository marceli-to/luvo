#!/usr/bin/env node
/**
 * Merges the partial results of the last PHPUnit, E2E and visual runs
 * (tests/.results/*.json) with the checklist into tests/qa-results.json:
 *
 *   { run, commit, results: { <id>: { status: pass|fail|manual, tests, error } } }
 *
 * fail: a linked test failed, or one documents a known finding (error
 * "Known finding F<n>: ..."). manual: no automated test. Exits 1 if a test
 * names an id that isn't in the checklist, or if any id failed.
 */
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const checklist = JSON.parse(fs.readFileSync(path.join(root, '.rewrite/qa-checklist.json'), 'utf8'));
const ids = checklist.sections.flatMap(section => section.items.map(item => item.id));

const suites = ['phpunit', 'e2e', 'visual'].flatMap(suite => {
  const file = path.join(root, 'tests/.results', `${suite}.json`);
  if (!fs.existsSync(file)) {
    console.warn(`[qa] no results for ${suite} yet (${path.relative(root, file)})`);
    return [];
  }
  const data = JSON.parse(fs.readFileSync(file, 'utf8'));
  console.log(`[qa] ${suite}: ${data.tests.length} tests from ${data.run}`);
  return [data];
});

const tests = suites.flatMap(suite => suite.tests.map(test => {
  // PHPUnit reports known findings as skipped "Known finding F<n>: ..."
  const known = test.status === 'known' || (test.status === 'skipped' && /^Known finding/.test(test.error ?? ''));
  return { ...test, suite: suite.suite, status: known ? 'known' : test.status };
}));

const unknown = [...new Set(tests.flatMap(test => test.ids).filter(id => !ids.includes(id)))];
if (unknown.length) console.error(`[qa] tests name ids that aren't in the checklist: ${unknown.join(', ')}`);

const results = {};
for (const id of ids) {
  const linked = tests.filter(test => test.ids.includes(id) && test.status !== 'skipped');
  const failed = linked.find(test => test.status === 'failed');
  // Every known finding behind the id, e.g. "Known finding F4: …; Known finding F18: …"
  const known = [...new Set(linked.filter(test => test.status === 'known').map(test => test.error?.startsWith('Known finding') ? test.error : `Known finding: ${test.error}`))].join('; ');
  results[id] = {
    status: linked.length === 0 ? 'manual' : failed || known ? 'fail' : 'pass',
    tests: linked.map(test => `${test.suite}: ${test.title}`),
    error: failed?.error ?? (known || null),
  };
}

let commit = 'unknown';
try { commit = execFileSync('git', ['rev-parse', 'HEAD'], { cwd: root, encoding: 'utf8' }).trim(); } catch {}

const output = { run: new Date().toISOString(), commit, results };
fs.writeFileSync(path.join(root, 'tests/qa-results.json'), JSON.stringify(output, null, 2) + '\n');

const count = status => Object.values(results).filter(result => result.status === status).length;
console.log(`[qa] tests/qa-results.json: ${ids.length} ids, ${count('pass')} pass, ${count('fail')} fail, ${count('manual')} manual`);
const unexpected = Object.entries(results).filter(([, result]) => result.status === 'fail' && !result.error?.startsWith('Known finding'));
if (unexpected.length) console.error(`[qa] failing (not a known finding): ${unexpected.map(([id]) => id).join(', ')}`);

process.exit(unknown.length || unexpected.length ? 1 : 0);
