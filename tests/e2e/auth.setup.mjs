import { test as setup, expect } from '@playwright/test';

const ADMIN_USER = process.env.E2E_ADMIN_USER || 'litecart';
const ADMIN_PASS = process.env.E2E_ADMIN_PASS || 'litecart';
const authFile = 'playwright/.auth-state.json';

setup('authenticate as admin', async ({ page }) => {
  await page.goto('/admin/login');

  // Fill login form
  await page.locator('input[name="username"]').fill(ADMIN_USER);
  await page.locator('input[name="password"]').fill(ADMIN_PASS);
  await page.locator('form[name="login_form"]').evaluate((form) => form.submit());

  // Wait for dashboard to load
  await expect(page).toHaveTitle(/Dashboard/);

  // Save signed-in state
  await page.context().storageState({ path: authFile });
});
