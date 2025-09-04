#!/bin/bash

# ===============================================================
# 🔥 Complete Pods Reset Script
# ===============================================================
# 
# This script completely resets Pods data, replicating exactly:
# "Pods Admin > Settings > Cleanup & Reset > Reset Pods entirely"
# 
# Usage: ./demo-data/pods-reset.sh
# ===============================================================

echo "🔥 SMART GALLERY FILTER - COMPLETE PODS RESET"
echo "=============================================="
echo ""

# Check if we're in the correct directory
if [[ ! -f "demo-data/pods-reset.php" ]]; then
    echo "❌ Error: Please run this script from the project root directory"
    echo "   Current directory: $(pwd)"
    echo "   Expected file: demo-data/pods-reset.php"
    exit 1
fi

# Check if DDEV is running
if ! ddev status > /dev/null 2>&1; then
    echo "❌ Error: DDEV is not running"
    echo "   Please start DDEV first: ddev start"
    exit 1
fi

echo "🔍 First, let's run a dry run to see what would be removed..."
echo ""

# Run dry run mode first
ddev exec wp eval-file demo-data/pods-reset.php

analysis_exit_code=$?

if [ $analysis_exit_code -ne 0 ]; then
    echo ""
    echo "❌ Dry run failed with exit code: $analysis_exit_code"
    echo "   Please check the error messages above"
    exit $analysis_exit_code
fi

echo ""
echo "⚠️  WARNING: This operation is IRREVERSIBLE!"
echo ""
echo "📋 This will completely remove:"
echo "   🗑️  All custom posts (cars, etc.)"
echo "   🗑️  All custom taxonomies and terms"
echo "   🗑️  All Pods configurations and fields"
echo "   🗑️  All featured images and attachments"
echo "   🗑️  All custom metadata"
echo "   🗑️  All Pods database tables"
echo "   🗑️  All orphaned data"
echo ""

# First confirmation
echo -n "❓ Are you sure you want to proceed with the complete reset? (y/N): "
read -r REPLY

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Reset cancelled by user"
    exit 0
fi

echo ""
echo "🔥 EXECUTING RESET NOW..."
echo ""

# Create temporary script for execution
temp_script="demo-data/pods-reset-execute.php"
{
    echo "<?php"
    echo "define('PODS_EXECUTE_RESET', true);"
    echo "define('WP_CLI', true);"
    echo "add_action('init', function() { remove_all_actions('admin_init'); }, 1);"
    echo "?>"
} > "$temp_script"
cat demo-data/pods-reset.php >> "$temp_script"

# Execute the reset
ddev exec wp eval-file "$temp_script"

reset_exit_code=$?

# Clean up temporary file
rm -f "$temp_script"

if [ $reset_exit_code -eq 0 ]; then
    echo ""
    echo "🎉 Complete Pods reset executed successfully!"
    echo ""
    echo "📋 What's next:"
    echo "   ✅ Pods plugin is still active"
    echo "   ✅ All configurations have been reset"
    echo "   ✅ Database is clean"
    echo "   🚀 You can now import fresh data: ./demo-data/pods-import.sh"
else
    echo ""
    echo "❌ Reset failed with exit code: $reset_exit_code"
    echo "   Please check the error messages above"
    exit $reset_exit_code
fi
