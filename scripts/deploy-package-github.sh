#!/bin/bash

# 🚀 Smart Gallery Plugin - GitHub Package Deploy Script  
# This script builds and publishes the plugin package to GitHub Packages
# 
# What it does:
#   - Builds plugin package using package.sh
#   - Authenticates with GitHub Packages
#   - Uploads package to GitHub Container Registry
#   - Creates/updates package metadata
#
# Usage:
#   ./scripts/deploy-package-github.sh [version]  # Deploy with specific version
#   ./scripts/deploy-package-github.sh            # Auto-detect version from git
#
# Requirements:
#   - GitHub CLI (gh) installed and authenticated
#   - Docker installed (for container registry)
#   - GITHUB_TOKEN environment variable (or gh auth)

set -e

# Configuration
PLUGIN_NAME="smart-gallery"
GITHUB_OWNER="gut0leao"  # Change to your GitHub username
GITHUB_REPO="smart-gallery"
REGISTRY="ghcr.io"

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
    
    # Check GitHub CLI
    if ! command -v gh &> /dev/null; then
        log_error "GitHub CLI (gh) not found. Please install it first:"
        echo "https://cli.github.com/"
        exit 1
    fi
    
    # Check GitHub authentication
    if ! gh auth status &> /dev/null; then
        log_error "GitHub CLI not authenticated. Please run 'gh auth login' first."
        exit 1
    fi
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker not found. Please install Docker first."
        exit 1
    fi
    
    # Check if package.sh exists
    if [ ! -f "scripts/package.sh" ]; then
        log_error "Package script not found at scripts/package.sh"
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

# Determine version
determine_version() {
    local version="$1"
    
    if [ -n "$version" ]; then
        echo "$version"
        return
    fi
    
    # Try to get version from git tag
    if git describe --tags --exact-match HEAD &> /dev/null; then
        version=$(git describe --tags --exact-match HEAD)
        log_info "Using git tag version: $version"
        echo "$version"
        return
    fi
    
    # Try to get version from plugin file
    if [ -f "wp-content/plugins/smart-gallery/smart-gallery.php" ]; then
        version=$(grep "Version:" wp-content/plugins/smart-gallery/smart-gallery.php | head -1 | sed 's/.*Version: *//' | sed 's/ *\*\/.*//')
        if [ -n "$version" ]; then
            # Add commit hash for non-tag versions
            COMMIT_SHORT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
            version="${version}-${COMMIT_SHORT}"
            log_info "Using plugin file version with commit: $version"
            echo "$version"
            return
        fi
    fi
    
    # Fallback version
    TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
    COMMIT_SHORT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
    version="1.0.0-dev-${TIMESTAMP}-${COMMIT_SHORT}"
    log_warning "Version not determined, using fallback: $version"
    echo "$version"
}

# Build plugin package
build_package() {
    log_info "Building plugin package..."
    
    # Run package script
    if ! ./scripts/package.sh; then
        log_error "Package build failed"
        exit 1
    fi
    
    log_success "Package build completed"
}

