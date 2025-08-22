import { test, expect, Page } from '@playwright/test';
import { preparePageForVisual, visualTestConfig } from '../visual-config';

// Helper: get the first article link (reused from site.spec.ts pattern)
async function getFirstArticleLink(page: Page) {
  const selectors = [
    'article h2 a',
    'article .node__title a',
    'article a',
    'main article a',
    'li.search-results__item a'
  ];
  for (const sel of selectors) {
    const links = page.locator(sel);
    if (await links.first().count()) {
      return links.first();
    }
  }
  return null;
}

test.describe('Visual Regression Testing', () => {
  // Configure timeout for visual tests
  test.setTimeout(visualTestConfig.timeout.screenshot);

  test.describe('Homepage Visual Tests', () => {
    test('homepage visual comparison', async ({ page, baseURL }) => {
      const home = baseURL || '/';

      // Navigate to homepage
      const response = await page.goto(home, { waitUntil: 'domcontentloaded' });
      expect(response, 'Home page should respond').toBeTruthy();
      expect(response!.ok(), `Home page should return OK: ${response && response.status()}`).toBeTruthy();

      // Prepare page for visual testing
      await preparePageForVisual(page);

      // Take screenshot and compare with baseline
      await expect(page).toHaveScreenshot('homepage-full.png');
    });
  });

  test.describe('Article Page Visual Tests', () => {
    test('article detail page visual comparison', async ({ page, baseURL }) => {
      const home = baseURL || '/';

      // Navigate to homepage first
      await page.goto(home, { waitUntil: 'domcontentloaded' });

      // Find and click first article link
      const firstArticleLink = await getFirstArticleLink(page);
      expect(firstArticleLink, 'Expected a clickable link inside an article').not.toBeNull();

      // Navigate to article page
      const [nav] = await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        firstArticleLink!.click(),
      ]);
      expect(nav, 'Article page navigation should complete').toBeTruthy();

      // Verify we're on an article page
      await expect(page.locator('article')).toHaveCount(1);

      // Prepare page for visual testing
      await preparePageForVisual(page);

      // Take screenshot and compare with baseline
      await expect(page).toHaveScreenshot('article-detail-full.png');
    });
  });

  test.describe('Search Page Visual Tests', () => {
    test('search results page visual comparison', async ({ page, baseURL }) => {
      const home = baseURL || '/';

      // Navigate to search results page
      const response = await page.goto(`${home}/search/node?keys=drupal`, { waitUntil: 'domcontentloaded' });
      expect(response, 'Search page should respond').toBeTruthy();
      expect(response!.ok(), `Search page should return OK: ${response && response.status()}`).toBeTruthy();

      // Verify we have search results
      const searchResults = page.locator('li.search-results__item');
      expect(await searchResults.count(), 'Expected at least one search result').toBeGreaterThan(0);

      // Prepare page for visual testing
      await preparePageForVisual(page);

      // Take screenshot and compare with baseline
      await expect(page).toHaveScreenshot('search-results-full.png');
    });
  });

  test.describe('Login Page Visual Tests', () => {
    test('user login page visual comparison', async ({ page }) => {
      // Navigate to login page
      const resp = await page.goto('/user/login', { waitUntil: 'domcontentloaded' });
      expect(resp, 'Expected a response for /user/login').toBeTruthy();
      expect(resp!.status(), 'Expected /user/login to return HTTP 200').toBe(200);

      // Prepare page for visual testing
      await preparePageForVisual(page);

      // Take screenshot and compare with baseline
      await expect(page).toHaveScreenshot('login-page-full.png');
    });
  });

  test.describe('Responsive Design Visual Tests', () => {
    test('homepage responsive behavior', async ({ page, baseURL }) => {
      const home = baseURL || '/';

      // Navigate to homepage
      await page.goto(home, { waitUntil: 'domcontentloaded' });

      // Prepare page for visual testing
      await preparePageForVisual(page);

      // Take screenshot (viewport-specific baseline will be used automatically)
      await expect(page).toHaveScreenshot('homepage-responsive.png');
    });

    test('article page responsive behavior', async ({ page, baseURL }) => {
      const home = baseURL || '/';

      // Navigate to homepage and find first article
      await page.goto(home, { waitUntil: 'domcontentloaded' });
      const firstArticleLink = await getFirstArticleLink(page);
      expect(firstArticleLink, 'Expected a clickable link inside an article').not.toBeNull();

      // Navigate to article page
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        firstArticleLink!.click(),
      ]);

      // Prepare page for visual testing
      await preparePageForVisual(page);

      // Take screenshot (viewport-specific baseline will be used automatically)
      await expect(page).toHaveScreenshot('article-responsive.png');
    });
  });
});
