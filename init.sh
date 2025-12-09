#!/bin/bash

# Smart Gallery - Complete Environment Initialization
# This script automates the complete development environment initialization

echo "🚀 Smart Gallery - Complete Initialization"
echo "================================================="

# Colors for output
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to check prerequisites
check_prerequisites() {
    echo -e "${BLUE}🔍 Checking system prerequisites...${NC}"
    echo ""
    
    local prerequisites_ok=true
    
    # Check if Docker is installed and running
    echo -n "   🐳 Docker: "
    if command -v docker &> /dev/null; then
        if docker info &> /dev/null; then
            echo -e "${GREEN}✅ Running${NC}"
        else
            echo -e "${RED}❌ Installed but not running${NC}"
            echo -e "      ${YELLOW}💡 Please start Docker service${NC}"
            prerequisites_ok=false
        fi
    else
        echo -e "${RED}❌ Not installed${NC}"
        echo -e "      ${YELLOW}💡 Install Docker: https://docs.docker.com/get-docker/${NC}"
        prerequisites_ok=false
    fi
    
    # Check if DDEV is installed
    echo -n "   🛠️  DDEV: "
    if command -v ddev &> /dev/null; then
        local ddev_version=$(ddev version | head -n 1 | awk '{print $3}')
        echo -e "${GREEN}✅ Installed (${ddev_version})${NC}"
    else
        echo -e "${RED}❌ Not installed${NC}"
        echo -e "      ${YELLOW}💡 Install DDEV: https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/${NC}"
        prerequisites_ok=false
    fi
    
    # Check available disk space (minimum 2GB recommended)
    echo -n "   💽 Disk Space: "
    local available_gb=$(df . | tail -1 | awk '{print int($4/1024/1024)}')
    if [ "$available_gb" -ge 2 ]; then
        echo -e "${GREEN}✅ ${available_gb}GB available${NC}"
    else
        echo -e "${YELLOW}⚠️  Only ${available_gb}GB available${NC}"
        echo -e "      ${YELLOW}💡 Recommend at least 2GB free space${NC}"
    fi
    
    # Check if port 443 and 80 are available
    echo -n "   🌐 Ports: "
    local ports_ok=true
    
    if netstat -tuln 2>/dev/null | grep -q ':80 '; then
        echo -e "${YELLOW}⚠️  Port 80 in use${NC}"
        ports_ok=false
    elif netstat -tuln 2>/dev/null | grep -q ':443 '; then
        echo -e "${YELLOW}⚠️  Port 443 in use${NC}"
        ports_ok=false
    fi
    
    if [ "$ports_ok" = true ]; then
        echo -e "${GREEN}✅ Available (80, 443)${NC}"
    else
        echo -e "      ${YELLOW}💡 DDEV may handle port conflicts automatically${NC}"
    fi
    
    # Check if zip utility is installed (needed for packaging scripts)
    echo -n "   📦 Zip Utility: "
    if command -v zip &> /dev/null; then
        echo -e "${GREEN}✅ Installed${NC}"
    else
        echo -e "${RED}❌ Not installed${NC}"
        echo -e "      ${YELLOW}💡 Installing zip utility automatically...${NC}"
        
        # Auto-install zip based on the system
        if command -v apt-get &> /dev/null; then
            echo "      📥 Installing via apt-get..."
            if sudo apt-get update &> /dev/null && sudo apt-get install -y zip &> /dev/null; then
                echo -e "      ${GREEN}✅ Zip installed successfully${NC}"
            else
                echo -e "${RED}❌ Failed to install zip automatically${NC}"
                echo -e "      ${YELLOW}💡 Please install manually: sudo apt-get install zip${NC}"
                prerequisites_ok=false
            fi
        elif command -v yum &> /dev/null; then
            echo "      📥 Installing via yum..."
            if sudo yum install -y zip &> /dev/null; then
                echo -e "      ${GREEN}✅ Zip installed successfully${NC}"
            else
                echo -e "${RED}❌ Failed to install zip automatically${NC}"
                echo -e "      ${YELLOW}💡 Please install manually: sudo yum install zip${NC}"
                prerequisites_ok=false
            fi
        elif command -v brew &> /dev/null; then
            echo "      📥 Installing via brew..."
            if brew install zip &> /dev/null; then
                echo -e "      ${GREEN}✅ Zip installed successfully${NC}"
            else
                echo -e "${RED}❌ Failed to install zip automatically${NC}"
                echo -e "      ${YELLOW}💡 Please install manually: brew install zip${NC}"
                prerequisites_ok=false
            fi
        else
            echo -e "${RED}❌ Cannot auto-install (unknown package manager)${NC}"
            echo -e "      ${YELLOW}💡 Please install zip manually for your system${NC}"
            prerequisites_ok=false
        fi
    fi

    # Check if project directory structure is correct
    echo -n "   📁 Project Structure: "
    if [ -f "./scripts/nuke.sh" ] && [ -f "./scripts/wp-setup.sh" ] && [ -f "./scripts/pods-import.sh" ]; then
        echo -e "${GREEN}✅ Complete${NC}"
    else
        echo -e "${YELLOW}⚠️  Some scripts missing${NC}"
        echo -e "      ${YELLOW}💡 Some features may not work correctly${NC}"
    fi
    
    echo ""
    
    if [ "$prerequisites_ok" = false ]; then
        echo -e "${RED}❌ Prerequisites check failed!${NC}"
        echo -e "${YELLOW}Please resolve the issues above before continuing.${NC}"
        echo ""
        exit 1
    else
        echo -e "${GREEN}✅ All prerequisites satisfied!${NC}"
        echo ""
    fi
}

