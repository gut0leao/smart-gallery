#!/bin/bash

# ===============================================================
# 🚗 Car Catalog Import Script
# ===============================================================
# 
# This script imports a complete car catalog with 196 cars
# including featured images and taxonomies via Pods Framework
# 
# Usage: ./demo-data/pods-import.sh
# ===============================================================

echo "🚗 SMART GALLERY FILTER - CAR CATALOG IMPORT"
echo "============================================="
echo ""

# Check if we're in the correct directory
if [[ ! -f "demo-data/pods-import.php" ]]; then
    echo "❌ Error: Please run this script from the project root directory"
    echo "   Current directory: $(pwd)"
    echo "   Expected file: demo-data/pods-import.php"
    exit 1
fi

# Check if DDEV is running
if ! ddev status > /dev/null 2>&1; then
    echo "❌ Error: DDEV is not running"
    echo "   Please start DDEV first: ddev start"
    exit 1
fi

echo "📋 This will:"
echo "   ✅ Create 'car' CPT with custom fields"
echo "   ✅ Create car taxonomies (brand, body type, fuel, transmission)"
echo "   ✅ Import 196 cars with realistic data"
echo "   ✅ Upload 196 featured images"
echo "   ✅ Associate taxonomies based on filenames"
echo ""

read -p "🤔 Do you want to proceed with the import? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Import cancelled by user"
    exit 0
fi

echo ""
echo "🚀 Starting car catalog import..."
echo ""

# Execute the import script
ddev exec wp eval-file demo-data/pods-import.php

exit_code=$?

if [ $exit_code -eq 0 ]; then
    echo ""
    echo "🎉 Import completed successfully!"
    echo ""
    echo "📋 Next steps:"
    echo "   1. Visit: https://$(ddev describe | grep URLs | awk '{print $2}' | head -1)/wp-admin/edit.php?post_type=car"
    echo "   2. Test the gallery plugin with the imported data"
    echo "   3. Check featured images are properly associated"
else
    echo ""
    echo "❌ Import failed with exit code: $exit_code"
    echo "   Please check the error messages above"
    exit $exit_code
fi
