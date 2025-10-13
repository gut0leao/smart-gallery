# Smart Gallery - GitHub Issues Templates

## How to use:
1. Go to: https://github.com/gut0leao/smart-gallery/issues
2. Click "New Issue" 
3. Copy and paste each issue below
4. Set appropriate labels and milestones

---

## ISSUE #1: [F1.3] Basic Elementor Controls

### 🏷️ **Labels:** `enhancement`, `phase-1-foundation`, `low-complexity`, `elementor`
### 🎯 **Milestone:** Phase 1 - Foundation
### 👤 **Assignee:** gut0leao

### **Description:**
Implement essential widget configuration controls in Elementor editor.

### **Requirements:**
- [ ] CPT selection dropdown (with Pods CPTs)
- [ ] Posts per page setting (number input)
- [ ] Basic layout controls (columns, spacing)
- [ ] Image size selection
- [ ] Order/OrderBy controls

### **Acceptance Criteria:**
- [ ] All controls visible in Elementor editor
- [ ] Controls update widget preview in real-time
- [ ] Proper default values set
- [ ] Controls validation working
- [ ] No console errors in Elementor editor

### **Dependencies:**
- None (Foundation feature)

### **Complexity:** Low
### **Estimated Time:** 2-4 hours

---

## ISSUE #2: [F1.2] Pods Framework Integration

### 🏷️ **Labels:** `enhancement`, `phase-1-foundation`, `high-complexity`, `pods-framework`
### 🎯 **Milestone:** Phase 1 - Foundation  
### 👤 **Assignee:** gut0leao

### **Description:**
Core integration with Pods Framework for CPT detection and data access.

### **Requirements:**
- [ ] Detect available Pods CPTs
- [ ] Access custom fields and taxonomies
- [ ] Handle missing Pods gracefully
- [ ] CPT validation and error handling
- [ ] Dynamic control population based on selected CPT

### **Acceptance Criteria:**
- [ ] Widget detects all Pods CPTs automatically
- [ ] Graceful fallback when Pods not available
- [ ] Custom fields and taxonomies accessible
- [ ] No PHP errors when Pods missing
- [ ] Admin notices for missing dependencies

### **Dependencies:**
- F1.3: Basic Elementor Controls

### **Complexity:** High
### **Estimated Time:** 6-8 hours

---

## ISSUE #3: [F1.1] Basic Gallery Display

### 🏷️ **Labels:** `enhancement`, `phase-1-foundation`, `medium-complexity`, `gallery`
### 🎯 **Milestone:** Phase 1 - Foundation
### 👤 **Assignee:** gut0leao

### **Description:**
Display CPT instances in responsive grid layout with basic functionality.

### **Requirements:**
- [ ] Show featured images as thumbnails
- [ ] Responsive grid layout (configurable columns)
- [ ] Click to open CPT permalink in new tab
- [ ] Basic hover effects
- [ ] Handle missing images gracefully

### **Acceptance Criteria:**
- [ ] Gallery displays CPT posts correctly
- [ ] Responsive design works on all devices
- [ ] Images load properly with fallbacks
- [ ] Links work correctly (new tab)
- [ ] Grid layout respects column settings

### **Dependencies:**
- F1.2: Pods Framework Integration
- F1.3: Basic Elementor Controls

### **Complexity:** Medium
### **Estimated Time:** 4-6 hours

---

## ISSUE #4: [F2.3] State Messages

### 🏷️ **Labels:** `enhancement`, `phase-2-core`, `low-complexity`, `ux`
### 🎯 **Milestone:** Phase 2 - Core Features
### 👤 **Assignee:** gut0leao

### **Description:**
Implement user feedback messages for different widget states.

### **Requirements:**
- [ ] "No results found" message (configurable)
- [ ] Empty state handling
- [ ] Error state messages
- [ ] Loading indicators
- [ ] Elementor-style message components

### **Acceptance Criteria:**
- [ ] Messages appear in appropriate situations
- [ ] Messages are configurable in widget settings
- [ ] Styling consistent with Elementor
- [ ] Messages support HTML content
- [ ] Proper accessibility attributes

### **Dependencies:**
- F1.1: Basic Gallery Display

