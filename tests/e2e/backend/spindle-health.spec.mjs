import { test, expect } from '@playwright/test';
import { backendConfig } from '../backend.config.js';

// Crawls every reachable page in the authenticated backend and aggregates:
//
//   - uncaught JavaScript exceptions (pageerror)            → FAIL
//   - JS resource load failures (syntax/parse errors)       → FAIL
//   - console errors                                        → FAIL (unless --ignore-console)
//   - console warnings                                      → FAIL (unless --ignore-warnings)
//   - HTTP responses with status >= 400                     → FAIL
//
// Strategy: breadth-first walk starting at /admin/ (or E2E_CRAWL_SEED),
// following same-origin links whose pathname starts with /admin/. External
// links and resource files (CSS/JS/images/fonts) are not followed as pages
// but are still tracked for HTTP failure checks.
//
// LiteCart v3 URL pattern is path-style (/admin/{app}/{doc}), so seed and
// discovered links look like '/admin/catalog/brands'.
//
// Configure with E2E_BACKEND_URL / E2E_ADMIN_USER / E2E_ADMIN_PASS.
// See tests/e2e/backend.config.js.
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

// Substrings known to be noisy on LiteCart admin pages — never fail on these.
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

test('spindle all backend pages with no JS errors and no HTTP 4xx/5xx', async ({ page, baseURL }) => {
  test.setTimeout(120_000);

  const origin = new URL(baseURL).origin;
  const adminPath = new URL(baseURL).pathname.replace(/\/$/, '');
  const seed = backendConfig.seedPath || adminPath + '/';

  // Normalize so '/admin/catalog/brands' and '/admin/catalog/brands/' collapse
  // to the same visited entry.
  const normalizePath = (p) => {
    const noSlash = p.replace(/\/+$/, '');
    return noSlash === adminPath ? noSlash + '/' : noSlash;
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
  //    requestfailed fires when a navigation/XHR fails before any status is known;
  //    for a 4xx/5xx response the 'response' handler above already covers it,
  //    but we still flag failed .js loads with no status as parse/syntax errors.
  page.on('requestfailed', (req) => {
    if (IGNORE_JS_LOAD) return;
    const url = req.url();
    // Only flag script resources; ignore aborted XHRs, images, fonts, etc.
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
        if (u.origin !== origin) continue;            // skip external
        if (!u.pathname.startsWith(adminPath + '/') && u.pathname !== adminPath) continue; // stay in /admin/
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
  while (queue.length > 0 && pagesVisited < backendConfig.maxPages) {
    const path = queue.shift();
    if (visited.has(path)) continue;
    visited.add(path);

    const url = new URL(path, baseURL).toString();

    const response = await page.goto(url, { waitUntil: 'networkidle' }).catch(() => null);
    if (!response) {
      jsIssues.push({ page: url, type: 'navigation', text: 'navigation failed (network error or timeout)' });
      continue;
    }

    // If the auth session expired we were redirected to the login form.
    const finalPath = new URL(response.url()).pathname;
    if (finalPath === `${adminPath}/login` && !url.endsWith('/login')) {
      jsIssues.push({ page: url, type: 'auth', text: 'redirected to login — session likely expired mid-crawl' });
      break;
    }

    pagesVisited++;

    // Wait briefly for deferred JS (admin widgets, datatables, charts…).
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
    `Backend health check failed.${hint}${lines.join('\n')}`,
  ).toBe(0);
});
