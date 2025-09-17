#!/bin/bash

# Smart Gallery - Fix Elementor Default Kit
# This script recreates the missing Elementor Default Kit

echo "🎨 Fixing Elementor Default Kit"
echo "==============================="

# Check if we're in DDEV environment
if ! command -v ddev &> /dev/null; then
    echo "❌ Error: DDEV not found. Please run this script from a DDEV environment."
    exit 1
fi

# Check if DDEV is running
if ! ddev status > /dev/null 2>&1; then
    echo "❌ Error: DDEV is not running"
    echo "   Please start DDEV first: ddev start"
    exit 1
fi

echo "🔧 Creating Elementor Default Kit..."

# Execute the PHP script to fix the kit
ddev exec wp eval-file scripts/fix-elementor-kit.php

result=$?

if [ $result -eq 0 ]; then
    echo
    echo "🎉 Success! Elementor Default Kit has been recreated"
    echo
    echo "💡 What to do next:"
    echo "   1. Go to WordPress admin: Elementor > Settings > General"
    echo "   2. Check if 'Default Kit' is now selected"
    echo "   3. Customize colors/fonts as needed"
    echo
else
    echo
    echo "❌ Failed to create Elementor Default Kit"
    echo
    echo "🔧 Manual solution:"
    echo "   1. Go to: WordPress Admin > Elementor > My Templates"
    echo "   2. Click 'Add New' > 'Theme Builder' > 'Kit'"
    echo "   3. Create a new kit and set it as default"
    echo
fi