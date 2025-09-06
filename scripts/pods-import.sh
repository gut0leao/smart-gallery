#!/bin/bash

# Smart Gallery Filter - Demo Data Import Script
# This script imports all demo data for the Smart Gallery Filter plugin

echo "📦 Smart Gallery Filter - Demo Data Import"
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
echo "   - Custom fields and taxonomies"
echo "   - Sample data (cars and dealers)"
echo "   - Hierarchical location taxonomy"

# Execute the import using our PHP script
echo "📦 Executing import script..."
ddev exec wp eval-file scripts/pods-import.php

import_exit_code=$?

if [ $import_exit_code -eq 0 ]; then
    echo
    echo "✅ Demo data import completed successfully!"
    echo
    echo "📊 Import summary:"
    echo "   • Custom post types created (cars, dealers)"  
    echo "   • Custom fields configured"
    echo "   • Sample data imported"
    echo "   • Location taxonomy with hierarchical structure"
    echo
    echo "🌐 Next steps:"
    echo "1. Access your site: https://smart-gallery-filter.ddev.site"
    echo "2. Check wp-admin: https://smart-gallery-filter.ddev.site/wp-admin"
    echo "3. Configure your Elementor widget"
else
    echo
    echo "❌ Import failed with exit code: $import_exit_code"
    echo "   Please check the error messages above"
    echo "   You may need to:"
    echo "   • Ensure all required plugins are active"
    echo "   • Check database connectivity"
    echo "   • Verify file permissions"
    exit $import_exit_code
fi
