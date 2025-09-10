#!/bin/bash

# Smart Gallery - Prerequisites Check Script
# Usage: cd scripts && ./check-prerequisites.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 Smart Gallery Prerequisites Check${NC}\n"

# Check if gh is installed
echo -e "${YELLOW}📦 Checking GitHub CLI installation...${NC}"
if command -v gh &> /dev/null; then
    GH_VERSION=$(gh version | head -n1)
    echo -e "${GREEN}✅ GitHub CLI installed: $GH_VERSION${NC}"
else
    echo -e "${RED}❌ GitHub CLI (gh) not found${NC}"
    echo -e "${YELLOW}📝 To install GitHub CLI:${NC}"
    echo "  • Ubuntu/Debian: sudo apt install gh"
    echo "  • macOS: brew install gh"
    echo "  • Windows: winget install --id GitHub.cli"
    echo "  • More info: https://github.com/cli/cli#installation"
    exit 1
fi

# Check authentication
echo -e "\n${YELLOW}🔐 Checking GitHub CLI authentication...${NC}"
if gh auth status &> /dev/null; then
    GITHUB_USER=$(gh api user --jq .login 2>/dev/null || echo "authenticated")
    echo -e "${GREEN}✅ Authenticated as: $GITHUB_USER${NC}"
else
    echo -e "${RED}❌ Not authenticated with GitHub${NC}"
    echo -e "${YELLOW}📝 To authenticate:${NC}"
    echo "  gh auth login"
    echo ""
    read -p "Would you like to authenticate now? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        gh auth login
        echo -e "${GREEN}✅ Authentication completed${NC}"
    else
        echo -e "${RED}❌ Authentication required to create issues${NC}"
        exit 1
    fi
fi

# Check repository access
echo -e "\n${YELLOW}🏠 Checking repository access...${NC}"
REPO="gut0leao/smart-gallery"
if gh repo view "$REPO" &> /dev/null; then
    echo -e "${GREEN}✅ Repository access confirmed: $REPO${NC}"
else
    echo -e "${RED}❌ Cannot access repository: $REPO${NC}"
    echo -e "${YELLOW}📝 Make sure:${NC}"
    echo "  • Repository exists"
    echo "  • You have write access"
    echo "  • Repository name is correct"
    exit 1
fi

# Check if issues already exist
echo -e "\n${YELLOW}📋 Checking existing issues...${NC}"
EXISTING_ISSUES=$(gh issue list --repo "$REPO" --state all --json title --jq '[.[].title | select(startswith("[F"))] | length')
if [ "$EXISTING_ISSUES" -gt 0 ]; then
    echo -e "${YELLOW}⚠️  Found $EXISTING_ISSUES existing issues with [F*] prefix${NC}"
    echo -e "${YELLOW}📝 Running the script will create duplicates${NC}"
    echo ""
    read -p "Continue anyway? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}ℹ️  Script cancelled${NC}"
        exit 0
    fi
else
    echo -e "${GREEN}✅ No existing feature issues found${NC}"
fi

echo -e "\n${GREEN}🎉 All prerequisites met!${NC}"
echo -e "${BLUE}📝 You can now run:${NC}"
echo -e "${YELLOW}  ./create-issues.sh${NC}"
echo ""
echo -e "${BLUE}📊 This will create:${NC}"
echo "  • 16 issues (F1.1 through F5.3)"
echo "  • 6 milestones (Phase 1-6)"
echo "  • 19 labels (complexity, phases, areas)"
echo ""
echo -e "${YELLOW}⚠️  Note: The script will create all issues at once${NC}"
echo -e "${YELLOW}   Consider creating them in phases if you prefer${NC}"
