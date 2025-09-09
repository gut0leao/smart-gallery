#!/bin/bash

# Smart Gallery - Complete Environment Destruction
# This script completely removes the DDEV and Docker environment

# Check if called from init.sh (less alarming mode)
CALLED_FROM_INIT=${CALLED_FROM_INIT:-false}

if [ "$CALLED_FROM_INIT" = "true" ]; then
    echo "🧹 Smart Gallery - Environment Cleanup"
    echo "=============================================="
else
    echo "💥 Smart Gallery - Environment Destroyer"
    echo "==============================================="
fi

# Colors for output
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

if [ "$CALLED_FROM_INIT" = "true" ]; then
    echo -e "${BLUE}🔄 Preparing clean environment for initialization...${NC}"
    echo ""
    echo -e "${YELLOW}This cleanup will:${NC}"
    echo "🧹 Stop and remove existing project containers"
    echo "🧹 Remove project-specific images and volumes"
    echo "🧹 Clean project networks"
    echo "🧹 Reset DDEV project to fresh state"
    echo ""
    echo -e "${GREEN}✅ Docker base images will be preserved${NC}"
    echo -e "${CYAN}ℹ️ This is normal for initialization - creating a clean slate${NC}"
    echo ""
else
    echo -e "${RED}⚠️  COMPLETE ENVIRONMENT DESTRUCTION${NC}"
    echo ""
    echo -e "${YELLOW}This script will:${NC}"
    echo "🗑️ Stop and remove project containers"
    echo "🗑️ Remove project-specific images"
    echo "🗑️ Clean project volumes and networks"
    echo "🗑️ Remove DDEV project"
    echo "🧹 Conservative cleanup (preserves base images)"
    echo ""
    echo -e "${GREEN}✅ Docker base images will be preserved${NC}"
    echo -e "${YELLOW}ℹ️ This speeds up future environment recreations${NC}"
    echo ""
    echo -e "${RED}⚠️  THIS ACTION IS IRREVERSIBLE!${NC}"
    echo -e "${RED}⚠️  ALL YOUR WORK WILL BE LOST!${NC}"
    echo ""
fi

if [ "$CALLED_FROM_INIT" = "true" ]; then
    # Skip confirmation when called from init.sh (user already confirmed)
    echo -e "${BLUE}🚀 Proceeding with environment cleanup...${NC}"
    confirm="DESTROY"
else
    read -p "Are you ABSOLUTELY SURE you want to destroy everything? (type 'DESTROY'): " confirm
    if [ "$confirm" != "DESTROY" ]; then
        echo -e "${GREEN}😌 Cancelled. Environment preserved.${NC}"
        exit 0
    fi
fi

echo ""
if [ "$CALLED_FROM_INIT" = "true" ]; then
    echo -e "${BLUE}🧹 STARTING CLEANUP...${NC}"
else
    echo -e "${RED}💀 STARTING DESTRUCTION...${NC}"
fi
echo ""

# Step 1: Stop DDEV project
echo -e "${BLUE}1/7${NC} 🛑 Stopping DDEV project..."
if ddev stop 2>/dev/null; then
    echo "   ✅ Project stopped"
else
    echo "   ⚠️ Project already stopped or error"
fi

# Step 2: Delete DDEV project
echo -e "${BLUE}2/7${NC} 🗑️ Removing DDEV project..."
if ddev delete --yes 2>/dev/null; then
    echo "   ✅ Project removed"
else
    echo "   ⚠️ Project already removed or error"
fi

# Step 3: Remove project containers only
echo -e "${BLUE}3/7${NC} 📦 Removing project containers..."
# Remove only DDEV project containers
project_containers=$(docker ps -aq --filter "name=ddev-smart-gallery" 2>/dev/null)
if [ ! -z "$project_containers" ]; then
    docker rm -f $project_containers 2>/dev/null
    echo "   ✅ Project containers removed"
else
    echo "   ℹ️ No project containers found"
fi

# Also remove any exited containers to clean up
exited_containers=$(docker ps -aq --filter "status=exited" 2>/dev/null)
if [ ! -z "$exited_containers" ]; then
    docker rm $exited_containers 2>/dev/null
    echo "   🧹 Exited containers removed"
fi

# Step 4: Remove project-specific images only
echo -e "${BLUE}4/7${NC} 🖼️ Removing project-specific images..."
# Remove only DDEV project images with project name in tag
project_images=$(docker images --format "table {{.Repository}}:{{.Tag}}" | grep "smart-gallery-built" | awk '{print $1}' 2>/dev/null)
if [ ! -z "$project_images" ]; then
    echo "$project_images" | xargs -r docker rmi -f 2>/dev/null
    echo "   ✅ Project-specific images removed"
else
    echo "   ℹ️ No project-specific images found"
fi

# Also remove any dangling/unused images to save space (but keep base images)
echo "   🧹 Removing orphaned images..."
docker image prune -f 2>/dev/null
echo "   ✅ Orphaned images removed"

# Step 5: Remove project volumes only
echo -e "${BLUE}5/7${NC} 💾 Removing project volumes..."
# Remove only DDEV project volumes
project_volumes=$(docker volume ls --filter "name=smart-gallery" -q 2>/dev/null)
if [ ! -z "$project_volumes" ]; then
    docker volume rm $project_volumes 2>/dev/null
    echo "   ✅ Project volumes removed"
else
    echo "   ℹ️ No project volumes found"
fi

# Remove dangling volumes
dangling_volumes=$(docker volume ls --filter "dangling=true" -q 2>/dev/null)
if [ ! -z "$dangling_volumes" ]; then
    docker volume rm $dangling_volumes 2>/dev/null
    echo "   🧹 Orphaned volumes removed"
fi

# Step 6: Remove project networks only
echo -e "${BLUE}6/7${NC} 🌐 Removing project networks..."
# Remove only DDEV project networks
project_networks=$(docker network ls --filter "name=ddev-smart-gallery" -q 2>/dev/null)
if [ ! -z "$project_networks" ]; then
    docker network rm $project_networks 2>/dev/null
    echo "   ✅ Project networks removed"
else
    echo "   ℹ️ No project networks found"
fi

# Remove unused networks
echo "   🧹 Removing unused networks..."
docker network prune -f 2>/dev/null
echo "   ✅ Orphaned networks removed"

# Step 7: System cleanup (conservative)
echo -e "${BLUE}7/7${NC} 🧹 Final system cleanup..."
# Clean up only unused resources, preserve base images and cache
docker system prune -f 2>/dev/null
echo "   ✅ System cleaned (base images preserved)"

echo ""
if [ "$CALLED_FROM_INIT" = "true" ]; then
    echo -e "${GREEN}🧹 ENVIRONMENT CLEANUP COMPLETE!${NC}"
    echo ""
    echo -e "${CYAN}✅ Clean slate ready for initialization${NC}"
else
    echo -e "${GREEN}💀 COMPLETE DESTRUCTION!${NC}"
    echo ""
    echo -e "${YELLOW}To recreate the environment:${NC}"
    echo "1. ddev start"
    echo "2. ./wp-setup.sh"
    echo "3. ./demo-data/pods-import.sh"
    echo ""
    echo -e "${GREEN}🎉 Environment completely destroyed and cleaned!${NC}"
fi
