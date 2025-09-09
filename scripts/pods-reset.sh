#!/bin/bash

# Smart Gallery - Demo Data Reset Script
# This script completely resets all demo data for the Smart Gallery plugin

echo "🧹 Smart Gallery - Demo Data Reset"
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

echo
echo "🚨 WARNING: This will completely reset all demo data!"
echo "   This action will:"
echo "   • Delete all cars and dealers"
echo "   • Remove custom post types"
echo "   • Clear custom fields"
echo "   • Reset taxonomies"
echo "   • Clear all sample data"
echo
echo "⚠️  This action is IRREVERSIBLE!"
echo

if [[ "$AUTO_CONFIRM" == "true" ]]; then
    echo "🤖 Auto-confirmation enabled. Proceeding with reset..."
    REPLY="y"
else
    read -p "Are you sure you want to RESET all demo data? (y/N): " -n 1 -r
    echo
fi

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "😌 Cancelled. Demo data preserved."
    exit 0
fi

echo
echo "🗑️ Starting demo data reset..."

# Execute the reset using our PHP script
echo "📦 Executing reset script..."
ddev exec wp eval-file scripts/pods-reset.php

reset_exit_code=$?

if [ $reset_exit_code -eq 0 ]; then
    echo
    echo "✅ Demo data reset completed successfully!"
    echo
    echo "🧹 Reset summary:"
    echo "   • All cars and dealers removed"
    echo "   • Custom post types reset"
    echo "   • Custom fields cleared"
    echo "   • Taxonomies reset"
    echo
    echo "💡 To import fresh demo data:"
    echo "   ./scripts/pods-import.sh"
else
    echo
    echo "❌ Reset failed with exit code: $reset_exit_code"
    echo "   Please check the error messages above"
    exit $reset_exit_code
fi
