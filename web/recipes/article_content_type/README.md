# Article Content Type Recipe

This recipe creates the Article content type with all its associated fields and configuration.

## What this recipe installs

### Content Type
- **Article**: Blog post content type for time-sensitive content like news, press releases or blog posts

### Fields
- **Body**: Rich text field with summary
- **Image**: Image field with alt text (required)
- **Tags**: Taxonomy reference to Tags vocabulary (autocomplete)
- **Client Tags**: Taxonomy reference to Client Tags vocabulary
- **Resources**: File field for downloadable resources (txt files)
- **Comments**: Comment field for user feedback
- **Layout**: Layout Builder field for custom layouts

### Taxonomies
- **Tags**: General purpose tagging vocabulary
- **Client Tags**: Client-specific tagging vocabulary

### Dependencies
- comment
- image
- file
- taxonomy
- text
- node
- menu_ui
- layout_builder

## Usage

Apply this recipe to create the Article content type:

```bash
ddev drush recipe:apply recipes/article_content_type
```

## Notes

- Articles are configured to show author and publication date
- Comments are disabled by default but field is available
- Tags support auto-creation of new terms
- Client tags must be created manually
- Images are stored in date-based directories (YYYY-MM format)
- Layout Builder is available for custom article layouts