### **Complexity:** Low
### **Estimated Time:** 2-3 hours

---

## ISSUE #5: [F2.1] Hover Effects & Descriptions

### 🏷️ **Labels:** `enhancement`, `phase-2-core`, `medium-complexity`, `ux`
### 🎯 **Milestone:** Phase 2 - Core Features
### 👤 **Assignee:** gut0leao

### **Description:**
Interactive hover states with content preview and smooth animations.

### **Requirements:**
- [ ] Hover overlay with post information
- [ ] Configurable description field selection
- [ ] Smooth CSS transitions
- [ ] Fallback to excerpt if no field selected
- [ ] Touch-friendly mobile interactions

### **Acceptance Criteria:**
- [ ] Hover effects work smoothly on desktop
- [ ] Touch interactions work on mobile
- [ ] Description content displays correctly
- [ ] Configurable field selection functional
- [ ] Performance optimized (no lag)

### **Dependencies:**
- F1.1: Basic Gallery Display
- F2.3: State Messages

### **Complexity:** Medium
### **Estimated Time:** 4-5 hours

---

## ISSUE #6: [F2.2] Pagination System

### 🏷️ **Labels:** `enhancement`, `phase-2-core`, `medium-complexity`, `pagination`
### 🎯 **Milestone:** Phase 2 - Core Features
### 👤 **Assignee:** gut0leao

### **Description:**
Navigate through multiple pages of gallery results.

### **Requirements:**
- [ ] Previous/Next buttons
- [ ] Numbered page buttons
- [ ] Configurable posts per page
- [ ] URL parameter handling
- [ ] AJAX page loading (optional)

### **Acceptance Criteria:**
- [ ] Pagination displays when needed
- [ ] All pagination buttons work correctly
- [ ] URL updates with page changes
- [ ] Pagination recalculates on filter changes
- [ ] Responsive pagination design

### **Dependencies:**
- F1.1: Basic Gallery Display
- F2.3: State Messages

### **Complexity:** Medium
### **Estimated Time:** 5-6 hours

---

## ISSUE #7: [F3.1] Text Search

### 🏷️ **Labels:** `enhancement`, `phase-3-search`, `medium-complexity`, `search`
### 🎯 **Milestone:** Phase 3 - Search & Basic Filtering
### 👤 **Assignee:** gut0leao

### **Description:**
Search functionality within CPT content with MercadoLibre-style input.

### **Requirements:**
- [ ] Search input with magnifying glass icon
- [ ] Search in post title and content
- [ ] Case-insensitive, trimmed input
- [ ] Configurable placeholder text
- [ ] Configurable position (sidebar top OR gallery top)

### **Acceptance Criteria:**
- [ ] Search input styled like MercadoLibre
- [ ] Search works on title and content
- [ ] Results update dynamically
- [ ] Position setting works correctly
- [ ] Search integrates with pagination

### **Dependencies:**
- F2.2: Pagination System
- F2.3: State Messages

### **Complexity:** Medium
### **Estimated Time:** 4-5 hours

---

## ISSUE #8: [F5.1] Dependency Management

### 🏷️ **Labels:** `enhancement`, `phase-3-search`, `low-complexity`, `admin`
### 🎯 **Milestone:** Phase 3 - Search & Basic Filtering
### 👤 **Assignee:** gut0leao

### **Description:**
Handle plugin dependencies gracefully with proper admin notices.

### **Requirements:**
- [ ] WordPress admin notices for missing dependencies
- [ ] Elementor widget warnings for missing Pods
- [ ] Graceful degradation when dependencies unavailable
- [ ] Clear installation instructions

### **Acceptance Criteria:**
- [ ] Admin notices appear when dependencies missing
- [ ] Widget shows helpful messages in Elementor
- [ ] Plugin doesn't crash when dependencies missing
- [ ] Installation instructions are clear
- [ ] Notices can be dismissed

### **Dependencies:**
- None (System level)

### **Complexity:** Low
### **Estimated Time:** 2-3 hours

---

## ISSUE #9: [F3.2] Custom Fields Filtering

### 🏷️ **Labels:** `enhancement`, `phase-4-advanced`, `high-complexity`, `filtering`
### 🎯 **Milestone:** Phase 4 - Advanced Filtering
### 👤 **Assignee:** gut0leao

