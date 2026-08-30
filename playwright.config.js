import { defineConfig, devices } from '@playwright/test';
import { backendConfig } from './tests/e2e/backend.config.js';
import { frontendConfig } from './tests/e2e/frontend.config.js';

const PORT = process.env.E2E_PORT || 8080;
const BASE_URL = `http://localhost:${PORT}`;

// When targeting a remote backend (e.g. litecart-major.local) we must NOT
// start the PHP built-in server — assume the remote URL is already live.
const usingRemoteBackend = !!process.env.E2E_BACKEND_URL;

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI ? [['html', { open: 'never' }], ['github']] : [['html', { open: 'on-failure' }]],
  timeout: 10_000,

  use: {
    baseURL: BASE_URL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'auth',
      testMatch: /auth\.setup\.js/,
      use: {
        baseURL: backendConfig.baseURL,
      },
    },
    {
      name: 'backend',
      testDir: './tests/e2e/backend',
      use: {
        ...devices['Desktop Chrome'],
        baseURL: backendConfig.baseURL,
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['auth'],
    },
    {
      name: 'frontend',
      testDir: './tests/e2e/frontend',
      use: {
        ...devices['Desktop Chrome'],
        baseURL: frontendConfig.baseURL,
      },
    },
  ],

  webServer: usingRemoteBackend
    ? undefined
    : {
        command: `php -S localhost:${PORT} -t src`,
        url: BASE_URL,
        timeout: 10_000,
        reuseExistingServer: !process.env.CI,
        stdout: 'ignore',
        stderr: 'pipe',
      },
});