# Create container image for GitHub Packages
create_container_image() {
    local version="$1"
    # Remove 'v' prefix if present for package name
    local clean_version="${version#v}"
    local package_name="${PLUGIN_NAME}-${clean_version}"
    
    log_info "Creating container image for GitHub Packages..."
    
    # Create temporary Dockerfile
    cat > Dockerfile.temp << EOF
FROM scratch
COPY dist/builds/${package_name}.zip /plugin.zip
COPY dist/builds/${package_name}.zip.sha256 /plugin.zip.sha256
COPY dist/builds/${package_name}.zip.md5 /plugin.zip.md5
COPY dist/builds/${package_name}.info /plugin.info
LABEL org.opencontainers.image.title="Smart Gallery WordPress Plugin"
LABEL org.opencontainers.image.description="A smart WordPress plugin for filterable galleries with Elementor integration"
LABEL org.opencontainers.image.version="${version}"
LABEL org.opencontainers.image.source="https://github.com/${GITHUB_OWNER}/${GITHUB_REPO}"
LABEL org.opencontainers.image.url="https://github.com/${GITHUB_OWNER}/${GITHUB_REPO}"
LABEL org.opencontainers.image.vendor="${GITHUB_OWNER}"
LABEL org.opencontainers.image.licenses="GPL-2.0+"
EOF
    
    # Build container image
    local image_name="${REGISTRY}/${GITHUB_OWNER}/${PLUGIN_NAME}:${version}"
    local image_latest="${REGISTRY}/${GITHUB_OWNER}/${PLUGIN_NAME}:latest"
    
    log_info "Building container image: $image_name"
    docker build -f Dockerfile.temp -t "$image_name" -t "$image_latest" . > /dev/null 2>&1
    
    # Clean up
    rm -f Dockerfile.temp
    
    log_success "Container image created"
}

# Authenticate with GitHub Container Registry
authenticate_registry() {
    log_info "Authenticating with GitHub Container Registry..."
    
    # Check if GITHUB_TOKEN is set
    if [ -z "$GITHUB_TOKEN" ]; then
        # Try to read token from gh config
        GITHUB_TOKEN=$(cat ~/.config/gh/hosts.yml | grep oauth_token | head -1 | cut -d: -f2 | tr -d ' ')
        
        if [ -z "$GITHUB_TOKEN" ]; then
            log_error "GITHUB_TOKEN not found. Please set GITHUB_TOKEN environment variable."
            log_error "You can get a token from: https://github.com/settings/tokens"
            exit 1
        fi
    fi
    
    # Login to container registry
    echo "$GITHUB_TOKEN" | docker login "$REGISTRY" -u "$GITHUB_OWNER" --password-stdin
    
    log_success "Registry authentication completed"
}

