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
     * Get posts from selected Pod/CPT
     * 
     * @param string $cpt_name
     * @param int $posts_per_page
     * @param int $paged
     * @return array|WP_Error
     */
    public function get_pod_posts($cpt_name, $posts_per_page = 12, $paged = 1) {
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

            $query = new WP_Query($query_args);
            
            if ($query->have_posts()) {
                $posts_data = [
                    'posts' => $query->posts,
                    'total' => $query->found_posts,
                    'pages' => $query->max_num_pages,
                    'current_page' => $paged
                ];
                
                wp_reset_postdata();
                return $posts_data;
            } else {
                wp_reset_postdata();
                return new WP_Error('no_posts', 'No posts found');
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
                $pod_posts = $this->get_pod_posts($selected_cpt, 1, 1);
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
}
