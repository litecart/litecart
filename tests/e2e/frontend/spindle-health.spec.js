import { test, expect } from '@playwright/test';
import { frontendConfig } from '../frontend.config.js';

// Crawls every reachable page in the public storefront and aggregates:
//
//   - uncaught JavaScript exceptions (pageerror)            → FAIL
//   - JS resource load failures (syntax/parse errors)       → FAIL
//   - console errors                                        → FAIL (unless --ignore-console)
//   - console warnings                                      → FAIL (unless --ignore-warnings)
//   - HTTP responses with status >= 400                     → FAIL
//
// Strategy: breadth-first walk starting at the storefront root
// (or E2E_CRAWL_SEED), following same-origin links whose pathname
// begins with the storefront path. External links and resource files
// (CSS/JS/images/fonts) are not followed as pages but are still
// tracked for HTTP failure checks.
//
// The storefront is public — no authentication is performed.
//
// Configure with E2E_FRONTEND_URL.
// See tests/e2e/frontend.config.js.
//
// Toggles:
//   E2E_IGNORE_CONSOLE=1     don't fail on console.error
//   E2E_IGNORE_WARNINGS=1    don't fail on console.warn
//   E2E_IGNORE_JS_LOAD=1     don't fail on failed JS resource loads
//   E2E_NOISE_REGEX="..."    extra substrings (pipe-separated) to suppress
//                            in console/pageerror reports

const IGNORE_CONSOLE  = !!process.env.E2E_IGNORE_CONSOLE;
const IGNORE_WARNINGS = !!process.env.E2E_IGNORE_WARNINGS;
const IGNORE_JS_LOAD  = !!process.env.E2E_IGNORE_JS_LOAD;
const NOISE_EXTRA = (process.env.E2E_NOISE_REGEX || '')
  .split('|').map(s => s.trim()).filter(Boolean);

// Substrings known to be noisy on LiteCart storefront pages — never fail on these.
const NOISE_BUILTIN = [
  'favicon.ico',                        // browser auto-requests favicon
  'DevTools',                           // devtools-related hints
  'Lit is in dev mode',                 // Lit dev-mode warning
  '[Vue warn]',                         // Vue dev warnings
  '[HMR]',                              // hot-module reload chatter
  'Failed to load resource: net::ERR_', // generic network drop without status
];

const isNoise = (text) => {
  if (!text) return false;
  for (const n of NOISE_BUILTIN) if (text.includes(n)) return true;
  for (const n of NOISE_EXTRA)   if (text.includes(n)) return true;
  return false;
};

