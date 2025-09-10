#!/bin/bash

# Simple GitHub Issues Creation for Smart Gallery
# Compatible with GitHub CLI v2.4.0

set -e

echo "🚀 Smart Gallery Issues Creation (Simple Version)"
echo "Repository: gut0leao/smart-gallery"
echo ""

# Check if we're in a git repository
if ! git rev-parse --is-inside-work-tree > /dev/null 2>&1; then
    echo "❌ Not in a git repository"
    exit 1
fi

# Get repository info
REPO_OWNER=$(git remote get-url origin | sed -n 's#.*github\.com[:/]\([^/]*\)/.*#\1#p')
REPO_NAME=$(git remote get-url origin | sed -n 's#.*github\.com[:/][^/]*/\([^/]*\)\.git#\1#p' | sed 's/\.git$//')

echo "📋 Creating core issues for $REPO_OWNER/$REPO_NAME..."
echo ""

# Phase 1 Issues
echo "🔧 Creating Phase 1 - Foundation issues..."

gh issue create \
  --title "F1.3 - Basic Elementor Controls" \
  --body "## 🎯 Description
Essential widget configuration in Elementor

## 📋 Requirements
- CPT selection dropdown
- Posts per page setting  
- Basic layout controls (columns, spacing)

## 🔗 Dependencies
- Elementor Page Builder

## ⏱️ Complexity
Low

## 📊 Estimated Time
2-4 hours

## ✅ Acceptance Criteria
- [ ] CPT dropdown populated with available Pods CPTs
- [ ] Posts per page number input with validation
- [ ] Column count selector (1-6 columns)
- [ ] Spacing controls for grid gaps
- [ ] Preview updates in real-time" \
  --repo "$REPO_OWNER/$REPO_NAME"

gh issue create \
  --title "F1.2 - Pods Framework Integration" \
  --body "## 🎯 Description
Core integration with Pods CPTs and custom fields

## 📋 Requirements
- Detect and list available CPTs from Pods
- Access custom fields and taxonomies
- Handle missing Pods scenarios gracefully

## 🔗 Dependencies
- Pods Framework plugin

## ⏱️ Complexity
High

## 📊 Estimated Time
6-8 hours

## ✅ Acceptance Criteria
- [ ] Function to detect installed Pods plugin
- [ ] Retrieve list of Pods CPTs
- [ ] Access custom fields for each CPT
- [ ] Graceful error handling when Pods missing
- [ ] Admin notices for dependency requirements" \
  --repo "$REPO_OWNER/$REPO_NAME"

gh issue create \
  --title "F1.1 - Basic Gallery Display" \
  --body "## 🎯 Description
Display CPT instances in a responsive grid layout

## 📋 Requirements
- Show featured image as main thumbnail
- Grid layout with configurable columns
- Responsive design (mobile/tablet/desktop)
- Click to open CPT permalink in new tab

## 🔗 Dependencies
- Pods Framework integration (F1.2)

## ⏱️ Complexity
Medium

## 📊 Estimated Time
4-6 hours

## ✅ Acceptance Criteria
- [ ] Grid layout rendering with CSS Grid
- [ ] Featured image display with fallback
- [ ] Responsive breakpoints (mobile/tablet/desktop)
- [ ] Click handlers for post permalinks
- [ ] Loading states during content fetch" \
  --repo "$REPO_OWNER/$REPO_NAME"

echo ""
echo "✅ Phase 1 issues created successfully!"
echo ""

# Phase 2 Issues
echo "🔧 Creating Phase 2 - Core Features issues..."

gh issue create \
  --title "F2.1 - Hover Effects & Descriptions" \
  --body "## 🎯 Description
Interactive hover states with content preview

## 📋 Requirements
- Hover overlay with post information
- Configurable description field (custom field or excerpt)
- Smooth transitions and animations
- Fallback to cropped content if no field selected

## 🔗 Dependencies
- F1.1 (Basic Gallery Display)

## ⏱️ Complexity
Medium

## 📊 Estimated Time
4-5 hours

## ✅ Acceptance Criteria
- [ ] CSS hover effects with smooth transitions
- [ ] Overlay with post title and description
- [ ] Configurable description source selection
- [ ] Text truncation for long descriptions
- [ ] Mobile-friendly hover alternatives" \
  --repo "$REPO_OWNER/$REPO_NAME"

gh issue create \
  --title "F2.2 - Pagination System" \
  --body "## 🎯 Description
Navigate through multiple pages of results

## 📋 Requirements
- Previous/Next buttons
- Numbered page buttons
- Configurable posts per page
- Dynamic recalculation on search/filter changes
- Standard pagination UI patterns

## 🔗 Dependencies
- F1.1 (Basic Gallery Display)

## ⏱️ Complexity
Medium

## 📊 Estimated Time
5-6 hours

## ✅ Acceptance Criteria
- [ ] Previous/Next navigation buttons
- [ ] Numbered page buttons with current page highlight
- [ ] Dynamic pagination based on total results
- [ ] AJAX-based page navigation
- [ ] URL parameter updates for page state" \
  --repo "$REPO_OWNER/$REPO_NAME"

gh issue create \
  --title "F2.3 - State Messages" \
  --body "## 🎯 Description
User feedback for different states

## 📋 Requirements
- \"No results found\" message (configurable)
- Empty state handling
- Loading indicators
- Error state management

## 🔗 Dependencies
- F1.1 (Basic Gallery Display)

## ⏱️ Complexity
Low

## 📊 Estimated Time
2-3 hours

## ✅ Acceptance Criteria
- [ ] Configurable \"no results\" message
- [ ] Loading spinner during content fetch
- [ ] Error messages for failed requests
- [ ] Empty state with helpful instructions
- [ ] Consistent styling with theme" \
  --repo "$REPO_OWNER/$REPO_NAME"

echo ""
echo "✅ Phase 2 issues created successfully!"
echo ""

# Phase 3 Issues
echo "🔧 Creating Phase 3 - Search & Filtering issues..."

gh issue create \
  --title "F3.1 - Text Search" \
  --body "## 🎯 Description
Search functionality within CPT content

## 📋 Requirements
- Search input with magnifying glass icon (like MercadoLibre style)
- Search in post title and content
- Case-insensitive, trimmed input
- Configurable placeholder text (default: \"Search...\")
- Configurable position (top of sidebar OR top of gallery)
- Clear search functionality

## 🔗 Dependencies
- F2.2 (Pagination), F2.3 (Messages)

## ⏱️ Complexity
Medium

## 📊 Estimated Time
4-5 hours

## ✅ Acceptance Criteria
- [ ] Search input with magnifying glass icon
- [ ] Real-time search as user types (debounced)
- [ ] Search in post title and content
- [ ] Clear search button
- [ ] Position configuration in widget settings" \
  --repo "$REPO_OWNER/$REPO_NAME"

echo ""
echo "✅ Phase 3 issues created successfully!"
echo ""

echo "🎉 Issues creation completed!"
echo ""
echo "📝 Next steps:"
echo "1. Review the created issues on GitHub"
echo "2. Start development with F1.3 (Basic Elementor Controls)"
echo "3. Follow the sequential development approach"
echo ""
echo "🔗 View issues: https://github.com/$REPO_OWNER/$REPO_NAME/issues"