# Run prerequisites check
check_prerequisites

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

# Enable strict error handling
set -e  # Exit immediately if any command fails
set -o pipefail  # Exit if any command in a pipeline fails

# Function to check if command succeeded
check_success() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo -e "${RED}❌ Error in step: $1${NC}"
        echo -e "${RED}❌ Exit code: $exit_code${NC}"
        echo -e "${YELLOW}💡 Check the logs above for more details.${NC}"
        echo ""
        echo -e "${CYAN}🆘 TROUBLESHOOTING TIPS:${NC}"
        case "$1" in
            "Environment preparation")
                echo "   • Check if Docker containers are running: docker ps"
                echo "   • Try: ddev poweroff && ddev start"
                echo "   • Install zip if missing: sudo apt-get install zip"
                ;;
            "DDEV initialization")
                echo "   • Check Docker service: sudo systemctl status docker"
                echo "   • Check ports: netstat -tuln | grep -E ':80|:443'"
                echo "   • Try: ddev poweroff && ddev start"
                ;;
            "WordPress configuration")
                echo "   • Check wp-setup.sh script exists and is executable"
                echo "   • Verify DDEV is running: ddev status"
                echo "   • Check for errors in wp-setup.sh output above"
                ;;
            "Demo data import")
                echo "   • Check pods-import.sh script exists"
                echo "   • Verify WordPress is accessible: ddev status"
                ;;
        esac
        echo ""
        exit 1
    fi
}

# Function to show progress and time estimation
show_progress() {
    local step=$1
    local total_steps=5
    local percentage=$((step * 100 / total_steps))
    
    echo -e "${CYAN}📊 Progress: ${percentage}% (Step ${step}/${total_steps})${NC}"
}

# Function to estimate time for each step
estimate_time() {
    local step=$1
    case "$step" in
        1) echo "~2-3 minutes" ;;
        2) echo "~1-2 minutes" ;;
        3) echo "~3-4 minutes" ;;
        4) echo "~30 seconds" ;;
        5) echo "~2-3 minutes" ;;
    esac
}

echo ""
echo -e "${CYAN}⏱️  Estimated total time: ~8-12 minutes${NC}"
echo ""

read -p "Ready to initialize your Smart Gallery environment? (Y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]] && [[ ! -z $REPLY ]]; then
    echo -e "${GREEN}😌 Setup cancelled. You can run this script anytime!${NC}"
    exit 0
fi

echo ""
echo -e "${RED}🔥 STARTING COMPLETE INITIALIZATION...${NC}"
echo ""

# Record start time
START_TIME=$(date +%s)

# Step 1: Environment preparation
show_progress 1
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 1/5: 🧹 ENVIRONMENT PREPARATION${NC}"
echo -e "${BLUE}   Estimated time: $(estimate_time 1)${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

STEP_START=$(date +%s)

if [ -f "./scripts/nuke.sh" ]; then
    CALLED_FROM_INIT=true ./scripts/nuke.sh
    check_success "Environment preparation"
else
    echo -e "${YELLOW}⚠️ Cleanup script not found. Skipping preparation.${NC}"
fi

STEP_END=$(date +%s)
STEP_TIME=$((STEP_END - STEP_START))
echo ""
echo -e "${GREEN}✅ Environment prepared! (${STEP_TIME}s)${NC}"