test('spindle all storefront pages with no JS errors and no HTTP 4xx/5xx', async ({ page, baseURL }) => {
  test.setTimeout(180_000); // storefront can have many more linked pages

  const origin = new URL(baseURL).origin;
  const storefrontPath = new URL(baseURL).pathname.replace(/\/$/, '');
  const seed = frontendConfig.seedPath || storefrontPath + '/';

  // Normalize so '/catalog' and '/catalog/' collapse to the same visited entry.
  const normalizePath = (p) => {
    const noSlash = p.replace(/\/+$/, '');
    return noSlash === storefrontPath ? noSlash + '/' : noSlash;
  };

  /** @type {Map<string, {status: number, url: string}>} */
  const failedResponses = new Map();
  /** @type {Array<{page: string, type: string, text: string}>} */
  const jsIssues = [];
  /** @type {Array<{page: string, type: string, text: string, count: number}>} */
  const consoleIssues = [];
  /** @type {Array<{page: string, type: string, text: string, url?: string}>} */
  const jsLoadIssues = [];
  const visited = new Set();
  const queue = [seed];

  // De-dupe identical console messages that fire repeatedly on the same page.
  const consoleKey = (page, type, text) => `${page}|${type}|${text}`;
  const consoleSeen = new Set();

  const recordResponse = (response) => {
    const status = response.status();
    if (status < 400) return;
    const key = response.url() + '|' + status;
    if (!failedResponses.has(key)) {
      failedResponses.set(key, { status, url: response.url() });
    }
  };

  page.on('response', recordResponse);

  // 1) Uncaught JS exceptions thrown during page execution.
  page.on('pageerror', (err) => {
    const text = err.stack || err.message;
    if (isNoise(text)) return;
    jsIssues.push({ page: page.url(), type: 'pageerror', text });
  });

  // 2) console.error / console.warn.
  page.on('console', (msg) => {
    const type = msg.type();
    if (type !== 'error' && type !== 'warning') return;
    const text = msg.text();
    if (isNoise(text)) return;
    const key = consoleKey(page.url(), type, text);
    if (consoleSeen.has(key)) return;
    consoleSeen.add(key);
    consoleIssues.push({
      page: page.url(),
      type,
      text,
      count: 1,
    });
  });

  // 3) JS resource load failures → typically a syntax error in a .js file.
  page.on('requestfailed', (req) => {
    if (IGNORE_JS_LOAD) return;
    const url = req.url();
    const resourceType = req.resourceType();
    if (resourceType !== 'script') return;
    const failure = req.failure();
    const text = failure?.errorText || 'request failed without status';
    if (isNoise(text) || isNoise(url)) return;
    jsLoadIssues.push({
      page: page.url(),
      type: 'js-load-failed',
      text,
      url,
    });
  });

  const collectLinks = async (pageUrl) => {
    const links = await page.$$eval('a[href]', (anchors) =>
      anchors.map((a) => a.getAttribute('href')).filter(Boolean),
    );
    const next = [];
    for (const href of links) {
      try {
        const u = new URL(href, pageUrl);
        if (u.origin !== origin) continue;             // skip external
        // Stay in the storefront. Allow the storefront root and anything
        // underneath it. Skip the admin panel — it has its own spindle.
        if (u.pathname.startsWith('/admin') || u.pathname.startsWith('/backend')) continue;
        if (storefrontPath !== '' && storefrontPath !== '/') {
          if (!u.pathname.startsWith(storefrontPath + '/') && u.pathname !== storefrontPath) continue;
        } else {
          // Root install — accept anything that doesn't start with /admin
          if (u.pathname.startsWith('/admin') || u.pathname.startsWith('/backend')) continue;
        }
        u.hash = '';
        const normalized = normalizePath(u.pathname) + (u.search || '');
        if (!visited.has(normalized) && !queue.includes(normalized)) {
          next.push(normalized);
        }
      } catch { /* malformed href */ }
    }
    return next;
  };

  let pagesVisited = 0;
  while (queue.length > 0 && pagesVisited < frontendConfig.maxPages) {
    const path = queue.shift();
    if (visited.has(path)) continue;
    visited.add(path);

    const url = new URL(path, baseURL).toString();

    const response = await page.goto(url, { waitUntil: 'networkidle' }).catch(() => null);
    if (!response) {
      jsIssues.push({ page: url, type: 'navigation', text: 'navigation failed (network error or timeout)' });
      continue;
    }

    pagesVisited++;

    // Wait briefly for deferred JS (carousels, lazy images, theme widgets…).
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(150);

    const discovered = await collectLinks(page.url());
    for (const d of discovered) queue.push(d);
  }

  // ── Build the failure report ─────────────────────────────────────────────
  const lines = [];
  if (failedResponses.size > 0) {
    lines.push(`\n  HTTP ${failedResponses.size} failed response(s):`);
    for (const f of failedResponses.values()) {
      lines.push(`    [${f.status}] ${f.url}`);
    }
  }
  if (jsIssues.length > 0) {
    lines.push(`\n  Uncaught JS exceptions (${jsIssues.length}):`);
    for (const i of jsIssues) {
      lines.push(`    [${i.type}] @ ${i.page}\n        ${i.text}`);
    }
  }
  if (jsLoadIssues.length > 0) {
    lines.push(`\n  JS resource load failures (${jsLoadIssues.length}):`);
    for (const i of jsLoadIssues) {
      lines.push(`    [${i.type}] @ ${i.page}\n        ${i.url}\n        ${i.text}`);
    }
  }
  if (consoleIssues.length > 0) {
    const errors   = consoleIssues.filter(i => i.type === 'error');
    const warnings = consoleIssues.filter(i => i.type === 'warning');
    if (errors.length > 0 && !IGNORE_CONSOLE) {
      lines.push(`\n  Console errors (${errors.length}):`);
      for (const i of errors.slice(0, 50)) {
        lines.push(`    [${i.type}] @ ${i.page}\n        ${i.text}`);
      }
      if (errors.length > 50) {
        lines.push(`    …and ${errors.length - 50} more`);
      }
    }
    if (warnings.length > 0 && !IGNORE_WARNINGS) {
      lines.push(`\n  Console warnings (${warnings.length}):`);
      for (const i of warnings.slice(0, 50)) {
        lines.push(`    [${i.type}] @ ${i.page}\n        ${i.text}`);
      }
      if (warnings.length > 50) {
        lines.push(`    …and ${warnings.length - 50} more`);
      }
    }
  }

  lines.unshift(`\n  Crawled ${pagesVisited} page(s) starting from ${seed}`);

  // Compute the actual failure count, honouring opt-outs.
  const failCount =
      failedResponses.size
    + jsIssues.length
    + (IGNORE_JS_LOAD ? 0 : jsLoadIssues.length)
    + (IGNORE_CONSOLE ? 0 : consoleIssues.filter(i => i.type === 'error').length)
    + (IGNORE_WARNINGS ? 0 : consoleIssues.filter(i => i.type === 'warning').length);

  const hint = (!IGNORE_CONSOLE || !IGNORE_WARNINGS || !IGNORE_JS_LOAD)
    ? ''
    : '\n  (note: E2E_IGNORE_CONSOLE / E2E_IGNORE_WARNINGS / E2E_IGNORE_JS_LOAD are set)';

  expect(
    failCount,
    `Storefront health check failed.${hint}${lines.join('\n')}`,
  ).toBe(0);
});
