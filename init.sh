#!/bin/bash

# Smart Gallery Filter - Complete Environment Initialization
# This script automates the complete development environment initialization

echo "🚀 Smart Gallery Filter - Complete Initialization"
echo "================================================="

# Colors for output
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}Welcome! This script will set up your complete development environment:${NC}"
echo ""
echo -e "${GREEN}📋 What will be created:${NC}"
echo "1. 🧹 Clean development environment (removes any existing setup)"
echo "2. 🐳 Fresh DDEV Docker containers"
echo "3. 🔧 WordPress installation with required plugins"
echo "4. 🗄️ Database preparation for demo data"
echo "5. 📦 Import 196 cars + 5 dealerships with images"
echo ""
echo -e "${BLUE}💡 Perfect for:${NC}"
echo "   • First-time setup"
echo "   • Getting a clean development environment"
echo "   • Resetting to factory defaults"
echo ""
echo -e "${YELLOW}⚠️ Important: This will replace any existing local WordPress data${NC}"
echo -e "${YELLOW}   (Your source code and configuration files are safe)${NC}"
echo ""

read -p "Ready to initialize your Smart Gallery Filter environment? (Y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]] && [[ ! -z $REPLY ]]; then
    echo -e "${GREEN}😌 Setup cancelled. You can run this script anytime!${NC}"
    exit 0
fi

echo ""
echo -e "${RED}🔥 STARTING COMPLETE INITIALIZATION...${NC}"
echo ""

# Function to check if command succeeded
check_success() {
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Error in step: $1${NC}"
        echo -e "${YELLOW}💡 Check the logs above for more details.${NC}"
        exit 1
    fi
}

# Step 1: Environment preparation
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 1/5: 🧹 ENVIRONMENT PREPARATION${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

if [ -f "./scripts/nuke.sh" ]; then
    CALLED_FROM_INIT=true ./scripts/nuke.sh
    check_success "Environment preparation"
else
    echo -e "${YELLOW}⚠️ Cleanup script not found. Skipping preparation.${NC}"
fi

echo ""
echo -e "${GREEN}✅ Environment prepared!${NC}"

# Step 2: Initialize DDEV
echo ""
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 2/5: 🐳 DDEV INITIALIZATION${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

echo "🚀 Starting DDEV..."
ddev start
check_success "DDEV initialization"

echo -e "${GREEN}✅ DDEV started successfully!${NC}"

# Step 3: WordPress setup
echo ""
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 3/5: 🔧 WORDPRESS CONFIGURATION${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

if [ -f "./scripts/wp-setup.sh" ]; then
    # Auto-confirm WordPress installation
    export AUTO_CONFIRM=true
    ./scripts/wp-setup.sh
    check_success "WordPress configuration"
else
    echo -e "${RED}❌ Script wp-setup.sh not found!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ WordPress configured successfully!${NC}"

# Step 4: Reset Pods data
echo ""
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 4/5: 🧹 PODS DATA RESET${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

if [ -f "./scripts/pods-reset.sh" ]; then
    # Auto-execute pods reset (with force flag if available)
    export PODS_EXECUTE_RESET=true
    echo "🧹 Executing Pods data reset..."
    ./scripts/pods-reset.sh
    check_success "Pods data reset"
else
    echo -e "${YELLOW}⚠️ Script pods-reset.sh not found. Skipping reset.${NC}"
fi

echo -e "${GREEN}✅ Pods data reset successfully!${NC}"

# Step 5: Import demo data
echo ""
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 5/5: 📦 DEMO DATA IMPORT${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

if [ -f "./scripts/pods-import.sh" ]; then
    ./scripts/pods-import.sh
    check_success "Demo data import"
else
    echo -e "${RED}❌ Script pods-import.sh not found!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Demo data imported successfully!${NC}"

# Final status
echo ""
echo -e "${GREEN}🎉 COMPLETE INITIALIZATION FINISHED!${NC}"
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${CYAN}📋 INSTALLATION SUMMARY:${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ DDEV:${NC} Docker environment initialized"
echo -e "${GREEN}✅ WordPress:${NC} Installed with plugins (Elementor + Pods)"
echo -e "${GREEN}✅ Pods Reset:${NC} Data cleaned and prepared"
echo -e "${GREEN}✅ Demo Data:${NC} 196 cars + 5 dealerships imported"
echo -e "${GREEN}✅ SSL:${NC} HTTPS certificates configured"
echo ""
echo -e "${CYAN}🌐 SYSTEM ACCESS:${NC}"
echo -e "${BLUE}🏠 Site:${NC} https://smart-gallery-filter.ddev.site"
echo -e "${BLUE}🔑 Admin:${NC} https://smart-gallery-filter.ddev.site/wp-admin"
echo -e "${BLUE}👤 User:${NC} admin"
echo -e "${BLUE}🔒 Password:${NC} admin"
echo -e "${BLUE}🗄️ phpMyAdmin:${NC} ddev phpmyadmin"
echo ""
echo -e "${CYAN}📋 NEXT STEPS:${NC}"
echo "1. Access WordPress Admin to configure Elementor"
echo "2. Create a page with Smart Gallery Filter widget"
echo "3. Configure filters as needed"
echo ""
echo -e "${GREEN}🎯 Environment ready for development!${NC}"
