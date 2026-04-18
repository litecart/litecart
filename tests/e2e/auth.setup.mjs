import { test as setup, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const authFile = path.join(__dirname, '../../playwright/.auth/admin.json');

const ADMIN_USER = process.env.E2E_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.E2E_ADMIN_PASS || 'admin123456';

setup('authenticate as admin', async ({ page }) => {
  await page.goto('/admin/');

  // Fill login form
  await page.getByPlaceholder('Username or Email Address').fill(ADMIN_USER);
  await page.getByPlaceholder('Password').fill(ADMIN_PASS);
  await page.getByRole('button', { name: 'Login' }).click();

  // Wait for dashboard to load
  await expect(page).toHaveTitle(/Dashboard/);

  // Save signed-in state
  await page.context().storageState({ path: authFile });
});
