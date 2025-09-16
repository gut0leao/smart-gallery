<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Smart Gallery Pods Integration
 * 
 * Handles all interactions with Pods Framework including:
 * - CPT detection and validation
 * - Post queries and data retrieval
 * - Custom fields and taxonomies access
 * - Error handling and graceful degradation
 * 
 * @since 1.0.2
 */
class Smart_Gallery_Pods_Integration {

    /**
     * Check if Pods plugin is active and available
     * 
     * @return bool
     */
    public function is_pods_available() {
        return function_exists('pods') && function_exists('pods_api');
    }

    /**
     * Get available Pods CPTs with enhanced error handling
     * 
     * @return array
     */
    public function get_available_cpts() {
        $options = [];

        // Check if Pods is active
        if (!$this->is_pods_available()) {
            $options['no_pods'] = esc_html__('Pods plugin not found', 'smart-gallery');
            return $options;
        }

        // Get Pods CPTs with comprehensive error handling
        try {
            $pods_api = pods_api();
            if (!$pods_api) {
                $options['api_error'] = esc_html__('Need at least one pod', 'smart-gallery');
                return $options;
            }

            $all_pods = $pods_api->load_pods(['type' => 'post_type']);
            
            if (!empty($all_pods) && is_array($all_pods)) {
                // Add default option first
                $options[''] = esc_html__('Select a Pod', 'smart-gallery');
                
                foreach ($all_pods as $pod) {
                    if (isset($pod['name']) && isset($pod['label']) && !empty($pod['name'])) {
                        // Verify the CPT actually exists in WordPress
                        if (post_type_exists($pod['name'])) {
                            $options[$pod['name']] = $pod['label'];
                        }
                    }
                }
                
                // If we only have the default option, no valid pods found
                if (count($options) <= 1) {
                    return ['no_cpts' => esc_html__('Need at least one pod', 'smart-gallery')];
                }
            } else {
                $options['no_cpts'] = esc_html__('Need at least one pod', 'smart-gallery');
            }
        } catch (Exception $e) {
            error_log('Smart Gallery - Pods integration error: ' . $e->getMessage());
            $options['error'] = esc_html__('Need at least one pod', 'smart-gallery');
        }

        return $options;
    }

    /**
     * Get posts from selected Pod/CPT with search support
     * 
     * @param string $cpt_name
     * @param int $posts_per_page
     * @param int $paged
     * @param string $search_term
     * @return array|WP_Error
     */
    public function get_pod_posts($cpt_name, $posts_per_page = 12, $paged = 1, $search_term = '', $custom_field_filters = [], $taxonomy_filters = []) {
        if (empty($cpt_name) || !$this->is_pods_available()) {
            return new WP_Error('no_pod', 'No valid pod specified');
        }

        // Verify CPT exists
        if (!post_type_exists($cpt_name)) {
            return new WP_Error('invalid_cpt', 'Post type does not exist');
        }

        try {
            // Use WP_Query for reliable post retrieval
            $query_args = [
                'post_type' => $cpt_name,
                'post_status' => 'publish',
                'posts_per_page' => intval($posts_per_page),
                'paged' => intval($paged),
                'meta_query' => [],
                'orderby' => 'date',
                'order' => 'DESC'
            ];

            // Add search functionality
            if (!empty($search_term)) {
                $search_term = sanitize_text_field($search_term);
                $query_args['s'] = $search_term;
            }

            // Apply custom field filters
            if (!empty($custom_field_filters) && is_array($custom_field_filters)) {
                $query_args = $this->apply_custom_field_filters($query_args, $custom_field_filters);
            }

            // Apply taxonomy filters
            if (!empty($taxonomy_filters) && is_array($taxonomy_filters)) {
                $query_args = $this->apply_taxonomy_filters($query_args, $taxonomy_filters);
            }

            $query = new WP_Query($query_args);
            
            if ($query->have_posts()) {
                $posts_data = [
                    'posts' => $query->posts,
                    'total' => $query->found_posts,
                    'pages' => $query->max_num_pages,
                    'current_page' => $paged,
                    'search_term' => $search_term,
                    'filters' => $custom_field_filters,
                    'taxonomy_filters' => $taxonomy_filters
                ];
                
                wp_reset_postdata();
                return $posts_data;
            } else {
                // No posts found is not an error - it's a normal result
                wp_reset_postdata();
                return [
                    'posts' => [],
                    'total' => 0,
                    'pages' => 0,
                    'current_page' => $paged,
                    'search_term' => $search_term,
                    'filters' => $custom_field_filters,
                    'taxonomy_filters' => $taxonomy_filters
                ];
            }
        } catch (Exception $e) {
            error_log('Smart Gallery - Post retrieval error: ' . $e->getMessage());
            return new WP_Error('query_error', 'Error retrieving posts');
        }
    }

