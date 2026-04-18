import { test, expect } from '@playwright/test';

test.describe('Backend Search Filters (#470)', () => {

  test.describe('Brands', () => {

    test('search field and button are present', async ({ page }) => {
      await page.goto('/admin/catalog/brands');
      await expect(page.getByPlaceholder('Search phrase or keyword')).toBeVisible();
      await expect(page.getByRole('button', { name: 'Search' })).toBeVisible();
    });

    test('search submits without errors', async ({ page }) => {
      await page.goto('/admin/catalog/brands?query=test');
      await expect(page.locator('.data-table')).toBeVisible();
      // No PHP errors on the page
      await expect(page.locator('body')).not.toContainText('Fatal error');
      await expect(page.locator('body')).not.toContainText('Warning:');
    });
  });

  test.describe('Suppliers', () => {

    test('search field and button are present', async ({ page }) => {
      await page.goto('/admin/catalog/suppliers');
      await expect(page.getByPlaceholder('Search phrase or keyword')).toBeVisible();
      await expect(page.getByRole('button', { name: 'Search' })).toBeVisible();
    });

    test('search submits without errors', async ({ page }) => {
      await page.goto('/admin/catalog/suppliers?query=test');
      await expect(page.locator('.data-table')).toBeVisible();
      await expect(page.locator('body')).not.toContainText('Fatal error');
      await expect(page.locator('body')).not.toContainText('Warning:');
    });
  });

  test.describe('Countries', () => {

    test('search field is present', async ({ page }) => {
      await page.goto('/admin/localization/countries/countries');
      await expect(page.getByPlaceholder('Search phrase or keyword')).toBeVisible();
    });

    test('client-side filter reduces visible rows', async ({ page }) => {
      await page.goto('/admin/localization/countries/countries');

      const rows = page.locator('.data-table tbody tr');
      const totalRows = await rows.count();
      expect(totalRows).toBeGreaterThan(10);

      // Type a filter term
      await page.getByPlaceholder('Search phrase or keyword').fill('Germany');
      await expect(rows.filter({ hasText: 'Germany' })).toBeVisible();

      const visibleRows = await rows.filter({ has: page.locator(':visible') }).count();
      expect(visibleRows).toBeLessThan(totalRows);
    });

    test('footer counter updates on filter', async ({ page }) => {
      await page.goto('/admin/localization/countries/countries');

      const footer = page.locator('.data-table tfoot td');
      const initialText = await footer.textContent();

      await page.getByPlaceholder('Search phrase or keyword').fill('Germany');

      // Counter should show a smaller number
      await expect(footer).not.toHaveText(initialText);
      await expect(footer).toContainText('Countries:');
    });

    test('clearing filter restores all rows', async ({ page }) => {
      await page.goto('/admin/localization/countries/countries');

      const rows = page.locator('.data-table tbody tr');
      const totalBefore = await rows.count();

      // Filter then clear
      await page.getByPlaceholder('Search phrase or keyword').fill('Germany');
      await page.getByPlaceholder('Search phrase or keyword').fill('');

      const totalAfter = await rows.count();
      expect(totalAfter).toBe(totalBefore);
    });
  });
});
