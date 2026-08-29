import { test as setup, expect } from '@playwright/test';
import { backendConfig } from './backend.config.js';

const authFile = 'playwright/.auth/admin.json';

setup('authenticate as admin', async ({ page }) => {
  await page.goto('login');

  // Fill login form
  await page.locator('input[name="username"]').fill(backendConfig.adminUser);
  await page.locator('input[name="password"]').fill(backendConfig.adminPass);
  await page.locator('form[name="login_form"]').evaluate((form) => form.submit());

  // Wait for dashboard to load
  await expect(page).toHaveTitle(/Dashboard/);

  // Save signed-in state
  await page.context().storageState({ path: authFile });
});
