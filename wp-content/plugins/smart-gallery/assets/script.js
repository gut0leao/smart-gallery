/**
 * Smart Gallery - JavaScript functionality
 * 
 * Handles:
 * - Search form Enter key submission
 * - Accessibility improvements
 * - Simple form interactions
 * 
 * @since 1.3.0 (F3.1 - Text Search)
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeSearchFunctionality();
    });

    /**
     * Initialize search functionality
     */
    function initializeSearchFunctionality() {
        const searchForms = document.querySelectorAll('.smart-gallery-search-form');
        
        searchForms.forEach(function(form) {
            const searchInput = form.querySelector('.smart-gallery-search-input');
            const searchButton = form.querySelector('.smart-gallery-search-button-internal'); // Updated selector for internal button
            const clearButton = form.querySelector('.smart-gallery-clear-button');
            
            // Handle Enter key submission
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        form.submit();
                    }
                });
                
                // Focus search button when input has content
                searchInput.addEventListener('input', function() {
                    if (searchButton) {
                        if (searchInput.value.trim().length > 0) {
                            searchButton.removeAttribute('disabled');
                        } else {
                            searchButton.setAttribute('disabled', 'disabled');
                        }
                    }
                });
                
                // Initialize button state
                if (searchButton) {
                    if (searchInput.value.trim().length === 0) {
                        searchButton.setAttribute('disabled', 'disabled');
                    }
                }
            }
            
            // Handle search button click (internal button)
            if (searchButton) {
                searchButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (searchInput && searchInput.value.trim().length > 0) {
                        form.submit();
                    }
                });
                
                // Accessibility: Space and Enter key support
                searchButton.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        searchButton.click();
                    }
                });
            }
            
            // Handle clear button accessibility
            if (clearButton) {
                clearButton.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        clearButton.click();
                    }
                });
            }
        });
    }

})();
