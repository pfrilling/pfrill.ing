# Personal blog for phil frilling.

## Domain pfrill.ing

### Local Development

```
ddev start
ddev composer install
ddev drush cache:rebuild
ddev drush config:import
ddev drush uli
```

### Running Playwright tests with ddev

Run the site’s Playwright end-to-end tests with a single command. The command will ensure ddev is started, auto-detect your PRIMARY_URL, and run the tests.

Basic usage:
```
ddev e2e
```

Helpful variants:
- Headed (see the browser):
```
ddev e2e --headed
```
- UI mode:
```
ddev e2e --ui
```
- First-time setup (installs npm deps and Playwright browsers on host):
```
ddev e2e --install
```

Notes:
- DDEV uses space-separated commands. The invocation is `ddev e2e` (not `ddev e2e:` or `ddev test:e2e`).
- Tests live in `tests/playwright`. If you prefer to run them manually, you still can (npm install, npx playwright install, then npm run scripts).
- BASE_URL is set automatically from `ddev describe` PRIMARY_URL.
- HTTPS errors are ignored by the Playwright config, so self-signed ddev certs are fine.