### **Description:**
Filter gallery items by CPT custom fields with dynamic UI generation.

### **Requirements:**
- [ ] Admin configurable field selection
- [ ] Dynamic filter UI based on field types
- [ ] Multiple field filtering (AND logic)
- [ ] Field type detection (text, number, select, etc.)
- [ ] Filter value validation

### **Acceptance Criteria:**
- [ ] Filters appear based on admin configuration
- [ ] Different field types render appropriate controls
- [ ] Multiple filters work together correctly
- [ ] Filter values are validated properly
- [ ] Performance optimized for large datasets

### **Dependencies:**
- F1.2: Pods Integration
- F3.1: Text Search

### **Complexity:** High
### **Estimated Time:** 8-10 hours

---

## ISSUE #10: [F3.3] Taxonomy Filtering

### 🏷️ **Labels:** `enhancement`, `phase-4-advanced`, `high-complexity`, `filtering`
### 🎯 **Milestone:** Phase 4 - Advanced Filtering
### 👤 **Assignee:** gut0leao

### **Description:**
Filter by CPT taxonomies with hierarchical support and tree structures.

### **Requirements:**
- [ ] Admin configurable taxonomy selection
- [ ] Checkbox-based filtering interface
- [ ] Hierarchical taxonomy support (tree structure)
- [ ] Parent/child selection logic
- [ ] Multiple taxonomy filtering

### **Acceptance Criteria:**
- [ ] Taxonomies display as configured
- [ ] Hierarchical taxonomies show tree structure
- [ ] Parent selection auto-selects children
- [ ] Multiple taxonomy filters work together
- [ ] Performance optimized for large taxonomies

### **Dependencies:**
- F1.2: Pods Integration
- F3.2: Custom Fields Filtering

### **Complexity:** High
### **Estimated Time:** 8-10 hours

---

## ISSUE #11: [F3.4] Filter Management

### 🏷️ **Labels:** `enhancement`, `phase-4-advanced`, `high-complexity`, `filtering`
### 🎯 **Milestone:** Phase 4 - Advanced Filtering
### 👤 **Assignee:** gut0leao

### **Description:**
Combined filter operations with clear controls and state management.

### **Requirements:**
- [ ] Clear all filters button (trash icon)
- [ ] Dynamic filter value updates based on search results
- [ ] Automatic pagination recalculation
- [ ] Filter state persistence during interactions
- [ ] URL parameter handling for filter states

### **Acceptance Criteria:**
- [ ] Clear button resets all filters
- [ ] Filter values update dynamically
- [ ] Pagination recalculates automatically
- [ ] Filter state persists during other operations
- [ ] URL reflects current filter state

### **Dependencies:**
- F3.1: Text Search
- F3.2: Custom Fields Filtering  
- F3.3: Taxonomy Filtering

### **Complexity:** High
### **Estimated Time:** 6-8 hours

---

## ISSUE #12: [F4.1] Loading States

### 🏷️ **Labels:** `enhancement`, `phase-5-polish`, `low-complexity`, `ux`
### 🎯 **Milestone:** Phase 5 - Polish & Enhancement
### 👤 **Assignee:** gut0leao

### **Description:**
Visual feedback during data operations with loading overlays.

### **Requirements:**
- [ ] Overlay loading spinner covering entire widget
- [ ] Prevent double-clicks and multiple requests
- [ ] Smooth transitions in/out
- [ ] Loading states for different operations
- [ ] Accessible loading indicators

### **Acceptance Criteria:**
- [ ] Loading overlay appears during operations
- [ ] Double-clicks prevented effectively
- [ ] Smooth loading transitions
- [ ] Different loading states handled
- [ ] Screen readers announce loading states

### **Dependencies:**
- All search/filter functionality

### **Complexity:** Low
### **Estimated Time:** 3-4 hours

---

## ISSUE #13: [F4.2] Visual Integration

### 🏷️ **Labels:** `enhancement`, `phase-5-polish`, `medium-complexity`, `styling`
### 🎯 **Milestone:** Phase 5 - Polish & Enhancement
### 👤 **Assignee:** gut0leao

### **Description:**
Seamless integration with Elementor themes and styling system.

