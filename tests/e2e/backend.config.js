// Backend test configuration.
//
// LiteCart v3 backend URLs follow the path-style pattern:
//   /admin/{app}/{doc}      e.g. /admin/catalog/brands
//   /admin/{page}           e.g. /admin/login
//
// Override via environment variables when running against a remote/live
// backend, e.g.:
//
//   E2E_BACKEND_URL=http://litecart.local/admin/ \
//   E2E_ADMIN_USER=admin \
//   E2E_ADMIN_PASS=secret \
//   npx playwright test --project=backend
//
// The defaults match the local dev environment.

const DEFAULT_BACKEND_URL = 'http://litecart-major.local/admin/';
const DEFAULT_ADMIN_USER = 'admin';
const DEFAULT_ADMIN_PASS = '1234';

function normalizeBaseUrl(url) {
  return url.endsWith('/') ? url : url + '/';
}

export const backendConfig = {
  baseURL: normalizeBaseUrl(process.env.E2E_BACKEND_URL || DEFAULT_BACKEND_URL),
  adminUser: process.env.E2E_ADMIN_USER || DEFAULT_ADMIN_USER,
  adminPass: process.env.E2E_ADMIN_PASS || DEFAULT_ADMIN_PASS,
  // Soft limit on crawled pages so a runaway link loop cannot hang CI.
  maxPages: Number(process.env.E2E_CRAWL_MAX_PAGES || 500),
  // Path-style seed (v3). Set e.g. '/admin/catalog/brands' to restrict the crawl.
  seedPath: process.env.E2E_CRAWL_SEED || '',
};

export default backendConfig;
