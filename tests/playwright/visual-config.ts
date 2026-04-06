import { Page } from '@playwright/test';

/**
 * Apply consistent styling to hide dynamic content during visual regression testing.
 * This prevents false positives from timestamps, user-specific content, and other
 * dynamic elements that change between test runs.
 */
export async function applyVisualTestStyling(page: Page): Promise<void> {
  await page.addStyleTag({
    content: `
      /* Hide dynamic Drupal elements that change between test runs */
      .submitted,
      [data-drupal-date-format],
      .contextual-links,
      #toolbar,
      .toolbar,
      .admin-toolbar,
      .admin-menu,
      .drupal-announcement,
      .messages--status,
      .messages--warning,
      .messages--error,
      .breadcrumb,
      .tabs,
      .action-links,
      .local-actions,
      .block-local-tasks-block,
      .block-page-title-block .contextual,
      .views-element-container .contextual,
      .contextual-region .contextual,
      .field--name-created,
      .field--name-changed,
      .node__meta,
      .comment__meta,
      .user-picture,
      .profile-picture,
      [data-contextual-id],
      .js-form-managed-file,
      input[type="hidden"],
      .visually-hidden,
      .sr-only {
        visibility: hidden !important;
        opacity: 0 !important;
      }

      /* Hide CSRF tokens and form build info */
      input[name*="form_build_id"],
      input[name*="form_token"],
      input[name*="form_id"] {
        display: none !important;
      }

      /* Ensure consistent spacing where hidden elements were */
      .submitted,
      .field--name-created,
      .field--name-changed,
      .node__meta {
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      /* Disable animations and transitions for consistent screenshots */
      *,
      *::before,
      *::after {
        animation-duration: 0s !important;
        animation-delay: 0s !important;
        transition-duration: 0s !important;
        transition-delay: 0s !important;
      }

      /* Hide any elements with changing content */
      [data-drupal-link-system-path] .contextual,
      .js-contextual-region .contextual,
      .contextual-toolbar-tab,
      .trigger {
        display: none !important;
      }

      /* Hide code blocks with dynamic syntax highlighting */
      pre,
      code,
      .highlight,
      .syntax-highlight,
      .language-php,
      .language-js,
      .language-css,
      .language-html,
      .language-plaintext,
      .hljs,
      .prism,
      .codehilite {
        font-family: monospace !important;
        color: #333 !important;
        background-color: #f5f5f5 !important;
      }

      /* Remove syntax highlighting classes */
      .highlight *,
      .hljs *,
      .prism *,
      .codehilite * {
        color: inherit !important;
        background-color: transparent !important;
        font-weight: normal !important;
        font-style: normal !important;
      }
    `
  });
}

/**
 * Prepare a page for visual regression testing by applying styling
 * and waiting for the page to stabilize.
 */
export async function preparePageForVisual(page: Page): Promise<void> {
  // Wait for network to be idle to ensure all resources are loaded
  await page.waitForLoadState('networkidle');

  // Wait for any syntax highlighting or dynamic content to load
  await page.waitForTimeout(1000);

  // Apply visual test styling to hide dynamic content
  await applyVisualTestStyling(page);

  // Wait a bit more for styling to be applied
  await page.waitForTimeout(500);
}

/**
 * Visual regression testing configuration options.
 */
export const visualTestConfig = {
  // Screenshot options
  screenshot: {
    fullPage: true,
    animations: 'disabled' as const,
  },

  // Comparison options
  comparison: {
    maxDiffPixels: 100,
    threshold: 0.2,
  },

  // Timeout options
  timeout: {
    screenshot: 10000,
    preparation: 5000,
  }
};
