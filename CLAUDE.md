# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a personal blog for Phil Frilling (pfrill.ing), built with Drupal 10 (composer.json shows core 10.3-10.5, but DDEV config shows drupal11 type). The site is a standard Drupal installation using the recommended project structure with contributed modules for content management, SEO, and development workflow.

## Development Setup

### Local Development with DDEV
```bash
ddev start
ddev composer install
ddev drush cache:rebuild
ddev drush config:import
ddev drush uli
```

### Essential Commands
- **Install dependencies**: `ddev composer install`
- **Cache rebuild**: `ddev drush cache:rebuild` or `ddev drush cr`
- **Config import**: `ddev drush config:import` or `ddev drush cim`
- **Config export**: `ddev drush config:export` or `ddev drush cex`
- **User login**: `ddev drush uli` (generates one-time login link)
- **Database updates**: `ddev drush updatedb` or `ddev drush updb`

### Code Quality
- **PHPCS linting**: `ddev exec vendor/bin/phpcs`
- **PHPUnit tests**: `ddev exec vendor/bin/phpunit`
- **Run specific test suite**: `ddev exec vendor/bin/phpunit --testsuite=unit` or `--testsuite=functional`
- **Fix code standards**: `ddev exec vendor/bin/phpcbf`

### End-to-End Testing
- **Run Playwright tests**: `ddev e2e` (headless mode using DDEV Node.js)
- **Run Playwright with browser**: `ddev e2e --headed` (uses host Node.js for GUI display)
- **Run Playwright UI mode**: `ddev e2e --ui` (uses host Node.js for GUI display)
- **Install Playwright dependencies**: `ddev e2e --install` (first run only)
- **Note**: Headless tests use DDEV container Node.js, GUI modes use host Node.js for display compatibility

### DDEV MTK (MySQL Toolkit) Add-on
This project uses the DDEV MTK add-on for efficient database management:
- **Database source**: Custom image `ghcr.io/pfrilling/pfrill.ing/database:latest`
- **MTK container**: Replaces standard DDEV database container (`omit_containers: [db]` in config)
- **Pull database**: `ddev pulldb` - manages database from custom Docker image
- **Environment variables**: MTK settings loaded via `.ddev/.env.web` (MTK_HOSTNAME=mtk, MTK_DATABASE=db, etc.)
- **Database connection**: Drupal connects to `mtk` container instead of standard `db` container
- **Legacy database**: `legacydb` database created automatically via post-start hook
- **Volume management**: If database is missing, stop DDEV, remove `ddev-pfrilling_mtk` volume, and restart

## Architecture

### Directory Structure
- `web/`: Document root containing Drupal core, contributed modules, and custom code
- `config/`: Configuration management split by environment
  - `config/common/`: Shared configuration across all environments
  - `config/local/`, `config/dev/`, `config/uat/`, `config/prod/`: Environment-specific configs
- `web/modules/custom/`: Custom Drupal modules
- `web/themes/custom/`: Custom Drupal themes
- `vendor/`: Composer dependencies

### Key Modules
The site uses several contributed modules:
- **admin_toolbar**: Enhanced admin toolbar
- **coffee**: Quick admin navigation
- **config_split**: Environment-specific configuration management
- **layout_builder**: Page layout management
- **migrate_plus/migrate_tools**: Content migration tools
- **pathauto**: Automatic URL alias generation
- **redirect**: URL redirect management
- **token**: Token replacement system

### Content Types
- **Article**: Blog posts with tags, client tags, images, and resources
- **Page**: Basic pages
- **Landing Page**: Layout builder-enabled pages

### Custom Modules
- **pf_migrate**: Custom migration plugins for importing content (articles, tags, client tags, files, users, URL redirects)
- **pf_user_roles**: User role management with functional tests for role-based permissions

### Drupal Recipes
The site uses Drupal recipes in `web/recipes/` for modular configuration:
- **essentials**: Meta-recipe that includes content_model and user_roles
- **content_model**: Content type definitions and field configurations  
- **user_roles**: Role definitions and permissions
- Individual content type recipes: article_content_type, page_content_type, landing_page_content_type
- Role-specific recipes: article_editor_role, page_editor_role, landing_page_editor_role

