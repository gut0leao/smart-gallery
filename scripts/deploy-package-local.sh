#!/bin/bash

# 🚀 Smart Gallery Plugin - Local Package Deploy Script
# This script deploys the plugin package to the local DDEV WordPress installation
# 
# What it does:
#   - Builds and validates the plugin package
#   - Installs/updates the plugin in local DDEV WordPress
#   - Activates the plugin
#   - Clears caches
#
# Usage:
#   ./scripts/deploy-package-local.sh  # Deploy to local DDEV
#
# Requirements:
#   - DDEV running with WordPress installed
#   - Smart Gallery plugin source code

set -e

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
    
    if ! command -v ddev &> /dev/null; then
        log_error "DDEV not found. Please install DDEV first."
        exit 1
    fi
    
    if ! ddev status > /dev/null 2>&1; then
        log_error "DDEV not running. Please run 'ddev start' first."
        exit 1
    fi
    
    if ! ddev exec wp core is-installed --quiet 2>/dev/null; then
        log_error "WordPress not installed in DDEV. Please run './scripts/wp-setup.sh' first."
        exit 1
    fi
    
    if [ ! -d "wp-content/plugins/smart-gallery" ]; then
        log_error "Smart Gallery plugin source not found at wp-content/plugins/smart-gallery"
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

# Show environment info
show_environment_info() {
    log_info "Environment Information:"
    echo "🐳 DDEV Project: $(ddev describe | grep "Project:" | sed 's/.*Project: \([^ ]*\).*/\1/')"
    echo "🌐 Site URL: $(ddev describe | grep "https://.*\.ddev\.site" | head -1 | sed 's/.*\(https:\/\/[^,]*\.ddev\.site\).*/\1/')"
    echo "🐘 PHP Version: $(ddev exec php -v | head -1 | cut -d' ' -f2)"
    echo "📦 WordPress: $(ddev exec wp core version)"
    echo ""
}

# Validate plugin code
validate_plugin() {
    log_info "Validating plugin code..."
    
    # PHP syntax check
    log_info "Checking PHP syntax..."
    find wp-content/plugins/smart-gallery -name "*.php" -print0 | while IFS= read -r -d '' file; do
        if ! ddev exec php -l "$file" > /dev/null 2>&1; then
            log_error "PHP syntax error in: $file"
            ddev exec php -l "$file"
            exit 1
        fi
    done
    
    # Check for required files
    required_files=(
        "wp-content/plugins/smart-gallery/smart-gallery.php"
        "wp-content/plugins/smart-gallery/includes/"
        "wp-content/plugins/smart-gallery/assets/"
    )
    
    for file in "${required_files[@]}"; do
        if [[ ! -e "$file" ]]; then
            log_error "Missing required file/directory: $file"
            exit 1
        fi
    done
    
    log_success "Plugin validation passed"
}

# Deploy plugin to DDEV WordPress
deploy_plugin() {
    log_info "Deploying Smart Gallery plugin to local WordPress..."
    
    # Check if plugin is currently active
    if ddev exec wp plugin is-active smart-gallery --quiet 2>/dev/null; then
        WAS_ACTIVE=true
        log_info "Plugin is currently active - will reactivate after deployment"
    else
        WAS_ACTIVE=false
        log_info "Plugin is currently inactive"
    fi
    
    # The plugin files are already in place (wp-content/plugins/smart-gallery)
    # We just need to ensure proper permissions and activation
    
    log_info "Setting proper file permissions..."
    # In DDEV, files are already properly owned, just ensure correct chmod
    ddev exec chmod -R 755 /var/www/html/wp-content/plugins/smart-gallery 2>/dev/null || log_warning "Could not set permissions, but continuing..."
    
    # Activate or reactivate plugin
    if [ "$WAS_ACTIVE" = true ]; then
        log_info "Reactivating plugin..."
        ddev exec wp plugin deactivate smart-gallery --quiet
        ddev exec wp plugin activate smart-gallery
    else
        log_info "Activating plugin..."
        ddev exec wp plugin activate smart-gallery
    fi
    
    log_success "Plugin deployed and activated"
}

# Clear caches
clear_caches() {
    log_info "Clearing caches..."
    
    # WordPress object cache
    ddev exec wp cache flush --quiet 2>/dev/null || log_info "WordPress cache flush attempted"
    
    # Elementor cache (if available)
    if ddev exec wp plugin is-active elementor --quiet 2>/dev/null; then
        ddev exec wp elementor flush_css --quiet 2>/dev/null || log_info "Elementor CSS cache flush attempted"
    fi
    
    log_success "Caches cleared"
}

# Verify deployment
verify_deployment() {
    log_info "Verifying deployment..."
    
    # Check if plugin is active
    if ddev exec wp plugin is-active smart-gallery --quiet; then
        log_success "Smart Gallery plugin is active"
    else
        log_error "Plugin activation failed"
        exit 1
    fi
    
    # Check plugin version
    VERSION=$(ddev exec wp plugin get smart-gallery --field=version 2>/dev/null || echo "Unknown")
    log_info "Plugin version: $VERSION"
    
    # Check if site is accessible
    SITE_URL=$(ddev describe | grep "https://.*\.ddev\.site" | head -1 | sed 's/.*\(https:\/\/[^,]*\.ddev\.site\).*/\1/')
    if curl -sSf "$SITE_URL" > /dev/null 2>&1; then
        log_success "Site is accessible at $SITE_URL"
    else
        log_warning "Site accessibility check failed"
    fi
    
    log_success "Deployment verification completed"
}

# Show next steps
show_next_steps() {
    SITE_URL=$(ddev describe | grep "https://.*\.ddev\.site" | head -1 | sed 's/.*\(https:\/\/[^,]*\.ddev\.site\).*/\1/')
    ADMIN_URL="${SITE_URL}/wp-admin"
    
    echo ""
    log_success "🎉 Local deployment completed successfully!"
    echo ""
    log_info "Next steps:"
    echo "1. 🌐 Visit your site: $SITE_URL"
    echo "2. 🔧 Configure in admin: $ADMIN_URL"
    echo "3. 🎨 Add Smart Gallery widget in Elementor"
    echo "4. 🧪 Test the filtering functionality"
    echo ""
    log_info "Quick access:"
    echo "• Admin login: admin / admin"
    echo "• Plugin settings: wp-admin → Plugins → Smart Gallery"
    echo "• Elementor editor: Edit any page with Elementor"
    echo ""
}

# Main execution
main() {
    echo ""
    log_info "🚀 Smart Gallery Plugin - Local Package Deploy Script"
    echo ""
    
    check_prerequisites
    show_environment_info
    validate_plugin
    deploy_plugin
    clear_caches
    verify_deployment
    show_next_steps
}

# Run main function
main "$@"