# Push to GitHub Packages
push_to_packages() {
    local image_name="$1"
    local version="$2"
    
    log_info "Pushing to GitHub Packages..."
    log_info "Image name: '$image_name'"
    log_info "Version: '$version'"
    
    # Push versioned image
    docker push "$image_name"
    
    # Push latest tag (only for release versions)
    if [[ "$version" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        local image_latest="${REGISTRY}/${GITHUB_OWNER}/${PLUGIN_NAME}:latest"
        docker push "$image_latest"
        log_info "Pushed latest tag for release version"
    fi
    
    log_success "Package pushed to GitHub Packages"
}

# Create GitHub release (for tagged versions)
create_github_release() {
    local version="$1"
    # Remove 'v' prefix if present for package name
    local clean_version="${version#v}"
    local package_name="${PLUGIN_NAME}-${clean_version}"
    
    # Only create release for proper version tags
    if [[ "$version" =~ ^v[0-9]+\.[0-9]+\.[0-9]+ ]]; then
        log_info "Creating GitHub release for version $version"
        
        # Create release notes
        cat > release_notes.md << EOF
## 🚀 Smart Gallery Plugin $version

### 📦 What's Included
- Complete Smart Gallery WordPress plugin
- Elementor widget integration  
- Advanced search and filtering system
- Taxonomy and custom fields support
- SVG icon system with professional UI

### 🎯 Features
- ✅ Text search with manual submission
- ✅ Custom fields filtering with dynamic loading
- ✅ Taxonomy filtering with hierarchical support
- ✅ Responsive design with hover effects
- ✅ URL state persistence
- ✅ Debug status panel

### 📥 Installation Options

#### Option 1: Download ZIP
1. Download the \`${package_name}.zip\` file below
2. Upload to WordPress: Plugins > Add New > Upload
3. Activate the plugin
4. Add Smart Gallery widget in Elementor

#### Option 2: GitHub Packages (Docker)
\`\`\`bash
# Pull from GitHub Container Registry
docker pull ${REGISTRY}/${GITHUB_OWNER}/${PLUGIN_NAME}:${version}

# Extract plugin files
docker create --name temp-container ${REGISTRY}/${GITHUB_OWNER}/${PLUGIN_NAME}:${version}
docker cp temp-container:/plugin.zip ./smart-gallery.zip
docker rm temp-container
\`\`\`

### 🔗 Links
- [Documentation](https://github.com/${GITHUB_OWNER}/${GITHUB_REPO}/blob/main/README.md)
- [Requirements](https://github.com/${GITHUB_OWNER}/${GITHUB_REPO}/blob/main/docs/requirements.md)
- [GitHub Packages](https://github.com/${GITHUB_OWNER}/${GITHUB_REPO}/pkgs/container/${PLUGIN_NAME})
EOF
        
        # Create release
        gh release create "$version" \
            "${package_name}.zip" \
            "${package_name}.zip.sha256" \
            "${package_name}.zip.md5" \
            "${package_name}.info" \
            --title "Smart Gallery Plugin $version" \
            --notes-file release_notes.md
        
        # Clean up
        rm -f release_notes.md
        
        log_success "GitHub release created: $version"
    else
        log_info "Skipping GitHub release for development version: $version"
    fi
}

# Show deployment info
show_deployment_info() {
    local version="$1"
    local image_name="$2"
    
    echo ""
    log_success "🎉 GitHub Packages deployment completed!"
    echo ""
    log_info "Package Information:"
    echo "📦 Plugin: Smart Gallery $version"
    echo "🐳 Container: $image_name"
    echo "📊 Registry: $REGISTRY"
    echo ""
    log_info "Access your package:"
    echo "🌐 Packages: https://github.com/$GITHUB_OWNER/$GITHUB_REPO/pkgs/container/$PLUGIN_NAME"
    echo "📋 Releases: https://github.com/$GITHUB_OWNER/$GITHUB_REPO/releases"
    echo ""
    log_info "Usage examples:"
    echo "# Docker pull:"
    echo "docker pull $image_name"
    echo ""
    echo "# Extract plugin:"
    echo "docker create --name temp $image_name"
    echo "docker cp temp:/plugin.zip ./smart-gallery.zip"
    echo "docker rm temp"
    echo ""
}

# Clean up artifacts
cleanup() {
    log_info "Cleaning up local artifacts..."
    
    # Remove Docker images (optional)
    if [ "${CLEANUP_IMAGES:-}" = "true" ]; then
        docker rmi "$image_name" "$image_latest" 2>/dev/null || true
        log_info "Local Docker images removed"
    fi
    
    # Keep local ZIP files for manual use
    log_info "Local package files retained for manual use"
}

# Main execution
main() {
    local version="$1"
    
    echo ""
    log_info "🚀 Smart Gallery Plugin - GitHub Package Deploy"
    echo ""
    
    check_prerequisites
    
    version=$(determine_version "$version")
    log_info "Deploying version: $version"
    
    build_package
    
    create_container_image "$version"
    image_name="${REGISTRY}/${GITHUB_OWNER}/${PLUGIN_NAME}:${version}"
    
    authenticate_registry
    
    push_to_packages "$image_name" "$version"
    
    create_github_release "$version"
    
    show_deployment_info "$version" "$image_name"
    
    cleanup
}

# Handle script arguments
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    echo "Usage: $0 [version]"
    echo ""
    echo "Deploy Smart Gallery plugin to GitHub Packages"
    echo ""
    echo "Arguments:"
    echo "  version    Optional version string (e.g., v1.0.0)"
    echo "             If not provided, version will be auto-detected"
    echo ""
    echo "Environment variables:"
    echo "  CLEANUP_IMAGES=true    Remove local Docker images after push"
    echo ""
    echo "Examples:"
    echo "  $0                     # Auto-detect version"
    echo "  $0 v1.0.0             # Specific version"
    echo "  CLEANUP_IMAGES=true $0 # Clean up after deployment"
    exit 0
fi

# Run main function
main "$@"