    /**
     * Get Pod custom fields for a specific CPT
     * 
     * @param string $cpt_name
     * @return array
     */
    public function get_pod_fields($cpt_name) {
        if (empty($cpt_name) || !$this->is_pods_available()) {
            return [];
        }

        try {
            $pod = pods($cpt_name);
            if (!$pod || !$pod->valid()) {
                return [];
            }

            $fields = $pod->fields();
            $formatted_fields = [];
            
            if (is_array($fields)) {
                foreach ($fields as $field_name => $field_data) {
                    if (isset($field_data['label']) && !empty($field_data['label'])) {
                        $formatted_fields[$field_name] = [
                            'label' => $field_data['label'],
                            'type' => $field_data['type'] ?? 'text',
                            'options' => $field_data['options'] ?? []
                        ];
                    }
                }
            }
            
            return $formatted_fields;
        } catch (Exception $e) {
            error_log('Smart Gallery - Field retrieval error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Pod taxonomies for a specific CPT
     * 
     * @param string $cpt_name
     * @return array
     */
    public function get_pod_taxonomies($cpt_name) {
        if (empty($cpt_name) || !post_type_exists($cpt_name)) {
            return [];
        }

        try {
            $taxonomies = get_object_taxonomies($cpt_name, 'objects');
            $formatted_taxonomies = [];
            
            foreach ($taxonomies as $taxonomy) {
                // Skip built-in taxonomies if needed
                if (!$taxonomy->public) {
                    continue;
                }
                
                $terms = get_terms([
                    'taxonomy' => $taxonomy->name,
                    'hide_empty' => false,
                    'number' => 100 // Limit for performance
                ]);
                
                if (!is_wp_error($terms) && !empty($terms)) {
                    $formatted_taxonomies[$taxonomy->name] = [
                        'label' => $taxonomy->label,
                        'terms' => $terms
                    ];
                }
            }
            
            return $formatted_taxonomies;
        } catch (Exception $e) {
            error_log('Smart Gallery - Taxonomy retrieval error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate CPT name
     * 
     * @param string $cpt_name
     * @return bool
     */
    public function is_valid_cpt($cpt_name) {
        if (empty($cpt_name)) {
            return false;
        }

        return post_type_exists($cpt_name) && $this->is_pods_available();
    }

    /**
     * Get Pods status information for debugging
     * 
     * @param string $selected_cpt
     * @return array
     */
    public function get_pods_status($selected_cpt = '') {
        $status = [
            'pods_available' => $this->is_pods_available(),
            'available_cpts' => [],
            'selected_cpt_valid' => false,
            'posts_count' => 0,
            'fields_count' => 0,
            'taxonomies_count' => 0
        ];

        if (!$status['pods_available']) {
            return $status;
        }

        // Get available CPTs
        $status['available_cpts'] = $this->get_available_cpts();
        
        if (!empty($selected_cpt)) {
            $status['selected_cpt_valid'] = $this->is_valid_cpt($selected_cpt);
            
            if ($status['selected_cpt_valid']) {
                // Get post count
                $pod_posts = $this->get_pod_posts($selected_cpt, 1, 1, '', []);
                if (!is_wp_error($pod_posts)) {
                    $status['posts_count'] = $pod_posts['total'];
                }
                
                // Get fields count
                $fields = $this->get_pod_fields($selected_cpt);
                $status['fields_count'] = count($fields);
                
                // Get taxonomies count
                $taxonomies = $this->get_pod_taxonomies($selected_cpt);
                $status['taxonomies_count'] = count($taxonomies);
            }
        }

        return $status;
    }

    /**
     * Get post description based on widget settings
     * 
     * @param WP_Post $post
     * @param array $settings
     * @return string
     */
    public function get_post_description($post, $settings) {
        // Check if description should be shown
        $show_description = $settings['show_description'] ?? 'yes';
        if ($show_description !== 'yes') {
            return '';
        }
        
        $description_field = $settings['description_field'] ?? 'content';
        $custom_field_name = $settings['custom_description_field'] ?? '';
        
        $description = '';
        
        switch ($description_field) {
            case 'content':
                $description = wp_strip_all_tags($post->post_content);
                // Apply length limit only for content field
                $max_length = intval($settings['description_length'] ?? 50);
                if (!empty($description) && strlen($description) > $max_length) {
                    $description = substr($description, 0, $max_length) . '...';
                }
                break;
                
            case 'custom_field':
                if (!empty($custom_field_name)) {
                    $custom_value = get_post_meta($post->ID, $custom_field_name, true);
                    if (!empty($custom_value)) {
                        $description = is_string($custom_value) ? $custom_value : '';
                    }
                }
                // Custom fields are displayed as-is (no length truncation)
                break;
                
            default:
                $description = wp_strip_all_tags($post->post_content);
                // Apply length limit for fallback content
                $max_length = intval($settings['description_length'] ?? 50);
                if (!empty($description) && strlen($description) > $max_length) {
                    $description = substr($description, 0, $max_length) . '...';
                }
        }
        
        // Fallback to content if custom field is empty
        if (empty($description) && $description_field === 'custom_field') {
            $description = wp_strip_all_tags($post->post_content);
            // Apply default length limit for fallback content
            $max_length = intval($settings['description_length'] ?? 50);
            if (!empty($description) && strlen($description) > $max_length) {
                $description = substr($description, 0, $max_length) . '...';
            }
        }
        
        return trim($description);
    }

    /**
     * Get unique values for a specific custom field from current result set
     * 
     * @param string $cpt_name
     * @param string $field_name
     * @param array $current_filters Additional filters to apply
     * @param string $search_term Search term to filter by
     * @return array Array of unique values with counts
     */
    public function get_field_values_with_counts($cpt_name, $field_name, $current_filters = [], $search_term = '') {
        if (!$this->is_pods_available() || empty($cpt_name) || empty($field_name)) {
            return [];
        }

        try {
            // Build query arguments
            $args = [
                'post_type' => $cpt_name,
                'posts_per_page' => -1, // Get all posts to count values
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => $field_name,
                        'value' => '',
                        'compare' => '!='
                    ]
                ]
            ];

            // Apply search term if provided
            if (!empty($search_term)) {
                $args['s'] = sanitize_text_field($search_term);
            }

            // Apply current filters (excluding the field we're counting)
            if (!empty($current_filters)) {
                $meta_queries = [];
                foreach ($current_filters as $filter_field => $filter_values) {
                    // Skip the field we're currently getting values for
                    if ($filter_field === $field_name) {
                        continue;
                    }
                    
                    if (!empty($filter_values) && is_array($filter_values)) {
                        if (count($filter_values) === 1) {
                            $meta_queries[] = [
                                'key' => $filter_field,
                                'value' => $filter_values[0],
                                'compare' => '='
                            ];
                        } else {
                            $meta_queries[] = [
                                'key' => $filter_field,
                                'value' => $filter_values,
                                'compare' => 'IN'
                            ];
                        }
                    }
                }
                
                if (!empty($meta_queries)) {
                    if (isset($args['meta_query'])) {
                        $args['meta_query'] = array_merge($args['meta_query'], $meta_queries);
                        $args['meta_query']['relation'] = 'AND';
                    } else {
                        $args['meta_query'] = $meta_queries;
                    }
                }
            }

            // Execute query
            $query = new WP_Query($args);
            $field_values = [];

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $value = get_post_meta(get_the_ID(), $field_name, true);
                    
                    if (!empty($value)) {
                        // Handle array values (like multi-select fields)
                        if (is_array($value)) {
                            foreach ($value as $single_value) {
                                $clean_value = is_string($single_value) ? trim($single_value) : $single_value;
                                if (!empty($clean_value)) {
                                    $field_values[$clean_value] = ($field_values[$clean_value] ?? 0) + 1;
                                }
                            }
                        } else {
                            $clean_value = is_string($value) ? trim($value) : $value;
                            if (!empty($clean_value)) {
                                $field_values[$clean_value] = ($field_values[$clean_value] ?? 0) + 1;
                            }
                        }
                    }
                }
                wp_reset_postdata();
            }

            // Sort by key (alphabetical) and return
            ksort($field_values);
            return $field_values;

        } catch (Exception $e) {
            error_log('Smart Gallery - Error getting field values: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available field values for multiple fields
     * 
     * @param string $cpt_name
     * @param array $field_names Array of field names to get values for
     * @param array $current_filters Current filter state
     * @param string $search_term Current search term
     * @return array Multi-dimensional array of field values with counts
     */
    public function get_multiple_field_values($cpt_name, $field_names, $current_filters = [], $search_term = '') {
        $all_field_values = [];
        
        if (!$this->is_pods_available() || empty($cpt_name) || empty($field_names)) {
            return $all_field_values;
        }

        foreach ($field_names as $field_name) {
            $field_values = $this->get_field_values_with_counts(
                $cpt_name, 
                $field_name, 
                $current_filters, 
                $search_term
            );
            
            if (!empty($field_values)) {
                $all_field_values[$field_name] = $field_values;
            }
        }

        return $all_field_values;
    }

    /**
     * Apply custom field filters to post query arguments
     * 
     * @param array $args WP_Query arguments
     * @param array $filters Array of field filters [field_name => [values]]
     * @return array Modified query arguments
     */
    public function apply_custom_field_filters($args, $filters) {
        if (empty($filters)) {
            return $args;
        }

        $meta_queries = [];
        
        foreach ($filters as $field_name => $values) {
            if (empty($values) || !is_array($values)) {
                continue;
            }
            
            // Clean up values
            $clean_values = array_filter(array_map('sanitize_text_field', $values));
            
            if (empty($clean_values)) {
                continue;
            }
            
            // Single value = exact match, multiple values = IN query
            if (count($clean_values) === 1) {
                $meta_queries[] = [
                    'key' => sanitize_key($field_name),
                    'value' => $clean_values[0],
                    'compare' => '='
                ];
            } else {
                $meta_queries[] = [
                    'key' => sanitize_key($field_name),
                    'value' => $clean_values,
                    'compare' => 'IN'
                ];
            }
        }

        if (!empty($meta_queries)) {
            if (isset($args['meta_query'])) {
                $args['meta_query'] = array_merge($args['meta_query'], $meta_queries);
                $args['meta_query']['relation'] = 'AND';
            } else {
                $args['meta_query'] = $meta_queries;
                $args['meta_query']['relation'] = 'AND';
            }
        }

        return $args;
    }

    /**
     * Apply taxonomy filters to WP_Query args (F3.3)
     * 
     * @param array $args
     * @param array $taxonomy_filters
     * @return array
     */
    private function apply_taxonomy_filters($args, $taxonomy_filters) {
        if (empty($taxonomy_filters)) {
            return $args;
        }

        $tax_queries = [];

        foreach ($taxonomy_filters as $taxonomy => $term_ids) {
            if (!empty($term_ids) && is_array($term_ids)) {
                // Sanitize term IDs
                $clean_term_ids = array_map('intval', $term_ids);
                $clean_term_ids = array_filter($clean_term_ids, function($id) { return $id > 0; });

                if (!empty($clean_term_ids)) {
                    $tax_queries[] = [
                        'taxonomy' => sanitize_key($taxonomy),
                        'field' => 'term_id',
                        'terms' => $clean_term_ids,
                        'operator' => 'IN' // OR logic within same taxonomy
                    ];
                }
            }
        }

        if (!empty($tax_queries)) {
            if (isset($args['tax_query'])) {
                $args['tax_query'] = array_merge($args['tax_query'], $tax_queries);
                $args['tax_query']['relation'] = 'AND'; // AND logic between different taxonomies
            } else {
                $args['tax_query'] = $tax_queries;
                $args['tax_query']['relation'] = 'AND';
            }
        }

        return $args;
    }

    /**
     * Get taxonomy terms with counts for multiple taxonomies (F3.3)
     * 
     * @param string $cpt_name
     * @param array $taxonomies
     * @param array $custom_field_filters
     * @param array $current_taxonomy_filters
     * @param string $search_term
     * @return array
     */
    public function get_multiple_taxonomy_terms($cpt_name, $taxonomies, $custom_field_filters = [], $current_taxonomy_filters = [], $search_term = '') {
        if (empty($cpt_name) || empty($taxonomies) || !$this->is_pods_available()) {
            return [];
        }

        $results = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = $this->get_taxonomy_terms_with_counts($cpt_name, $taxonomy, $custom_field_filters, $current_taxonomy_filters, $search_term);
            if (!empty($terms)) {
                $results[$taxonomy] = $terms;
            }
        }

        return $results;
    }

    /**
     * Get taxonomy terms with hierarchical structure and counts (F3.3)
     * 
     * @param string $cpt_name
     * @param string $taxonomy_name
     * @param array $custom_field_filters
     * @param array $current_taxonomy_filters
     * @param string $search_term
     * @return array
     */
    public function get_taxonomy_terms_with_counts($cpt_name, $taxonomy_name, $custom_field_filters = [], $current_taxonomy_filters = [], $search_term = '') {
        if (empty($cpt_name) || empty($taxonomy_name)) {
            return [];
        }

        // Ensure parameters are arrays
        if (!is_array($custom_field_filters)) {
            $custom_field_filters = [];
        }
        if (!is_array($current_taxonomy_filters)) {
            $current_taxonomy_filters = [];
        }

        try {
            // Build query args to determine which posts are available with current filters
            $query_args = [
                'post_type' => $cpt_name,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => []
            ];

            // Apply search
            if (!empty($search_term)) {
                $query_args['s'] = sanitize_text_field($search_term);
            }

            // Apply custom field filters
            if (!empty($custom_field_filters)) {
                $query_args = $this->apply_custom_field_filters($query_args, $custom_field_filters);
            }

            // Apply taxonomy filters from OTHER taxonomies (not the current one we're building)
            $other_taxonomy_filters = $current_taxonomy_filters;
            unset($other_taxonomy_filters[$taxonomy_name]); // Remove current taxonomy from filters

            if (!empty($other_taxonomy_filters)) {
                $query_args = $this->apply_taxonomy_filters($query_args, $other_taxonomy_filters);
            }

            // Get post IDs that match all current filters
            $query = new WP_Query($query_args);
            $post_ids = $query->posts;
            wp_reset_postdata();

            if (empty($post_ids)) {
                return [];
            }

            // Get all terms for this taxonomy
            $terms = get_terms([
                'taxonomy' => $taxonomy_name,
                'hide_empty' => false,
                'object_ids' => $post_ids // Only get terms that are used by our filtered posts
            ]);

            if (is_wp_error($terms) || empty($terms)) {
                return [];
            }

            // Build hierarchical structure with counts
            $term_tree = $this->build_hierarchical_terms($terms, $post_ids);

            return $term_tree;

        } catch (Exception $e) {
            error_log('Smart Gallery - Taxonomy terms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build hierarchical term structure with counts
     * 
     * @param array $terms
     * @param array $post_ids
     * @param int $parent_id
     * @return array
     */
    private function build_hierarchical_terms($terms, $post_ids, $parent_id = 0) {
        $result = [];

        foreach ($terms as $term) {
            if ($term->parent == $parent_id) {
                // Get count for this term
                $term_count = $this->get_term_post_count($term, $post_ids);
                
                if ($term_count > 0) { // Only include terms that have posts
                    $term_data = [
                        'term_id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'count' => $term_count,
                        'children' => []
                    ];

                    // Get children recursively
                    $children = $this->build_hierarchical_terms($terms, $post_ids, $term->term_id);
                    if (!empty($children)) {
                        $term_data['children'] = $children;
                    }

                    $result[] = $term_data;
                }
            }
        }

        return $result;
    }

    /**
     * Get count of posts for a specific term
     * 
     * @param object $term
     * @param array $post_ids
     * @return int
     */
    private function get_term_post_count($term, $post_ids) {
        $term_posts = get_objects_in_term($term->term_id, $term->taxonomy);
        
        if (is_wp_error($term_posts)) {
            return 0;
        }

        // Count intersection of term posts and our filtered posts
        $intersection = array_intersect($term_posts, $post_ids);
        return count($intersection);
    }
}
