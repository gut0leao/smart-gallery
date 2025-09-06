#!/bin/bash

# Smart Gallery Filter - Demo Data Reset Script
# This script completely resets all demo data for the Smart Gallery Filter plugin

echo "🧹 Smart Gallery Filter - Demo Data Reset"
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

echo
echo "⚠️  WARNING: This will delete ALL demo data!"
echo "   - All Cars posts"
echo "   - All Dealers posts" 
echo "   - All related taxonomies and terms"
echo "   - All uploaded demo images"
echo "   - All Pods configurations for demo CPTs"
echo

# Ask for confirmation
if [[ "$PODS_EXECUTE_RESET" == "true" ]]; then
    echo "🤖 Auto-confirmation enabled. Proceeding with reset..."
    REPLY="y"
else
    read -p "Are you sure you want to continue? (y/N): " -n 1 -r
    echo
fi

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Reset cancelled."
    exit 1
fi

echo
echo "🗑️  Starting demo data cleanup..."

# Execute the reset using our PHP script with the execute flag
ddev exec wp eval "define('PODS_EXECUTE_RESET', true); include 'scripts/pods-reset.php';"

reset_exit_code=$?

if [ $reset_exit_code -eq 0 ]; then
    echo
    echo "✅ Demo data reset completed!"
    echo
    echo "📋 Summary:"
    echo "   - All car and dealer posts removed"
    echo "   - All demo taxonomies and terms deleted"
    echo "   - Pods configurations cleaned up"
    echo "   - Rewrite rules flushed"
    echo
    echo "🎯 Next step: Run './scripts/pods-import.sh' to import fresh demo data"
else
    echo
    echo "❌ Reset failed with exit code: $reset_exit_code"
    echo "   Please check the error messages above"
    exit $reset_exit_code
fi
