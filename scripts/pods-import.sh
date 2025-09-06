#!/bin/bash

# Smart Gallery Filter - Demo Data Import Script
# echo "📦 Executing import script..."

# Execute the import using our PHP script
ddev exec wp eval-file scripts/pods-import.php

import_exit_code=$?cript imports all demo data for the Smart Gallery Filter plugin

echo "� Smart Gallery Filter - Demo Data Import"
echo "========================================"

# Check if we're in DDEV environment
if ! command -v ddev &> /dev/null; then
    echo "❌ Error: DDEV not found. Please run this script from a DDEV environment."
    exit 1
fi

# Check if WordPress is accessible
if ! ddev exec wp core is-installed --quiet 2>/dev/null; then
    echo "❌ Error: WordPress is not installed or not accessible."
    exit 1
fi

# Check if DDEV is running
if ! ddev status > /dev/null 2>&1; then
    echo "❌ Error: DDEV is not running"
    echo "   Please start DDEV first: ddev start"
    exit 1
fi

# Check if Pods plugin is active
if ! ddev exec wp plugin is-active pods --quiet 2>/dev/null; then
    echo "❌ Error: Pods plugin is not active. Please activate it first:"
    echo "   ddev exec wp plugin activate pods"
    exit 1
fi

# Check if Smart Gallery Filter plugin is active
if ! ddev exec wp plugin is-active smart-gallery-filter --quiet 2>/dev/null; then
    echo "❌ Error: Smart Gallery Filter plugin is not active. Please activate it first:"
    echo "   ddev exec wp plugin activate smart-gallery-filter"
    exit 1
fi

echo
echo "🚀 Starting demo data import..."
echo "   This will create:"
echo "   - Car and Dealer custom post types"
echo "   - Related taxonomies (brands, body types, etc.)"
echo "   - Sample cars with images (196 items)"
echo "   - Sample dealers (5 items)"
echo "   - Proper taxonomy associations"
echo

# Check for non-interactive mode
if [ -t 0 ]; then
    read -p "🤔 Do you want to proceed with the import? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Import cancelled by user"
        exit 0
    fi
else
    echo "🤖 Running in non-interactive mode, proceeding with import..."
fi

echo
echo "� Executing import script..."

# Execute the import using our PHP script
ddev exec wp eval-file scripts/pods-import.php

import_exit_code=$?

if [ $import_exit_code -eq 0 ]; then
    echo
    echo "✅ Demo data import completed!"
    echo
    echo "🎯 Next steps:"
    echo "   1. Visit wp-admin/edit.php?post_type=car to see imported cars"
    echo "   2. Visit wp-admin/edit.php?post_type=dealer to see imported dealers"
    echo "   3. Check taxonomy menus in WordPress admin sidebar"
    echo "   4. Test the Smart Gallery Filter widget in Elementor"
    echo
    echo "🔧 Available taxonomies:"
    echo "   - Car Brand (shared with dealers)"
    echo "   - Car Body Type, Fuel Type, Transmission"
    echo "   - Car Location, Dealer Location"
else
    echo
    echo "❌ Import failed with exit code: $import_exit_code"
    echo "   Please check the error messages above"
    echo "   You may need to run './scripts/pods-reset.sh' first"
    exit $import_exit_code
fi
