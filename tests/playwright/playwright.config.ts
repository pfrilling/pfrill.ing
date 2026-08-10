import { defineConfig, devices } from '@playwright/test';

// Base URL for the site under test. Override with BASE_URL env var.
const baseURL = process.env.BASE_URL || process.env.PLAYWRIGHT_BASE_URL || 'http://localhost';

export default defineConfig({
  testDir: './tests',
  retries: 0,
  timeout: 60000,
  use: {
    baseURL,
    trace: 'on-first-retry',
    actionTimeout: 15000,
    navigationTimeout: 30000,
    // Accept self-signed certs in case of non-prod envs
    ignoreHTTPSErrors: true,
  },
  // Visual testing expects configuration
  expect: {
    // Maximum difference in pixels between expected and actual screenshots
    toHaveScreenshot: { 
      maxDiffPixels: 100,
      threshold: 0.2,
      animations: 'disabled',
    },
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'chromium-desktop-visual',
      testMatch: '**/visual-regression.spec.ts',
      use: { 
        ...devices['Desktop Chrome'],
        viewport: { width: 1280, height: 720 },
      },
    },
    {
      name: 'chromium-mobile-visual',
      testMatch: '**/visual-regression.spec.ts', 
      use: { 
        ...devices['iPhone 12'],
        viewport: { width: 390, height: 844 },
      },
    },
  ],
});
