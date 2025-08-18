# Page Content Type Recipe

This recipe creates the Basic Page content type for static content.

## What this recipe installs

### Content Type
- **Page**: Basic page content type for static content like 'About us' pages

### Fields
- **Body**: Rich text field with summary

### Dependencies
- text
- node

## Usage

Apply this recipe to create the Basic Page content type:

```bash
ddev drush recipe:apply recipes/page_content_type
```

## Notes

- Pages do not show author or publication date by default
- Simple content type with minimal configuration
- Perfect for static content that doesn't change frequently