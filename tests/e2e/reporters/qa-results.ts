import fs from 'node:fs';
import path from 'node:path';
import type { FullResult, Reporter, TestCase, TestResult } from '@playwright/test/reporter';

type Entry = { title: string; ids: string[]; status: 'passed' | 'failed' | 'known' | 'skipped'; error: string | null };

/**
 * Writes each test's outcome with its `qa` annotations (checklist ids) to a
 * JSON file, merged into tests/qa-results.json by scripts/qa-results.mjs.
 * A test marked with knownFinding() that fails as expected is "known".
 */
export default class QaResultsReporter implements Reporter {
  private tests: Entry[] = [];

  constructor(private options: { output: string; suite?: string }) {}

  onTestEnd(test: TestCase, result: TestResult) {
    const annotations = [...test.annotations, ...(result.annotations ?? [])];
    const ids = [...new Set(annotations.filter(a => a.type === 'qa').map(a => a.description!))];
    const finding = annotations.find(a => a.type === 'finding')?.description;
    const project = test.parent.project()?.name;
    const title = `[${project}] ${test.titlePath().slice(3).join(' › ')}`;

    let status: Entry['status'];
    let error = result.error?.message?.split('\n').find(line => line.trim())?.replace(/\u001b\[[0-9;]*m/g, '') ?? null;
    if (result.status === 'skipped') status = 'skipped';
    else if (test.expectedStatus === 'failed' && result.status !== 'passed') {
      status = 'known';
      error = `Known finding ${finding}`;
    }
    else if (test.expectedStatus === 'failed') {
      status = 'failed';
      error = `Known finding ${finding} passes now: remove the knownFinding() marker`;
    }
    else status = result.status === 'passed' ? 'passed' : 'failed';

    // Retries: keep the last result only
    this.tests = this.tests.filter(entry => entry.title !== title);
    this.tests.push({ title, ids, status, error });
  }

  onEnd(_result: FullResult) {
    fs.mkdirSync(path.dirname(this.options.output), { recursive: true });
    fs.writeFileSync(this.options.output, JSON.stringify({
      suite: this.options.suite ?? 'e2e',
      run: new Date().toISOString(),
      tests: this.tests,
    }, null, 2));
  }

  printsToStdio() {
    return false;
  }
}
