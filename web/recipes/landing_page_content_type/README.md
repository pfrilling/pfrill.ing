# Landing Page Content Type Recipe

This recipe creates the Landing Page content type with Layout Builder support.

## What this recipe installs

### Content Type
- **Landing Page**: Content type with Layout Builder for custom page layouts

### Fields
- **Layout**: Layout Builder field for drag-and-drop page building

### Dependencies
- layout_builder
- node
- menu_ui

## Usage

Apply this recipe to create the Landing Page content type:

```bash
ddev drush recipe:apply recipes/landing_page_content_type
```

## Notes

- Landing pages do not show author or publication date by default
- Uses Layout Builder for complete layout control
- Perfect for marketing pages, homepages, and custom layouts
- Available in main menu by default