### Database Configuration
- **MTK Integration**: Settings file `settings.ddev-mtk.php` is included in main `settings.php` 
- **Database switching**: MTK settings override default DDEV database configuration when MTK container is active
- **Connection details**: Uses environment variables (MTK_HOSTNAME, MTK_DATABASE, etc.) for database connection
- **Dual setup**: Can work with either standard DDEV database container or MTK container based on configuration

## Configuration Management

The site uses Config Split for environment-specific configuration:
- Production config excludes development modules like dblog
- Local config includes development tools like dblog and views for watchdog
- Always export configuration changes: `ddev drush cex`

## Testing

### Unit and Functional Testing
- PHPUnit configuration in `phpunit.xml` with unit and functional test suites
- Tests should be placed in `web/modules/custom/*/tests/src/Unit/` or `web/modules/custom/*/tests/src/Functional/`
- Run all tests: `ddev exec vendor/bin/phpunit`
- Run specific test suite: `ddev exec vendor/bin/phpunit --testsuite=unit` or `--testsuite=functional`
- Test coverage reports generated in `tests/phpunit/results/`

### End-to-End Testing
- Playwright tests located in `tests/playwright/tests/`
- Configuration in `tests/playwright/playwright.config.ts`
- Tests automatically target the DDEV site URL via `BASE_URL` environment variable
- Current test coverage includes:
  - Home page functionality and navigation
  - Article listing and detail pages
  - User login page accessibility
  - Search functionality (when available)

## Code Standards

- Follows Drupal coding standards via phpcs.xml
- PHPCS rules target custom modules, profiles, and themes only
- Use `ddev exec vendor/bin/phpcs` to check standards
- Use `ddev exec vendor/bin/phpcbf` to auto-fix issues

## Troubleshooting

### MTK Database Issues
If the `db` database is missing from MTK container:
1. **Check database**: `docker exec ddev-pfrill.ing-mtk mysql -u root -proot -e "SHOW DATABASES;"`
2. **Fix missing database**: 
   ```bash
   ddev stop
   docker volume rm ddev-pfrilling_mtk
   ddev start
   ```
3. **Verify fix**: Database should now be loaded from custom image `ghcr.io/pfrilling/pfrill.ing/database:latest`

### Environment Variables
MTK environment variables should be in `.ddev/.env.web` (not `.ddev/.env.mtk.web`). DDEV loads service-specific environment files as `.env.<service>`.

## Developer Onboarding

### Modern Development Workflow (Recipe-Based Approach)

**⚠️ IMPORTANT: This project has moved away from traditional Drupal configuration management.**

#### What Changed
As of August 2025 (commit 97fe561), this project uses a **Drupal Recipe-based workflow** instead of traditional `drush config:export/import` patterns:

- **No more `drush cex/cim`**: Configuration is NOT managed through exportable YAML files
- **Recipe-driven**: All configuration changes must be implemented as Drupal recipes
- **Database-first**: Content and configuration come from pre-built, sanitized database containers
- **Automated updates**: Dependabot + automated testing handle package updates
- **E2E testing required**: All changes must pass end-to-end tests before deployment

### Database Management (Container Registry Approach)

#### Database Source
Databases are **NOT** managed locally or through traditional dumps:

1. **Primary database**: Pulled from container registry `ghcr.io/pfrilling/pfrill.ing/database:latest`
2. **Sanitized data**: Production database is sanitized using `sanitize-config.yml` before containerization
3. **Automated creation**: GitHub Actions workflow (`upsun-database-image.yml`) creates database containers from Upsun

#### Accessing Databases
```bash
# First-time setup: Authenticate with GitHub Container Registry
ddev ghcr-login                    # Interactive token input
# OR
ddev ghcr-login ghp_xxxxxxxxxxxx   # Direct token input

# Local development (via MTK)
ddev pulldb  # Pulls from container registry
docker exec ddev-pfrill.ing-mtk mysql -u root -proot

# CI/E2E testing
# Database automatically available as GitHub service container
# Image: ghcr.io/pfrilling/pfrill.ing/database:latest
```

#### GitHub Container Registry Authentication
To access the private database container, you need a GitHub Personal Access Token (PAT):

