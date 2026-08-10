# Visual Regression Testing

This directory contains visual regression tests that capture screenshots and compare them against baseline images to detect unintended visual changes.

## Quick Start

```bash
# Run all visual regression tests
ddev e2e --grep "Visual Regression Testing"

# Run specific viewport tests only
ddev e2e --project=chromium-desktop-visual
ddev e2e --project=chromium-mobile-visual

# Update all visual baselines (use after intentional visual changes)
ddev e2e --grep "Visual Regression Testing" --update-snapshots
```

## Test Coverage

### Pages Tested
- **Homepage** (`/`) - Full page with articles and navigation
- **Article Detail** - Individual article pages
- **Search Results** (`/search/node?keys=drupal`) - Search functionality
- **Login Page** (`/user/login`) - User authentication interface

### Viewport Coverage
- **Desktop**: 1280x720 (`chromium-desktop-visual`)
- **Mobile**: 390x844 (`chromium-mobile-visual`)

## Dynamic Content Masking

Visual tests automatically hide dynamic content that changes between test runs:

- Timestamps and dates
- User session information
- Contextual admin links
- Form tokens and build IDs
- Status messages
- Breadcrumbs and local tasks

## Baseline Management

### Creating New Baselines
```bash
# Create baselines for new tests
ddev e2e --update-snapshots --grep "test-name"

# Create baselines for specific viewport
ddev e2e --project=chromium-desktop-visual --update-snapshots
```

### Updating Existing Baselines
When you make intentional visual changes:

1. Run tests to see failures: `ddev e2e --grep "Visual Regression"`
2. Review the diff images in `test-results/`
3. If changes are correct, update baselines: `ddev e2e --update-snapshots --grep "Visual Regression"`
4. Commit the new baseline images to Git

### Baseline Storage
- **Location**: `tests/visual-regression.spec.ts-snapshots/`
- **Naming**: `{test-name}-{project-name}-linux.png`
- **Version Control**: Baselines are stored in Git repository

## CI/CD Integration

Visual regression tests run automatically on:
- Pull requests
- Workflow dispatch events

### Failure Handling
When visual tests fail in CI:
1. **Artifacts**: Visual diff images are uploaded as GitHub Actions artifacts
2. **Auto-merge**: Dependabot PRs will not auto-merge with visual test failures
3. **Manual Review**: Developer must review diffs and update baselines manually

### Updating Baselines in CI
Baselines must be updated manually - never automatically in CI:

1. Run tests locally with `--update-snapshots`
2. Review and approve the changes
3. Commit new baselines to repository
4. Push to trigger new CI run

## Troubleshooting

### Test Failures
```bash
# See detailed diff information
ddev e2e --project=chromium-desktop-visual --reporter=html

# Debug specific test with trace
ddev e2e --grep "login page" --trace on
```

### Browser Dependencies
If you see browser dependency errors:
```bash
# Install browsers in DDEV container
ddev exec "cd tests/playwright && npx playwright install"
```

### Screenshot Differences
Common causes of visual test failures:
- **Fonts**: Different font rendering between environments
- **Timing**: Page not fully loaded before screenshot
- **Dynamic Content**: New dynamic elements not masked
- **Dependencies**: Different package versions affecting styling

## Configuration

### Visual Test Settings
- **Max Diff Pixels**: 100 pixels can differ
- **Threshold**: 0.2 (20% difference tolerance)
- **Animations**: Disabled during screenshot capture
- **Full Page**: Always captures entire page scroll height

### Viewport Configurations
- **Desktop**: Chrome desktop device with 1280x720 viewport
- **Mobile**: iPhone 12 device preset with 390x844 viewport

## File Structure

```
tests/playwright/
├── tests/
│   ├── site.spec.ts                    # Functional tests
│   └── visual-regression.spec.ts       # Visual regression tests
├── visual-config.ts                    # Visual test utilities
├── playwright.config.ts                # Test configuration
└── tests/visual-regression.spec.ts-snapshots/  # Baseline screenshots
    ├── *-chromium-desktop-visual-linux.png
    └── *-chromium-mobile-visual-linux.png
```