### **Requirements:**
- [ ] Inherit Elementor colors and typography
- [ ] Use Elementor's message/alert styles
- [ ] Minimal custom CSS
- [ ] Responsive design consistency
- [ ] Theme compatibility testing

### **Acceptance Criteria:**
- [ ] Widget looks native to current theme
- [ ] Colors inherit from Elementor settings
- [ ] Typography matches theme
- [ ] Messages use Elementor components
- [ ] Works with popular themes

### **Dependencies:**
- F1.3: Elementor Controls

### **Complexity:** Medium
### **Estimated Time:** 4-6 hours

---

## ISSUE #14: [F4.3] Animations & Transitions

### 🏷️ **Labels:** `enhancement`, `phase-5-polish`, `low-complexity`, `ux`
### 🎯 **Milestone:** Phase 5 - Polish & Enhancement
### 👤 **Assignee:** gut0leao

### **Description:**
Smooth user experience enhancements with simple animations.

### **Requirements:**
- [ ] Content loading transitions
- [ ] Hover effect animations
- [ ] Filter application feedback
- [ ] Simple easing functions
- [ ] Performance-optimized animations

### **Acceptance Criteria:**
- [ ] All transitions are smooth
- [ ] Animations enhance UX without distraction
- [ ] Performance remains good
- [ ] Animations respect user preferences
- [ ] Fallbacks for reduced motion

### **Dependencies:**
- F2.1: Hover Effects
- F4.1: Loading States

### **Complexity:** Low
### **Estimated Time:** 3-4 hours

---

## ISSUE #15: [F5.2] Advanced Elementor Controls

### 🏷️ **Labels:** `enhancement`, `phase-5-polish`, `medium-complexity`, `elementor`
### 🎯 **Milestone:** Phase 5 - Polish & Enhancement
### 👤 **Assignee:** gut0leao

### **Description:**
Complete widget configuration interface with all advanced options.

### **Requirements:**
- [ ] All configuration options organized in sections
- [ ] Dynamic controls (show/hide based on selections)
- [ ] Validation and error handling
- [ ] Preview updates in real-time
- [ ] Import/export widget settings

### **Acceptance Criteria:**
- [ ] All controls organized logically
- [ ] Dynamic controls work correctly
- [ ] Real-time preview updates
- [ ] Validation prevents errors
- [ ] Settings can be saved/loaded

### **Dependencies:**
- All functional layers

### **Complexity:** Medium
### **Estimated Time:** 5-7 hours

---

## ISSUE #16: [F5.3] Plugin Information

### 🏷️ **Labels:** `enhancement`, `phase-6-final`, `low-complexity`, `documentation`
### 🎯 **Milestone:** Phase 6 - Finalization
### 👤 **Assignee:** gut0leao

### **Description:**
Plugin branding and information display in widget controls.

### **Requirements:**
- [ ] Plugin info section in widget controls
- [ ] Repository link for documentation
- [ ] Version information display
- [ ] Credits and attribution
- [ ] Help links and documentation

### **Acceptance Criteria:**
- [ ] Plugin information visible in controls
- [ ] All links work correctly
- [ ] Version information accurate
- [ ] Professional presentation
- [ ] Help documentation accessible

### **Dependencies:**
- None

### **Complexity:** Low
### **Estimated Time:** 1-2 hours

---

## 📊 MILESTONES TO CREATE:

1. **Phase 1 - Foundation** (3 issues)
2. **Phase 2 - Core Features** (3 issues)  
3. **Phase 3 - Search & Basic Filtering** (2 issues)
4. **Phase 4 - Advanced Filtering** (3 issues)
5. **Phase 5 - Polish & Enhancement** (4 issues)
6. **Phase 6 - Finalization** (1 issue)

## 🏷️ LABELS TO CREATE:

- `phase-1-foundation`
- `phase-2-core`
- `phase-3-search`
- `phase-4-advanced`
- `phase-5-polish`
- `phase-6-final`
- `low-complexity`
- `medium-complexity`
- `high-complexity`
- `pods-framework`
- `elementor`
- `gallery`
- `pagination`
- `search`
- `filtering`
- `ux`
- `styling`
- `admin`
- `documentation`