# Step 2: Initialize DDEV
echo ""
show_progress 2
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 2/5: 🐳 DDEV INITIALIZATION${NC}"
echo -e "${BLUE}   Estimated time: $(estimate_time 2)${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

STEP_START=$(date +%s)

echo "🚀 Starting DDEV..."
ddev start
check_success "DDEV initialization"

STEP_END=$(date +%s)
STEP_TIME=$((STEP_END - STEP_START))
echo -e "${GREEN}✅ DDEV started successfully! (${STEP_TIME}s)${NC}"

# Step 3: WordPress setup
echo ""
show_progress 3
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 3/5: 🔧 WORDPRESS CONFIGURATION${NC}"
echo -e "${BLUE}   Estimated time: $(estimate_time 3)${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

STEP_START=$(date +%s)

if [ -f "./scripts/wp-setup.sh" ]; then
    # Auto-confirm WordPress installation
    export AUTO_CONFIRM=true
    ./scripts/wp-setup.sh
    check_success "WordPress configuration"
else
    echo -e "${RED}❌ Script wp-setup.sh not found!${NC}"
    exit 1
fi

STEP_END=$(date +%s)
STEP_TIME=$((STEP_END - STEP_START))
echo -e "${GREEN}✅ WordPress configured successfully! (${STEP_TIME}s)${NC}"

# Step 4: Reset Pods data
echo ""
show_progress 4
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 4/5: 🧹 PODS DATA RESET${NC}"
echo -e "${BLUE}   Estimated time: $(estimate_time 4)${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

STEP_START=$(date +%s)

if [ -f "./scripts/pods-reset.sh" ]; then
    # Auto-execute pods reset (with force flag if available)
    export PODS_EXECUTE_RESET=true
    echo "🧹 Executing Pods data reset..."
    ./scripts/pods-reset.sh
    check_success "Pods data reset"
else
    echo -e "${YELLOW}⚠️ Script pods-reset.sh not found. Skipping reset.${NC}"
fi

STEP_END=$(date +%s)
STEP_TIME=$((STEP_END - STEP_START))
echo -e "${GREEN}✅ Pods data reset successfully! (${STEP_TIME}s)${NC}"

# Step 5: Import demo data
echo ""
show_progress 5
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}   STEP 5/5: 📦 DEMO DATA IMPORT${NC}"
echo -e "${BLUE}   Estimated time: $(estimate_time 5)${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

STEP_START=$(date +%s)

if [ -f "./scripts/pods-import.sh" ]; then
    ./scripts/pods-import.sh
    check_success "Demo data import"
else
    echo -e "${RED}❌ Script pods-import.sh not found!${NC}"
    exit 1
fi

STEP_END=$(date +%s)
STEP_TIME=$((STEP_END - STEP_START))
echo -e "${GREEN}✅ Demo data imported successfully! (${STEP_TIME}s)${NC}"

# Final status
echo ""
echo -e "${GREEN}🎉 COMPLETE INITIALIZATION FINISHED!${NC}"

# Calculate total execution time
END_TIME=$(date +%s)
TOTAL_TIME=$((END_TIME - START_TIME))
MINUTES=$((TOTAL_TIME / 60))
SECONDS=$((TOTAL_TIME % 60))

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
echo -e "${CYAN}⏱️  EXECUTION TIME:${NC}"
if [ $MINUTES -gt 0 ]; then
    echo -e "${GREEN}🕒 Total time: ${MINUTES}m ${SECONDS}s${NC}"
else
    echo -e "${GREEN}🕒 Total time: ${SECONDS}s${NC}"
fi
echo ""
echo -e "${CYAN}🌐 SYSTEM ACCESS:${NC}"
echo -e "${BLUE}🏠 Site:${NC} https://smart-gallery.ddev.site"
echo -e "${BLUE}🔑 Admin:${NC} https://smart-gallery.ddev.site/wp-admin"
echo -e "${BLUE}👤 User:${NC} admin"
echo -e "${BLUE}🔒 Password:${NC} admin"
echo -e "${BLUE}🗄️ phpMyAdmin:${NC} ddev phpmyadmin"
echo ""
echo -e "${CYAN}📋 NEXT STEPS:${NC}"
echo "1. Access WordPress Admin to configure Elementor"
echo "2. Create a page with Smart Gallery widget"
echo "3. Configure filters as needed"
echo ""
echo -e "${GREEN}🎯 Environment ready for development!${NC}"
