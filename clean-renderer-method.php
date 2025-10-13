<?php
/**
 * Clean version of render_filters_interface method
 */

    /**
     * Render filters interface in left bar
     * 
     * @param array $settings
     * @param string $search_term
     */
    public function render_filters_interface($settings, $search_term = '') {
        $selected_cpt = $settings['selected_cpt'] ?? '';
        $available_fields = $settings['available_fields_for_filtering'] ?? [];
        $available_taxonomies = $settings['available_taxonomies_for_filtering'] ?? [];
        
        // Bail if no CPT selected
        if (empty($selected_cpt)) {
            return;
        }

        // Check if we have any filtering enabled (fields OR taxonomies)
        $has_fields = !empty($available_fields);
        $has_taxonomies = !empty($available_taxonomies);
        
        if (!$has_fields && !$has_taxonomies) {
            return;
        }

        // Start rendering filter interface
        echo '<div class="smart-gallery-filters">';
        echo '<h4 class="smart-gallery-filters-title">' . esc_html__('Filter Options', 'smart-gallery') . '</h4>';
        
        // Start unified form
        echo '<form method="get" class="smart-gallery-filters-form">';
        
        // Preserve URL parameters
        $this->preserve_url_parameters_unified();
        
        // Get current filter values from URL
        $current_filters = $this->get_current_filters_from_url();
        
        // Render custom fields filters if enabled
        if ($has_fields) {
            $this->render_custom_fields_section($selected_cpt, $available_fields, $current_filters, $search_term);
        }
        
        // Add taxonomy filters (F3.3)
        $this->render_taxonomy_filters($settings, $current_filters, $search_term);

        echo '</form>'; // End unified form

        // Global clear filters button
        $current_taxonomy_filters = $this->get_current_taxonomy_filters_from_url();
        if (!empty($current_filters) || !empty($current_taxonomy_filters)) {
            echo '<button type="button" class="smart-gallery-clear-all-filters" onclick="' . esc_attr($this->get_clear_all_filters_js()) . '">';
            echo '<span class="clear-icon">🗑️</span> ';
            echo esc_html__('Clear All Filters', 'smart-gallery');
            echo '</button>';
        }

        echo '</div>'; // End smart-gallery-filters
        
        // Add JavaScript for filter submission
        $this->add_filters_javascript();
    }

    /**
     * Render custom fields filter section
     */
    private function render_custom_fields_section($selected_cpt, $available_fields, $current_filters, $search_term) {
        // Ensure available_fields is a proper array of field names
        if (!is_array($available_fields)) {
            $available_fields = [$available_fields];
        }
        
        // Filter out empty values
        $available_fields = array_filter($available_fields);
        
        // IMPORTANT: Filter fields to only include those that belong to the selected CPT
        $cpt_fields = $this->pods_integration->get_pod_fields($selected_cpt);
        $valid_fields = [];
        
        if (!empty($cpt_fields)) {
            foreach ($available_fields as $field_name) {
                // Only include fields that actually exist in the selected CPT
                if (isset($cpt_fields[$field_name])) {
                    $valid_fields[] = $field_name;
                }
            }
        }
        
        if (empty($valid_fields)) {
            return;
        }

        // Get field values with counts (using only valid fields)
        $field_values = $this->pods_integration->get_multiple_field_values(
            $selected_cpt, 
            $valid_fields, 
            $current_filters, 
            $search_term
        );

        // Only render if we have actual field values
        if (empty($field_values)) {
            return;
        }

        // Render each field filter section
        foreach ($valid_fields as $field_name) {
            if (!isset($field_values[$field_name]) || empty($field_values[$field_name])) {
                continue; // Skip fields with no values
            }

            $field_label = $this->get_field_label($selected_cpt, $field_name);
            $values = $field_values[$field_name];

            echo '<div class="smart-gallery-filter-section">';
            echo '<h5 class="smart-gallery-filter-title">' . esc_html($field_label) . '</h5>';
            
            echo '<div class="smart-gallery-filter-options">';
            
            foreach ($values as $value => $count) {
                $is_selected = isset($current_filters[$field_name]) && 
                              in_array($value, $current_filters[$field_name]);
                $checkbox_id = 'filter_' . sanitize_key($field_name) . '_' . sanitize_key($value);
                
                echo '<label class="smart-gallery-filter-option" for="' . esc_attr($checkbox_id) . '">';
                echo '<input type="checkbox" ';
                echo 'id="' . esc_attr($checkbox_id) . '" ';
                echo 'name="filter[' . esc_attr($field_name) . '][]" ';
                echo 'value="' . esc_attr($value) . '" ';
                echo $is_selected ? 'checked' : '';
                echo ' onchange="smartGallerySubmitFilters()"';
                echo '>';
                echo '<span class="filter-value">' . esc_html($value) . '</span>';
                echo '<span class="filter-count">(' . intval($count) . ')</span>';
                echo '</label>';
            }
            
            echo '</div>';
            echo '</div>'; // End filter-section
        }
    }