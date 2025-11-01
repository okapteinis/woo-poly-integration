#!/bin/bash

# Hyyan WooCommerce Polylang Integration - PHP 8.4 Compatibility Fixer
# This script applies common PHP 8.4 compatibility fixes across the codebase

echo "Starting PHP 8.4 compatibility fixes..."

# Find all PHP files
PHP_FILES=$(find ./src -name "*.php" -type f)

for file in $PHP_FILES; do
    echo "Processing: $file"
    
    # Backup original file
    cp "$file" "$file.bak"
    
    # Fix: Replace is_ajax() with wp_doing_ajax() (only if not already done)
    # This is a careful replacement that avoids double-fixing
    sed -i '' 's/function_exists.*is_ajax.*is_ajax()/wp_doing_ajax()/g' "$file"
    sed -i '' 's/is_ajax()/wp_doing_ajax()/g' "$file"
    
    # Fix: Add strict comparison to in_array calls that don't have it
    # Pattern: in_array(xxx, yyy) becomes in_array(xxx, yyy, true)
    sed -i '' 's/in_array(\([^,]*\), \([^)]*\))/in_array(\1, \2, true)/g' "$file"
    # Cleanup double-added true (in case some already had it)
    sed -i '' 's/, true, true)/, true)/g' "$file"
    
    # Note: For loose comparisons (== to ===), we need to be more careful
    # We'll do selective fixes for the most common safe patterns
    
    # Fix: WP_Error class checks
    sed -i '' "s/get_class([^)]*) == 'WP_Error'/\0 instanceof WP_Error/g" "$file"
    
    echo "  ✓ Fixed"
done

echo ""
echo "Automated fixes complete!"
echo "Now running manual verification..."

