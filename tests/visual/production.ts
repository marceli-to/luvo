import { createHash } from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import type { BrowserContext, Route } from '@playwright/test';
import { root } from '../e2e/support/env';

/** Production, read only. */
export const production = 'https://luksundvogt.ch';
const productionHosts = ['luksundvogt.ch', 'www.luksundvogt.ch'];
const localHosts = ['127.0.0.1:8010'];

const cacheDir = path.join(root, 'tests/.cache/prod');
const DAY = 24 * 60 * 60 * 1000;

/** At most two production requests in flight (one worker runs the visual tests). */
const MAX_IN_FLIGHT = 2;
let inFlight = 0;
const waiting: (() => void)[] = [];
async function acquire() {
  if (inFlight < MAX_IN_FLIGHT) { inFlight++; return; }
  await new Promise<void>(resolve => waiting.push(resolve));
  inFlight++;
}
function release() {
  inFlight--;
  waiting.shift()?.();
}

export let productionRequests = 0;

type Cached = { status: number; headers: Record<string, string>; body: Buffer };
const memory = new Map<string, Cached>();

function diskPath(url: string) {
  return path.join(cacheDir, createHash('sha1').update(url).digest('hex'));
}

/**
 * GET from production through the limiter, cached: images on disk for a day
 * (2400 px JPEGs; no need to download them on every run), everything else
 * in memory for this run. Anything but GET is refused.
 */
export async function productionGet(url: string): Promise<Cached> {
  if (memory.has(url)) return memory.get(url)!;
  const file = diskPath(url);
  if (fs.existsSync(file) && Date.now() - fs.statSync(file).mtimeMs < DAY) {
    const { status, headers } = JSON.parse(fs.readFileSync(`${file}.json`, 'utf8'));
    return { status, headers, body: fs.readFileSync(file) };
  }
  await acquire();
  try {
    productionRequests++;
    const response = await fetch(url, { method: 'GET', redirect: 'follow', headers: { 'User-Agent': 'luvo-qa-visual (read-only)' } });
    const cached = { status: response.status, headers: Object.fromEntries(response.headers), body: Buffer.from(await response.arrayBuffer()) };
    delete cached.headers['content-encoding'];
    delete cached.headers['content-length'];
    if (new URL(url).pathname.startsWith('/img/') && cached.status === 200) {
      fs.mkdirSync(cacheDir, { recursive: true });
      fs.writeFileSync(file, cached.body);
      fs.writeFileSync(`${file}.json`, JSON.stringify({ status: cached.status, headers: cached.headers }));
    }
    else {
      memory.set(url, cached);
    }
    return cached;
  }
  finally {
    release();
  }
}

/**
 * Every request of a browser context: production only via productionGet
 * (non-GET aborted), the local server as is, third parties (analytics)
 * blocked so screenshots are stable.
 */
export async function guard(context: BrowserContext) {
  await context.route('**/*', async (route: Route) => {
    const request = route.request();
    const url = new URL(request.url());
    if (['data:', 'blob:'].includes(url.protocol) || localHosts.includes(url.host)) {
      return route.continue();
    }
    if (!productionHosts.includes(url.host) || request.method() !== 'GET') {
      return route.abort('blockedbyclient');
    }
    const response = await productionGet(request.url());
    return route.fulfill({ status: response.status, headers: response.headers, body: response.body });
  });
}
