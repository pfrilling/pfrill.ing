# Umami Analytics Recipe

This recipe installs and configures the Umami Analytics module with environment variable support for the website ID.

## Requirements

- Drupal 9 or higher
- Umami Analytics module

## Installation

1. Make sure the Umami Analytics module is available in your project
2. Set the `UMAMI_WEBSITE_ID` environment variable with your Umami website ID
3. Apply this recipe using Drupal's recipe system

## Environment Variables

This recipe uses the following environment variables through the input section:

- `UMAMI_WEBSITE_ID`: Your Umami Analytics website ID (required)

## Example Usage

```bash
# Set the environment variable
export UMAMI_WEBSITE_ID="your-website-id-here"

# Apply the recipe
drush recipe:import umami_analytics
```

## Configuration

The recipe only configures the Website ID for Umami Analytics:

- Website ID: Uses the value from the `UMAMI_WEBSITE_ID` environment variable

All other configuration settings will use the module's default values. You can configure additional settings after installation through the Umami Analytics configuration page or by editing the configuration files directly.
