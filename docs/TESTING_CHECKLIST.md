# Smart Gallery — Testing Checklist

This file centralizes manual and automated test cases for Smart Gallery. Use it during development, QA, and before releases. The checklist is organized by project phases.

---

## Phase 1 — Gallery basics

- [x] Grid layout: images display in configured columns on desktop/tablet/mobile
- [x] Responsive breakpoints: columns adjust to settings
- [x] Hover effects: image and content variations render correctly
- [x] Card link behavior: clicking a card opens target URL or detail view
- [x] No-results state: custom message appears when no posts match

## Phase 2 — Pagination

- [x] Pagination shows correct number of pages given posts_per_page
- [x] Page links preserve search and filter parameters
- [x] Next/Prev navigation works and updates URL

## Phase 3 — Search

- [x] Search bar accepts text and filters server-side results
- [x] Clear search resets results and URL
- [x] Search + pagination interaction preserves search term
- [x] Search integrates with filters (field and taxonomy)

## Phase 4 — Advanced Filters (current focus)

### F4.1 — Custom Fields Filtering

- [ ] Filter UI: checkboxes/radios render for each configured field
- [ ] Multi-select: selecting multiple values refines results (AND logic)
- [ ] Counts: each value shows number of matching posts
- [ ] URL persistence: selected field values reflect in URL parameters
- [ ] Auto-submit: JS submits filter form when `auto_submit` enabled
- [ ] Field types: validate behavior for text/number/date/boolean/relationship
- [ ] Performance: filter queries return in acceptable time (< 2s) on demo data

Edge cases to test:
- Empty field values
- Very long list of distinct values (>200)
- Fields with comma-separated values

### F4.2 — Taxonomy Filtering

- [ ] Taxonomy checkboxes render and select correctly
- [ ] Hierarchical display: parent-child relationships shown with indentation
- [ ] Selecting parent optionally includes/excludes children (configurable)
- [ ] Shared taxonomies across post types behave correctly
- [ ] Combined with custom fields and search produces correct results

Edge cases:
- Deep hierarchies (3+ levels)
- Taxonomies with thousands of terms

### F4.3 — Combined Filtering

- [ ] Mixing search + fields + taxonomy filters returns intersection results
- [ ] URL state preserves all active filters and page number
- [ ] Clearing a filter updates results and URL correctly

### F4.4 — UX and polish

- [ ] Clear individual filter button works
- [ ] Clear all filters resets state and URL
- [ ] Active filters summary displays selected items
- [ ] Loading indicators show during server requests
- [ ] Mobile layout: filters usable on small screens

## Integration & Acceptance

- [ ] Demo data imported (use `demo-data/` scripts)
- [ ] Run tests with at least two CPTs sharing taxonomies
- [ ] Confirm Pods field types compatibility
- [ ] Confirm Elementor preview reflects active settings

## Smoke tests (quick checks after deployment)

- [ ] Homepage with gallery loads in < 3s (no heavy filters)
- [ ] Applying a filter returns results in < 2s on demo environment
- [ ] Pagination and search combinations don't 404

## How to run

1. Start DDEV: `ddev start`
2. Import DB + media (if needed): `./init.sh`
3. Import demo pods data: `./scripts/pods-import.sh`
4. Visit gallery pages and perform tests described above

---

If you find a failing test, create an issue with steps-to-reproduce, expected vs actual, and a short debug log (query, PHP error, browser console). Use the `scripts/create-issues-simple.sh` helper if you want to batch-create multiple issues.
