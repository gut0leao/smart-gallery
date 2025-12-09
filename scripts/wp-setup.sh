#!/bin/bash

# Smart Gallery - WordPress Setup Script
# This script configures WordPress from scratch with required plugins

# Enable strict error handling
set -e
set -o pipefail

echo "🚀 Smart Gallery - WordPress Setup"
echo "========================================"

# Check if we're in DDEV
if ! command -v ddev &> /dev/null; then
    echo "❌ Error: DDEV not found. Please run this script from a DDEV environment."
    exit 1
fi

# Set explicit WordPress path
WP_PATH="/var/www/html"

# Check if WordPress is accessible
if ! ddev exec wp core is-installed --path="$WP_PATH" --quiet 2>/dev/null; then
    echo "ℹ️ WordPress not installed. Installing WordPress first..."
    
    # First, download WordPress (using curl directly to bypass SSL issues)
    echo "📥 Downloading WordPress core files..."
    ddev exec bash -c "cd '$WP_PATH' && curl -skL https://wordpress.org/latest.tar.gz | tar xz --strip-components=1"
    
    echo "🔧 Creating wp-config.php..."
    ddev exec wp config create --path="$WP_PATH" \
        --dbname="db" \
        --dbuser="db" \
        --dbpass="db" \
        --dbhost="db" \
        --force
    
    echo "📦 Installing WordPress..."
    ddev exec wp core install --path="$WP_PATH" \
        --url="https://smart-gallery.ddev.site" \
        --title="Smart Gallery Demo" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="admin@example.com"
else
    echo "⚠️  WORDPRESS IS ALREADY INSTALLED!"
    echo ""
    echo "🚨 ATTENTION: This script will perform a COMPLETE installation from scratch:"
    echo "   • All WordPress data will be LOST"
    echo "   • Database will be RECREATED"
    echo "   • Posts, pages, users, settings will be DELETED"
    echo "   • Configuration files will be OVERWRITTEN"
    echo ""
    echo "💡 If you only want to update plugins/themes without losing data, cancel and:"
    echo "   • Use individual commands: ddev exec wp plugin install [plugin]"
    echo "   • Or use wp-admin for manual installations"
    echo ""
    echo "⚠️  This action is IRREVERSIBLE!"
    echo ""
    
    if [[ "$AUTO_CONFIRM" == "true" ]]; then
        echo "🤖 Auto-confirmation enabled. Proceeding with reinstallation..."
        REPLY="y"
    else
        read -p "Are you sure you want to REINSTALL WordPress from scratch? (y/N): " -n 1 -r
        echo
    fi
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "😌 Cancelled. Your WordPress installation remains intact."
        exit 0
    fi
    
    echo "🗑️ Removing existing WordPress..."
    ddev exec wp db reset --path="$WP_PATH" --yes
    
    echo "📥 Downloading WordPress core files..."
    ddev exec bash -c "cd '$WP_PATH' && curl -skL https://wordpress.org/latest.tar.gz | tar xz --strip-components=1"
    
    echo "🔧 Creating wp-config.php..."
    ddev exec wp config create --path="$WP_PATH" \
        --dbname="db" \
        --dbuser="db" \
        --dbpass="db" \
        --dbhost="db" \
        --force
    
    echo "📦 Reinstalling WordPress..."
    ddev exec wp core install --path="$WP_PATH" \
        --url="https://smart-gallery.ddev.site" \
        --title="Smart Gallery Demo" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="admin@example.com"
fi

echo ""
echo "🧹 Removing default WordPress plugins..."

# Remove Akismet Anti-spam
echo "   🗑️ Removing Akismet Anti-spam..."
ddev exec wp plugin delete akismet --path="$WP_PATH" --quiet 2>/dev/null || true

# Remove Hello Dolly
echo "   🗑️ Removing Hello Dolly..."
ddev exec wp plugin delete hello --path="$WP_PATH" --quiet 2>/dev/null || true

echo ""
echo "🔌 Installing required plugins..."

# Install Elementor
echo "   📦 Installing Elementor..."
ddev exec rm -rf /var/www/html/wp-content/plugins/elementor 2>/dev/null || true
ddev exec bash -c "cd '$WP_PATH/wp-content/plugins' && curl -skL https://downloads.wordpress.org/plugin/elementor.latest-stable.zip -o elementor.zip && unzip -q elementor.zip && rm elementor.zip"
ddev exec wp plugin activate elementor --path="$WP_PATH"

# Install and activate Hello Elementor theme (Elementor's default theme)
echo "   🎨 Installing Hello Elementor theme..."
ddev exec bash -c "cd '$WP_PATH/wp-content/themes' && curl -skL https://downloads.wordpress.org/theme/hello-elementor.latest-stable.zip -o hello-elementor.zip && unzip -q hello-elementor.zip && rm hello-elementor.zip"
ddev exec wp theme activate hello-elementor --path="$WP_PATH"

# Install Pods
echo "   📦 Installing Pods Framework..."
ddev exec rm -rf /var/www/html/wp-content/plugins/pods 2>/dev/null || true
ddev exec bash -c "cd '$WP_PATH/wp-content/plugins' && curl -skL https://downloads.wordpress.org/plugin/pods.latest-stable.zip -o pods.zip && unzip -q pods.zip && rm pods.zip"
ddev exec wp plugin activate pods --path="$WP_PATH"

# Activate main plugin
echo "   🎯 Activating Smart Gallery..."
ddev exec wp plugin activate smart-gallery --path="$WP_PATH"

echo ""
echo "🔧 Configuring HTTPS with mkcert..."
if command -v mkcert &> /dev/null; then
    # Create ssl-certs directory if it doesn't exist
    mkdir -p ssl-certs
    
    # Generate certificates in the ssl-certs directory
    cd ssl-certs
    mkcert -install
    mkcert smart-gallery.ddev.site
    cd ..
    
    echo "   ✅ SSL certificates created in ssl-certs/ directory"
else
    echo "   ⚠️ mkcert not found. Install for automatic HTTPS:"
    echo "      https://github.com/FiloSottile/mkcert"
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "🌐 Access your site at: https://smart-gallery.ddev.site"
echo "🔑 Admin: https://smart-gallery.ddev.site/wp-admin"
echo "   User: admin"
echo "   Pass: admin"
echo ""
echo "📋 Next steps:"
echo "1. Run: ./scripts/pods-import.sh (to import demo data)"
echo "2. Configure your Elementor widget"
echo "3. Test the functionalities"
