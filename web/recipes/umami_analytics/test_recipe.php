<?php

/**
 * @file
 * Test script for the Umami Analytics recipe.
 *
 * This script simulates the recipe application process and verifies that
 * the environment variable is correctly used for the website_id through the input section.
 */

// Set a test environment variable.
putenv('UMAMI_WEBSITE_ID=test-website-id-123');

// Simulate loading the recipe.
$recipe_file = __DIR__ . '/recipe.yml';
$recipe_content = file_get_contents($recipe_file);
echo "Recipe content loaded successfully.\n";

// Check if the recipe contains the input section with environment variable.
if (strpos($recipe_content, 'source: env') !== false &&
    strpos($recipe_content, 'env: UMAMI_WEBSITE_ID') !== false) {
  echo "Input section with environment variable found in recipe.\n";
} else {
  echo "ERROR: Input section with environment variable not found in recipe.\n";
  exit(1);
}

// Check if the recipe uses simpleConfigUpdate with the input variable.
if (strpos($recipe_content, 'simpleConfigUpdate:') !== false &&
    strpos($recipe_content, 'website_id: ${website_id}') !== false) {
  echo "simpleConfigUpdate with input variable found in recipe.\n";
} else {
  echo "ERROR: simpleConfigUpdate with input variable not found in recipe.\n";
  exit(1);
}

// In a real scenario, the recipe system would process the input section,
// retrieve the environment variable value, and use it in the simpleConfigUpdate.
// Here we just verify the recipe structure is correct.

echo "Test completed successfully. The recipe is configured to use the UMAMI_WEBSITE_ID environment variable through the input section.\n";
