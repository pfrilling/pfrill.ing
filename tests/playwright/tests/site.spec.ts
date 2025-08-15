import { test, expect, Page } from '@playwright/test';

// Helper: find a pager element in Drupal-like markup.
async function findPager(page: Page) {
  // Common Drupal pager structures
  const candidates = [
    'nav.pager',
    'nav[role="navigation"].pager',
    'ul.pager__items',
    'nav[aria-label="Pagination"]',
  ];
  for (const sel of candidates) {
    const el = page.locator(sel);
    if (await el.first().count()) {
      return el.first();
    }
  }
  return null;
}

// Helper: get the first article link
async function getFirstArticleLink(page: Page) {
  const selectors = [
    'article h2 a',
    'article .node__title a',
    'article a',
    'main article a',
  ];
  for (const sel of selectors) {
    const links = page.locator(sel);
    if (await links.first().count()) {
      return links.first();
    }
  }
  return null;
}

// Helper: ensure an element A appears before element B in DOM order.
async function expectDomOrderBefore(page: Page, aSelector: string, bSelector: string) {
  const [aHandle, bHandle] = await Promise.all([
    page.locator(aSelector).first().elementHandle(),
    page.locator(bSelector).first().elementHandle(),
  ]);
  expect(aHandle, `Element not found for selector: ${aSelector}`).not.toBeNull();
  expect(bHandle, `Element not found for selector: ${bSelector}`).not.toBeNull();
  const isBefore = await page.evaluate(({ a, b }) => {
    if (!a || !b) return false;
    // Node.DOCUMENT_POSITION_FOLLOWING = 4
    return !!(a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING);
  }, { a: aHandle, b: bHandle });
  expect(isBefore, `${aSelector} should appear before ${bSelector} in the DOM`).toBeTruthy();
}

// 1) Home page checks
// - Loads
// - Has a pager at the bottom
// - At least one article above the pager
// - Click on one of the articles and confirm the page loads

test.describe('Home page and article navigation', () => {
  test('home page has articles above pager and article pages load', async ({ page, baseURL }) => {
    const home = baseURL || '/';
    const response = await page.goto(home, { waitUntil: 'domcontentloaded' });
    expect(response, 'Home page should respond').toBeTruthy();
    expect(response!.ok(), `Home page should return OK: ${response && response.status()}`).toBeTruthy();

    // Find at least one article
    const articles = page.locator('article');
    expect(await articles.count(), 'Expected at least one <article> on the page').toBeGreaterThan(0);

    // Find pager and assert ordering
    const pager = await findPager(page);
    expect(pager, 'Expected a pager element on the home page').not.toBeNull();

    // Ensure first article appears before pager in DOM order
    {
      const articleHandle = await page.locator('article').first().elementHandle();
      const pagerHandle = await pager!.elementHandle();
      expect(articleHandle).not.toBeNull();
      expect(pagerHandle).not.toBeNull();
      const isBefore = await page.evaluate(({ a, b }) => {
        if (!a || !b) return false;
        return !!(a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING);
      }, { a: articleHandle, b: pagerHandle });
      expect(isBefore, 'Expected an <article> to appear before the pager').toBeTruthy();
    }

    // Click first article link and ensure article page loads
    const firstArticleLink = await getFirstArticleLink(page);
    expect(firstArticleLink, 'Expected a clickable link inside an article').not.toBeNull();

    const [nav] = await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      firstArticleLink!.click(),
    ]);
    expect(nav, 'Article page navigation should complete').toBeTruthy();
    await expect(page, 'Article page should have a heading').toHaveTitle(/.+/);
    await expect(page.locator('article')).toHaveCount(1);
  });
});

// 2) Main navigation has a "search" link
// 3) Following the search link shows a search form
// 4) Search for "Drupal" and confirm results are found

test.describe('Search feature', () => {
  test('navigation has search link and search works for "Drupal"', async ({ page, baseURL }) => {
    const home = baseURL || '/';
    const response = await page.goto(home, { waitUntil: 'domcontentloaded' });
    expect(response, 'Home page should respond').toBeTruthy();
    expect(response!.ok(), `Home page should return OK: ${response && response.status()}`).toBeTruthy();

    // Find at least one article
    const articles = page.locator('article');
    expect(await articles.count(), 'Expected at least one <article> on the page').toBeGreaterThan(0);

    // Try to locate a nav that contains a link with name containing "search"
    const searchLink = page.getByRole('link', { name: /search/i });
    expect(await searchLink.count(), 'Expected at least one "search" link in the main navigation').toBeGreaterThan(0);

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      searchLink.click(),
    ]);

    // On Drupal core Search page, expect an input name="keys"
    const searchInput = page.locator('input[name="keys"], input[type="search"]');
    await expect(searchInput, 'Expected a search input on the search page').toHaveCount(1);

    await searchInput.fill('Drupal');
    // Submit the form; try pressing Enter or clicking submit button
    const submitButton = page.locator('#search-form input#edit-submit').first();

    if (await submitButton.count()) {
      // In CI, Playwright's click waits for navigation and may hit the 15s actionTimeout.
      // Avoid double-waiting by disabling auto-wait on the click and explicitly waiting
      // for the load state instead.
      await Promise.all([
        page.waitForLoadState('domcontentloaded'),
        submitButton.click(),
      ]);
    } else {
      await Promise.all([
        page.waitForLoadState('domcontentloaded'),
        page.keyboard.press('Enter'),
      ]);
    }

    // Confirm results render or a "no results" message is shown (Drupal may need indexing in fresh envs)
    const results = page.locator('ol.search-results li.search-result, .search-results li');
    const resultsCount = await results.count();

    // Common Drupal messages when no results are found
    const noResultsMessage = page.locator('text=/no results|did not match any results/i');
    const noResultsCount = await noResultsMessage.count();

    expect(
      resultsCount,
      'Expected search results or a "no results" message for "Drupal"'
    ).toBeGreaterThan(0);
  });
});

// 5) Browse to /user/login and confirm a 200

test('Login page responds with 200', async ({ page }) => {
  const resp = await page.goto('/user/login', { waitUntil: 'domcontentloaded' });
  expect(resp, 'Expected a response for /user/login').toBeTruthy();
  expect(resp!.status(), 'Expected /user/login to return HTTP 200').toBe(200);
});
