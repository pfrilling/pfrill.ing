# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a personal blog for Phil Frilling (pfrill.ing), built with Drupal 10. The site is a standard Drupal installation using the recommended project structure with contributed modules for content management, SEO, and development workflow.

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