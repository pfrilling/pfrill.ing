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
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
