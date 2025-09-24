#!/bin/bash

# 🚀 Smart Gallery Plugin - Package Builder Script
# This script creates a deployment-ready ZIP package of the plugin
# 
# What it does:
#   - Validates PHP syntax and code quality
#   - Creates clean ZIP package without dev files
#   - Generates checksums and deployment info
#   - Prepares package for upload/deployment
#
# What it does NOT do:
#   - Deploy to remote servers
#   - Upload to GitHub Packages
#   - Install on WordPress instances
# 
# Usage:
#   ./scripts/package.sh              # Auto-detect DDEV or local PHP
#   FORCE_LOCAL=1 ./scripts/package.sh # Force local PHP (skip DDEV)
#
# Requirements:
#   - DDEV (preferred) or local PHP 8.0+
#   - zip utility
#   - Git (optional, for version info)

set -e

# Configuration
PLUGIN_NAME="smart-gallery"
PLUGIN_DIR="wp-content/plugins/smart-gallery"
DIST_DIR="dist"
BUILD_DIR="dist/builds"  # Directory for final packages
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check prerequisites
check_prerequisites() {
    log_info "Checking prerequisites..."
    
    if [ ! -d "$PLUGIN_DIR" ]; then
        log_error "Plugin directory not found: $PLUGIN_DIR"
        exit 1
    fi
    
    # Check if DDEV is available and running (unless forced to use local)
    if [ "${FORCE_LOCAL:-}" != "1" ] && command -v ddev &> /dev/null; then
        if ddev status > /dev/null 2>&1; then
            USE_DDEV=true
            PHP_VERSION=$(ddev exec php -v | head -1 | cut -d' ' -f2)
            log_info "Using DDEV environment (PHP $PHP_VERSION)"
        else
            log_warning "DDEV found but not running. Falling back to local PHP"
            log_info "Tip: Run 'ddev start' to use DDEV environment"
            USE_DDEV=false
        fi
    else
        USE_DDEV=false
    fi
    
    # Fallback to local PHP if DDEV not used
    if [ "$USE_DDEV" = false ]; then
        if command -v php &> /dev/null; then
            PHP_VERSION=$(php -v | head -1 | cut -d' ' -f2)
            log_info "Using local PHP installation (PHP $PHP_VERSION)"
        else
            log_error "No PHP found. Please install PHP or start DDEV with 'ddev start'"
            exit 1
        fi
    fi
    
    # Check if zip utility is available (prefer host system for simplicity)
    ZIP_CMD=""
    if command -v zip >/dev/null 2>&1; then
        ZIP_CMD="zip"
        log_info "Using host system zip"
    elif ddev exec which zip >/dev/null 2>&1; then
        ZIP_CMD="ddev exec zip"
        log_warning "Using DDEV zip (host zip not available)"
    else
        log_error "ZIP utility is not installed on host or DDEV container"
        log_info "Install with: sudo apt-get install zip (Ubuntu/Debian) or brew install zip (macOS)"
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

# PHP Syntax Check
php_syntax_check() {
    log_info "Running PHP syntax check..."
    
    # Function to run PHP command (DDEV or local)
    run_php() {
        if [ "$USE_DDEV" = true ]; then
            ddev exec php "$@"
        else
            php "$@"
        fi
    }
    
    find "$PLUGIN_DIR" -name "*.php" -print0 | while IFS= read -r -d '' file; do
        if ! run_php -l "$file" > /dev/null 2>&1; then
            log_error "PHP syntax error in: $file"
            run_php -l "$file"
            exit 1
        fi
    done
    
    log_success "PHP syntax check passed"
}

# Security and Quality Checks
security_check() {
    log_info "Running security and quality checks..."
    
    # Check for eval() usage
    if grep -r "eval(" "$PLUGIN_DIR" --include="*.php" > /dev/null; then
        log_warning "Found eval() usage - please review for security"
        grep -r "eval(" "$PLUGIN_DIR" --include="*.php"
    fi
    
    # Check for direct database queries (should use WordPress functions)
    if grep -r "\$wpdb->query" "$PLUGIN_DIR" --include="*.php" > /dev/null; then
        log_info "Found direct database queries - ensure they're properly sanitized"
    fi
    
    # Check for unescaped output
    if grep -r "echo \$" "$PLUGIN_DIR" --include="*.php" | grep -v "esc_" > /dev/null; then
        log_warning "Found potentially unescaped output - please review"
    fi
    
    log_success "Security check completed"
}

# Show environment info
show_environment_info() {
    log_info "Environment Information:"
    
    if [ "$USE_DDEV" = true ]; then
        echo "🐳 DDEV Environment:"
        echo "   Project: $(ddev describe | grep 'Name:' | awk '{print $2}' 2>/dev/null || echo 'smart-gallery')"
        echo "   Status: Running"
        echo "   PHP: $(ddev exec php -v | head -1 | cut -d' ' -f1-2)"
        echo "   WordPress: $(ddev exec wp --version 2>/dev/null || echo 'WP-CLI not available')"
    else
        echo "💻 Local Environment:"
        echo "   PHP: $(php -v | head -1 | cut -d' ' -f1-2)"
        echo "   OS: $(uname -s)"
    fi
    
    echo "📁 Plugin Directory: $PLUGIN_DIR"
    echo "📦 Temp Directory: $DIST_DIR"
    echo "📦 Build Directory: $BUILD_DIR"
    echo ""
}

# Get version from plugin file
get_version() {
    local version=$(grep "Version:" "$PLUGIN_DIR/smart-gallery.php" | head -1 | sed 's/.*Version: *//' | sed 's/ *\*\/.*//' | tr -d '\r\n')
    if [ -z "$version" ]; then
        version="1.0.0-dev-$TIMESTAMP"
        log_warning "Version not found in plugin file, using: $version"
    fi
    echo "$version"
}

# Create package
create_package() {
    local version=$1
    local package_name="${PLUGIN_NAME}-${version}"
    
    log_info "Creating plugin package: $package_name"
    
    # Clean and create directories
    rm -rf "$DIST_DIR"
    mkdir -p "$DIST_DIR"
    mkdir -p "$BUILD_DIR"
    
    # Copy plugin files
    cp -r "$PLUGIN_DIR" "$DIST_DIR/"
    
    # Clean up development files
    log_info "Cleaning development files..."
    find "$DIST_DIR/$PLUGIN_NAME" -name "*.md" -not -name "README.md" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name ".git*" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "*.tmp" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "*.log" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "debug-*.php" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "test-*.php" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "*~" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "*.bak" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name ".DS_Store" -delete
    find "$DIST_DIR/$PLUGIN_NAME" -name "Thumbs.db" -delete
    
    # Create ZIP package
    log_info "Creating zip file: ${package_name}.zip"
    
    # Get absolute paths
    BUILD_PATH="$(pwd)/$BUILD_DIR"
    DIST_PATH="$(pwd)/$DIST_DIR"
    
    cd "$DIST_DIR"
    if [ "$ZIP_CMD" = "ddev exec zip" ]; then
        if ! ddev exec zip -r "$BUILD_PATH/${package_name}.zip" "$PLUGIN_NAME/" > /dev/null 2>&1; then
            log_error "Failed to create ZIP package using DDEV"
            exit 1
        fi
    else
        if ! zip -r "$BUILD_PATH/${package_name}.zip" "$PLUGIN_NAME/" > /dev/null 2>&1; then
            log_error "Failed to create ZIP package"
            exit 1
        fi
    fi
    cd ..
    
    # Generate checksums in build directory
    cd "$BUILD_DIR"
    sha256sum "${package_name}.zip" > "${package_name}.zip.sha256"
    md5sum "${package_name}.zip" > "${package_name}.zip.md5"
    cd - > /dev/null
    
    # Display package info
    log_success "Package created successfully!"
    echo "📦 Package: $BUILD_DIR/${package_name}.zip"
    echo "📊 Size: $(du -h "$BUILD_DIR/${package_name}.zip" | cut -f1)"
    echo "🔒 SHA256: $(cat "$BUILD_DIR/${package_name}.zip.sha256" | cut -d' ' -f1)"
    echo "🔒 MD5: $(cat "$BUILD_DIR/${package_name}.zip.md5" | cut -d' ' -f1)"
    
    # List contents
    echo ""
    log_info "Package contents:"
    unzip -l "$BUILD_DIR/${package_name}.zip" | head -20
    if [ $(unzip -l "$BUILD_DIR/${package_name}.zip" | wc -l) -gt 25 ]; then
        echo "... (and more files)"
    fi
}

# Test package
test_package() {
    local version=$1
    local package_name="${PLUGIN_NAME}-${version}"
    
    log_info "Testing package integrity..."
    
    # Change to build directory for testing
    cd "$BUILD_DIR"
    
    # Verify checksums
    if sha256sum -c "${package_name}.zip.sha256" > /dev/null 2>&1; then
        log_success "SHA256 checksum verified"
    else
        log_error "SHA256 checksum verification failed"
        cd - > /dev/null
        exit 1
    fi
    
    # Test ZIP integrity
    if unzip -t "${package_name}.zip" > /dev/null 2>&1; then
        log_success "ZIP integrity verified"
    else
        log_error "ZIP integrity check failed"
        cd - > /dev/null
        exit 1
    fi
    
    cd - > /dev/null
    
    # Extract and test PHP syntax in package
    log_info "Testing extracted package..."
    temp_dir="./temp-package-test"
    rm -rf "$temp_dir"
    mkdir -p "$temp_dir"
    unzip -q "$BUILD_DIR/${package_name}.zip" -d "$temp_dir"
    
    find "$temp_dir" -name "*.php" -print0 | while IFS= read -r -d '' file; do
        if [ "$USE_DDEV" = true ]; then
            # Convert absolute path to relative for DDEV
            relative_file="${file#./}"
            if ! ddev exec php -l "/var/www/html/$relative_file" > /dev/null 2>&1; then
                log_error "PHP syntax error in packaged file: $file"
                exit 1
            fi
        else
            if ! php -l "$file" > /dev/null 2>&1; then
                log_error "PHP syntax error in packaged file: $file"
                exit 1
            fi
        fi
    done
    
    # Clean up temp directory
    rm -rf "$temp_dir"
    
    rm -rf "$temp_dir"
    log_success "Package testing completed"
}

# Generate deployment info
generate_info() {
    local version=$1
    local package_name="${PLUGIN_NAME}-${version}"
    
    cat > "$BUILD_DIR/${package_name}.info" << EOF
Smart Gallery Plugin Deployment Package
=======================================

Package: ${package_name}.zip
Version: ${version}
Created: $(date -u)
Git Commit: $(git rev-parse HEAD 2>/dev/null || echo "N/A")
Git Branch: $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "N/A")

Files: $(unzip -l "$BUILD_DIR/${package_name}.zip" | tail -1 | awk '{print $2}')
Size: $(du -h "$BUILD_DIR/${package_name}.zip" | cut -f1)

Checksums:
SHA256: $(cat "$BUILD_DIR/${package_name}.zip.sha256" | cut -d' ' -f1)
MD5: $(cat "$BUILD_DIR/${package_name}.zip.md5" | cut -d' ' -f1)

Installation:
1. Upload ${package_name}.zip to WordPress
2. Extract to wp-content/plugins/
3. Activate Smart Gallery plugin
4. Configure Elementor widget

Requirements:
- WordPress 6.0+
- Elementor 3.0+
- Pods Framework 3.0+
- PHP 8.0+

EOF

    log_success "Deployment info generated: ${package_name}.info"
}

# Main execution
main() {
    echo ""
    log_info "🚀 Smart Gallery Plugin - Package Builder Script"
    echo ""
    
    check_prerequisites
    show_environment_info
    php_syntax_check
    security_check
    
    version=$(get_version)
    log_info "Plugin version: $version"
    
    create_package "$version"
    test_package "$version"
    generate_info "$version"
    
    echo ""
    log_success "🎉 Deployment package ready!"
    echo ""
    log_info "Next steps:"
    echo "1. Test the package locally"
    echo "2. Upload to your WordPress installation"
    echo "3. Or use with CI/CD pipeline"
    echo ""
}

# Run main function
main "$@"