1. **Create PAT**: Go to https://github.com/settings/tokens
2. **Required permissions**: `read:packages` (to pull container images)
3. **Authenticate**: `ddev ghcr-login` (interactive) or `ddev ghcr-login <your-token>`
4. **Restart DDEV**: `ddev restart` (pulls custom database image)

**Note**: Authentication is required only once per development environment.

#### Database Workflow
1. **Production changes** happen on Upsun platform
2. **Database sanitization** runs via GitHub Actions (manual trigger)
3. **New container image** pushed to `ghcr.io/pfrilling/pfrill.ing/database:latest`
4. **Local development** pulls updated container via `ddev pulldb`

### Automated Package Management

#### Dependabot Configuration
- **Weekly updates**: Composer packages updated automatically
- **Grouped updates**: Drupal core components updated together
- **Major version protection**: Major Drupal updates ignored (manual review required)
- **Location**: `.github/dependabot.yml`

#### Update Deployment Process
1. **Dependabot** creates PR with package updates
2. **E2E tests** run automatically (`e2e-tests.yml`)
3. **Auto-merge** if tests pass (configured in repository settings)
4. **Production deployment** happens automatically via platform integration

### Recipe-Based Configuration Management

#### Core Concept
All configuration changes MUST be implemented as recipes in `web/recipes/`:

```
web/recipes/
├── essentials/          # Meta-recipe (includes all others)
├── content_model/       # Content types and fields
├── user_roles/          # User roles and permissions
├── performance/         # Performance-related config
├── article_content_type/ # Article content type recipe
├── page_content_type/    # Page content type recipe
├── landing_page_content_type/ # Landing page content type recipe
└── *_editor_role/       # Role-specific permission recipes
```

#### Recipe Development Workflow
1. **Create recipe**: New functionality requires a new recipe in `web/recipes/`
2. **Test locally**: Apply recipe via `drush recipe web/recipes/your-recipe`
3. **Update essentials**: Add your recipe to `web/recipes/essentials/recipe.yml`
4. **E2E tests**: Ensure all tests pass with your changes
5. **Deploy**: Recipe automatically applied in CI/production

#### Key Recipe Files
- **`recipe.yml`**: Recipe definition with dependencies, config, content
- **`config/`**: Exportable configuration (if any)
- **`README.md`**: Recipe documentation and usage

### End-to-End Testing Requirements

#### Testing Philosophy
**E2E tests are the primary quality gate** for automated updates. All changes must maintain test compatibility.

#### Test Structure
```
tests/playwright/
├── package.json         # Node.js dependencies
├── playwright.config.ts # Playwright configuration
└── tests/
    └── site.spec.ts     # Main site functionality tests
```

#### Current Test Coverage
- **Home page functionality** and navigation
- **Article listing** and detail pages
- **User login** page accessibility
- **Search functionality** (when available)
- **Content rendering** validation

#### Adding Tests for New Features
When adding new functionality:

1. **Write E2E tests first**: Define expected behavior in Playwright tests
2. **Test critical paths**: Focus on user-facing functionality
3. **Test data integrity**: Verify content displays correctly
4. **Update test suite**: Add tests to `tests/playwright/tests/`
5. **Verify CI**: Ensure tests pass in GitHub Actions environment

#### Running E2E Tests
```bash
# Local development
ddev e2e                 # Headless mode
ddev e2e --headed        # With browser UI
ddev e2e --ui            # Interactive mode
ddev e2e --install       # First-time setup

# CI Environment
# Automatically runs on all PRs via .github/workflows/e2e-tests.yml
```

### Development Guidelines

#### DO NOT Do These Traditional Drupal Tasks
- ❌ `drush config:export` / `drush cex`
- ❌ `drush config:import` / `drush cim`
- ❌ Manual database dumps/imports
- ❌ Direct configuration file editing in `config/`

#### DO Follow This Recipe-Based Workflow
- ✅ Create recipes for all configuration changes
- ✅ Test recipes locally before committing
- ✅ Write E2E tests for new functionality
- ✅ Use container registry for database access
- ✅ Let automated systems handle deployments

#### Content Development
- **Content changes**: Make on production Upsun platform
- **Structure changes**: Implement as recipes
- **Database refresh**: Trigger sanitized database rebuild when needed