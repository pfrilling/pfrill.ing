# Personal blog for Phil Frilling

## Domain: pfrill.ing

**⚠️ IMPORTANT: This project uses a modern Recipe-based workflow, NOT traditional Drupal configuration management.**

### Quick Start for New Developers

#### Local Development Setup
```bash
# Start the development environment
ddev start
ddev composer install

# First-time only: Authenticate with GitHub Container Registry
ddev ghcr-login

# Pull sanitized database from container registry (replaces traditional DB import)
ddev pulldb

# Apply current recipes (replaces drush config:import)
ddev drush recipe web/recipes/essentials --yes

# Get login link
ddev drush uli
```

### Key Workflow Changes (August 2025)

#### ❌ What NOT to do (Old Workflow)
- `drush config:export` / `drush cex`
- `drush config:import` / `drush cim`
- Manual database dumps/imports
- Direct editing of config YAML files

#### ✅ What TO do (New Recipe-Based Workflow)
- Create Drupal recipes for configuration changes
- Use `ddev pulldb` to get database from container registry
- Write E2E tests for all new features
- Let Dependabot handle package updates automatically

### Database Management

This project uses **containerized databases** instead of traditional database management:

- **Database source**: `ghcr.io/pfrilling/pfrill.ing/database:latest`
- **Local access**: `ddev pulldb` (pulls from container registry)
- **Production data**: Automatically sanitized before containerization
- **Updates**: Triggered manually via GitHub Actions when production changes

#### Authentication Setup

**First-time setup requires GitHub authentication:**

```bash
# Interactive authentication (recommended)
ddev ghcr-login

# Or direct token input
ddev ghcr-login ghp_xxxxxxxxxxxx
```

**To create a GitHub Personal Access Token:**
1. Go to https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Select scope: `read:packages`
4. Copy the token and use with `ddev ghcr-login`

**After authentication:**
- Run `ddev restart` to pull the custom database image
- Authentication persists until Docker credentials are cleared

### Automated Updates

- **Dependabot**: Weekly Composer package updates
- **Auto-testing**: E2E tests run on every PR
- **Auto-deployment**: Updates deploy automatically if tests pass

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

### Recipe-Based Configuration

All site configuration is managed through Drupal recipes in `web/recipes/`:

```bash
# Apply all essential recipes
ddev drush recipe web/recipes/essentials --yes

# Apply individual recipes
ddev drush recipe web/recipes/article_content_type --yes
ddev drush recipe web/recipes/user_roles --yes
```

#### Creating New Recipes

1. Create recipe directory: `web/recipes/my-feature/`
2. Add `recipe.yml` with configuration
3. Test locally: `ddev drush recipe web/recipes/my-feature --yes`
4. Add to essentials: Update `web/recipes/essentials/recipe.yml`
5. Write E2E tests for the feature
6. Commit and let CI handle deployment

### Database Troubleshooting

If database is missing or corrupted:

```bash
# Reset database from container registry
ddev stop
docker volume rm ddev-pfrilling_mtk
ddev start
ddev drush recipe web/recipes/essentials --yes
```

### Development Workflow

1. **Pull latest code**: `git pull origin main`
2. **Update dependencies**: `ddev composer install`
3. **Refresh database**: `ddev pulldb` (if needed)
4. **Apply recipes**: `ddev drush recipe web/recipes/essentials --yes`
5. **Start coding**: Make changes using recipe-based approach
6. **Test changes**: `ddev e2e` (critical for automated deployments)
7. **Commit and push**: CI will handle testing and deployment

### Key Files and Directories

- `web/recipes/`: All configuration recipes
- `tests/playwright/`: End-to-end tests
- `sanitize-config.yml`: Database sanitization rules
- `.github/workflows/e2e-tests.yml`: Automated testing
- `.github/dependabot.yml`: Automated package updates
