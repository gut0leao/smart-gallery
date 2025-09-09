<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Elementor_Smart_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'smart_gallery';
    }

    public function get_title() {
        return 'Smart Gallery';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        // Main Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Content Settings',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label' => 'Post Type (CPT)',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'car',
                'options' => $this->get_pods_post_types(),
                'description' => 'Select which Custom Post Type to display in the gallery',
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => 'Posts per Page',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
                'description' => 'Number of items to display',
            ]
        );

        $this->end_controls_section();

        // Grid Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => 'Grid Layout',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => 'Columns',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns',
                ],
            ]
        );

        $this->add_control(
            'gap',
            [
                'label' => 'Gap',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
            ]
        );

        $this->add_control(
            'enable_pagination',
            [
                'label' => 'Enable Pagination',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'no',
                'description' => 'Enable pagination for large galleries',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pagination_type',
            [
                'label' => 'Pagination Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'numbers',
                'options' => [
                    'numbers' => 'Page Numbers',
                    'prev_next' => 'Previous/Next Only',
                    'load_more' => 'Load More Button',
                    'infinite' => 'Infinite Scroll',
                ],
                'condition' => ['enable_pagination' => 'yes'],
            ]
        );

        $this->add_control(
            'items_per_page',
            [
                'label' => 'Items per Page',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
                'condition' => ['enable_pagination' => 'yes'],
                'description' => 'Number of items to show per page (overrides Posts per Page when pagination is enabled)',
            ]
        );

        $this->end_controls_section();

        // Image Settings Section
        $this->start_controls_section(
            'image_section',
            [
                'label' => 'Image Settings',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => 'Image Size',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'medium',
                'options' => [
                    'thumbnail' => 'Thumbnail (150x150)',
                    'medium' => 'Medium (300x300)',
                    'medium_large' => 'Medium Large (768x768)',
                    'large' => 'Large (1024x1024)',
                    'full' => 'Full Size',
                ],
            ]
        );

        $this->add_control(
            'image_ratio',
            [
                'label' => 'Image Aspect Ratio',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1:1',
                'options' => [
                    '1:1' => '1:1 (Square)',
                    '4:3' => '4:3 (Standard)',
                    '16:9' => '16:9 (Widescreen)',
                    '3:4' => '3:4 (Portrait)',
                    'auto' => 'Auto (Original)',
                ],
            ]
        );

        $this->end_controls_section();

        // Filter Settings Section
        $this->start_controls_section(
            'filter_section',
            [
                'label' => 'Filter Settings',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_filters',
            [
                'label' => 'Enable Filters',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'filter_fields',
            [
                'label' => 'Filter Fields',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_pods_fields(),
                'default' => [],
                'description' => 'Select which custom fields to use as filters',
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_taxonomies',
            [
                'label' => 'Filter Taxonomies',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_post_type_taxonomies(),
                'default' => [],
                'description' => 'Select which taxonomies to use as filters',
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_position',
            [
                'label' => 'Filter Position',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => 'Left Sidebar',
                    'right' => 'Right Sidebar',
                    'top' => 'Top Bar',
                ],
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Messages Section
        $this->start_controls_section(
            'messages_section',
            [
                'label' => 'Custom Messages',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'empty_message',
            [
                'label' => 'Empty Results Message',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'No items found for selected Pod.',
                'description' => 'Message displayed when no posts are found',
                'placeholder' => 'Enter your custom message...',
            ]
        );

        $this->add_control(
            'no_filters_message',
            [
                'label' => 'No Filter Results Message',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'No items match your current filters. Try adjusting or clearing your filters.',
                'description' => 'Message displayed when filters return zero results',
                'placeholder' => 'Enter your custom message...',
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'config_message',
            [
                'label' => 'Configuration Message',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Please select a Post Type in the widget settings to display the gallery.',
                'description' => 'Message displayed when widget is not properly configured',
                'placeholder' => 'Enter your custom message...',
            ]
        );

        $this->add_control(
            'show_message_icon',
            [
                'label' => 'Show Message Icon',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => 'Show an icon with the message',
            ]
        );

        $this->add_control(
            'message_style',
            [
                'label' => 'Message Style',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'info',
                'options' => [
                    'info' => 'Info (Blue)',
                    'warning' => 'Warning (Yellow)',
                    'error' => 'Error (Red)',
                    'success' => 'Success (Green)',
                    'neutral' => 'Neutral (Gray)',
                ],
                'description' => 'Visual style for the message box',
            ]
        );

        $this->end_controls_section();

        // Search Settings Section
        $this->start_controls_section(
            'search_section',
            [
                'label' => 'Search Settings',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_search',
            [
                'label' => 'Enable Search',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'search_position',
            [
                'label' => 'Search Position',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'top',
                'options' => [
                    'top' => 'Top (Above Gallery)',
                    'filter-top' => 'Filter Sidebar Top',
                ],
                'condition' => [
                    'enable_search' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'search_fields',
            [
                'label' => 'Search Fields',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_text_fields(),
                'default' => ['post_title', 'post_content'],
                'description' => 'Select which fields to include in search. Only text fields are supported.',
                'condition' => [
                    'enable_search' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'search_placeholder',
            [
                'label' => 'Search Placeholder',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Search ...',
                'description' => 'Placeholder text for the search input',
                'condition' => [
                    'enable_search' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'search_button_text',
            [
                'label' => 'Search Button Text',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Search',
                'description' => 'Text for the search button (leave empty to hide button)',
                'condition' => [
                    'enable_search' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'search_live',
            [
                'label' => 'Live Search',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => 'Search as user types (without button click)',
                'condition' => [
                    'enable_search' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Plugin Information Section
        $this->start_controls_section(
            'info_section',
            [
                'label' => 'Plugin Information',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'plugin_info',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #007cba;">
                        <h4 style="margin: 0 0 10px 0; color: #007cba;">🎯 Smart Gallery</h4>
                        <p style="margin: 0 0 10px 0; font-size: 13px; line-height: 1.4;">
                            Free alternative to Elementor Pro Posts widget with advanced filtering and pagination.
                        </p>
                        
                        <h5 style="margin: 10px 0 5px 0; font-size: 12px; color: #666;">Required Dependencies:</h5>
                        <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #666;">
                            <li><strong>Elementor</strong> ✅</li>
                            <li><strong>Pods Framework</strong> ' . (function_exists('pods_api') ? '✅' : '❌ <a href="' . admin_url('plugin-install.php?s=pods&tab=search&type=term') . '" target="_blank">Install</a>') . '</li>
                        </ul>
                        
                        <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                            <a href="https://github.com/gut0leao/smart-gallery" target="_blank">Documentation & Support</a>
                        </p>
                    </div>
                ',
            ]
        );

        $this->end_controls_section();
    }

    private function get_pods_post_types() {
        $options = ['' => 'Select a Post Type'];
        
        if (!function_exists('pods_api')) {
            return $options;
        }
        
        $api = pods_api();
        $pods = $api->load_pods(['type' => 'post_type']);
        
        foreach ($pods as $pod) {
            $options[$pod['name']] = $pod['label'];
        }
        
        // Also include built-in post types that might be extended by Pods
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $post_type) {
            if (!isset($options[$post_type->name])) {
                $options[$post_type->name] = $post_type->label;
            }
        }
        
        return $options;
    }

    private function get_pods_fields() {
        $options = [];
        
        if (!function_exists('pods_api')) {
            return $options;
        }
        
        // Get current post type from settings if available
        $current_post_type = 'car'; // Default fallback
        
        $api = pods_api();
        $pod = $api->load_pod(['name' => $current_post_type]);
        
        if ($pod && !empty($pod['fields'])) {
            foreach ($pod['fields'] as $field_name => $field_data) {
                // Only include filterable field types
                $filterable_types = ['pick', 'number', 'currency', 'date', 'datetime', 'time'];
                if (in_array($field_data['type'], $filterable_types)) {
                    $options[$field_name] = $field_data['label'] . ' (' . $field_data['type'] . ')';
                }
            }
        }
        
        return $options;
    }

    private function get_post_type_taxonomies() {
        $options = [];
        $current_post_type = 'car'; // Default fallback
        
        $taxonomies = get_object_taxonomies($current_post_type, 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->public || $taxonomy->show_ui) {
                $options[$taxonomy->name] = $taxonomy->label;
            }
        }
        
        return $options;
    }

    private function get_text_fields() {
        $options = [];
        
        // Default WordPress fields
        $options['post_title'] = 'Post Title';
        $options['post_content'] = 'Post Content';
        $options['post_excerpt'] = 'Post Excerpt';
        
        if (!function_exists('pods_api')) {
            return $options;
        }
        
        // Get current post type from settings if available
        $current_post_type = 'car'; // Default fallback
        
        $api = pods_api();
        $pod = $api->load_pod(['name' => $current_post_type]);
        
        if ($pod && !empty($pod['fields'])) {
            foreach ($pod['fields'] as $field_name => $field_data) {
                // Only include text-based field types
                $text_types = ['text', 'textarea', 'wysiwyg', 'code', 'email', 'phone', 'password', 'slug', 'color'];
                if (in_array($field_data['type'], $text_types)) {
                    $options[$field_name] = $field_data['label'] . ' (Pods)';
                }
            }
        }
        
        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['post_type'])) {
            $this->render_message($settings['config_message'], 'config', $settings);
            return;
        }
        
        $query_data = $this->get_gallery_posts($settings);
        $posts = $query_data['posts'];
        
        if (empty($posts)) {
            $this->render_message($settings['empty_message'], 'empty', $settings);
            return;
        }
        
        // Generate unique ID for this widget instance
        $widget_id = 'sgf-' . $this->get_id();
        
        // Render the complete gallery with filters
        $this->render_gallery_with_filters($posts, $settings, $widget_id, $query_data);
        
        // Add no-results message for filters (hidden by default)
        if ($settings['enable_filters'] === 'yes') {
            echo '<div class="sgf-no-filter-results" id="no-results-' . $widget_id . '" style="display: none;">';
            $this->render_message($settings['no_filters_message'], 'no-filters', $settings, false);
            echo '</div>';
        }
    }
    
    private function render_gallery_with_filters($posts, $settings, $widget_id, $query_data = null) {
        $enable_filters = $settings['enable_filters'] === 'yes';
        $enable_search = $settings['enable_search'] === 'yes';
        $enable_pagination = $settings['enable_pagination'] === 'yes';
        $filter_position = $settings['filter_position'] ?? 'left';
        $search_position = $settings['search_position'] ?? 'top';
        
        // Render search at top OUTSIDE the main container
        if ($enable_search && $search_position === 'top') {
            $this->render_search($settings, $widget_id, 'top');
        }
        
        // Container class based on filter position
        $container_class = 'sgf-container';
        if ($enable_filters) {
            $container_class .= ' sgf-with-filters sgf-filters-' . $filter_position;
        }
        
        echo '<div class="' . $container_class . '" id="' . $widget_id . '">';
        
        // Render filters
        if ($enable_filters) {
            $this->render_filters($settings, $widget_id, $enable_search, $search_position);
        }
        
        // Render gallery
        echo '<div class="sgf-gallery-container">';
        $this->render_gallery($posts, $settings, $widget_id);
        echo '</div>';
        
        echo '</div>'; // Close main container
        
        // Render pagination outside but after the main container
        if ($enable_pagination && $query_data) {
            $this->render_pagination($query_data, $settings, $widget_id);
        }
        
        // Add JavaScript for filtering and search
        if ($enable_filters || $enable_search) {
            $this->render_filter_and_search_script($widget_id, $settings);
        }
    }
    
    private function render_filters($settings, $widget_id, $enable_search = false, $search_position = 'top') {
        $filter_fields = $settings['filter_fields'] ?? [];
        $filter_taxonomies = $settings['filter_taxonomies'] ?? [];
        
        if (empty($filter_fields) && empty($filter_taxonomies) && !($enable_search && $search_position === 'filter-top')) {
            return;
        }
        
        echo '<div class="sgf-filters-sidebar">';
        echo '<div class="sgf-filters-header">';
        echo '<h4>Filters</h4>';
        echo '<button type="button" class="sgf-clear-filters" data-target="' . $widget_id . '">Clear All</button>';
        echo '</div>';
        
        // Search at filter top
        if ($enable_search && $search_position === 'filter-top') {
            $this->render_search($settings, $widget_id, 'filter-top');
        }
        
        // Taxonomy filters
        foreach ($filter_taxonomies as $taxonomy) {
            $this->render_taxonomy_filter($taxonomy, $widget_id);
        }
        
        // Custom field filters
        foreach ($filter_fields as $field) {
            $this->render_field_filter($field, $settings['post_type'], $widget_id);
        }
        
        echo '</div>';
    }
    
    private function render_search($settings, $widget_id, $position) {
        $placeholder = $settings['search_placeholder'] ?? 'Search items...';
        $button_text = $settings['search_button_text'] ?? '';
        $live_search = $settings['search_live'] === 'yes';
        $current_search = isset($_GET['sgf_search']) ? sanitize_text_field($_GET['sgf_search']) : '';
        
        $container_class = 'sgf-search-container sgf-search-' . $position;
        
        echo '<div class="' . $container_class . '">';
        
        if ($position === 'top') {
            echo '<div class="sgf-search-header">';
            echo '<h4>Search</h4>';
            echo '</div>';
        } elseif ($position === 'filter-top') {
            echo '<div class="sgf-search-group">';
            echo '<label class="sgf-filter-label">Search</label>';
        }
        
        echo '<form method="GET" class="sgf-search-form" style="position: relative;">';
        
        // Preserve existing GET parameters
        foreach ($_GET as $key => $value) {
            if ($key !== 'sgf_search' && $key !== 'sgf_page') {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }
        }
        
        echo '<div class="sgf-search-input-container" style="position: relative;">';
        echo '<input type="text" name="sgf_search" class="sgf-search-input" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($current_search) . '" style="padding-right: 35px;">';
        echo '<button type="submit" class="sgf-search-icon" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">';
        echo '<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>';
        echo '</svg>';
        echo '</button>';
        echo '</div>';
        
        echo '</form>';
        
        if ($position === 'filter-top') {
            echo '</div>';
        }
        
        echo '</div>';
    }

    private function render_taxonomy_filter($taxonomy, $widget_id) {
        $taxonomy_obj = get_taxonomy($taxonomy);
        if (!$taxonomy_obj) return;
        
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ]);
        
        if (empty($terms) || is_wp_error($terms)) return;
        
        echo '<div class="sgf-filter-group">';
        echo '<label class="sgf-filter-label">' . esc_html($taxonomy_obj->label) . '</label>';
        echo '<select class="sgf-filter-select" data-filter="taxonomy" data-taxonomy="' . esc_attr($taxonomy) . '" data-target="' . $widget_id . '">';
        echo '<option value="">All ' . esc_html($taxonomy_obj->label) . '</option>';
        
        foreach ($terms as $term) {
            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . ' (' . $term->count . ')</option>';
        }
        
        echo '</select>';
        echo '</div>';
    }
    
    private function render_field_filter($field, $post_type, $widget_id) {
        if (!function_exists('pods_api')) return;
        
        $api = pods_api();
        $pod = $api->load_pod(['name' => $post_type]);
        
        if (!$pod || empty($pod['fields'][$field])) return;
        
        $field_data = $pod['fields'][$field];
        $field_type = $field_data['type'];
        
        echo '<div class="sgf-filter-group">';
        echo '<label class="sgf-filter-label">' . esc_html($field_data['label']) . '</label>';
        
        switch ($field_type) {
            case 'pick':
                $this->render_pick_field_filter($field, $field_data, $widget_id);
                break;
            case 'number':
            case 'currency':
                $this->render_number_field_filter($field, $field_data, $widget_id);
                break;
            default:
                $this->render_text_field_filter($field, $field_data, $widget_id);
                break;
        }
        
        echo '</div>';
    }
    
    private function render_pick_field_filter($field, $field_data, $widget_id) {
        // Get unique values for this pick field
        global $wpdb;
        $values = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value",
            $field
        ));
        
        if (empty($values)) return;
        
        echo '<select class="sgf-filter-select" data-filter="field" data-field="' . esc_attr($field) . '" data-target="' . $widget_id . '">';
        echo '<option value="">All ' . esc_html($field_data['label']) . '</option>';
        
        foreach ($values as $value) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
        }
        
        echo '</select>';
    }
    
    private function render_number_field_filter($field, $field_data, $widget_id) {
        // Get min/max values for range filter
        global $wpdb;
        $minmax = $wpdb->get_row($wpdb->prepare(
            "SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_val, MAX(CAST(meta_value AS UNSIGNED)) as max_val 
             FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' AND meta_value REGEXP '^[0-9]+$'",
            $field
        ));
        
        if (!$minmax) return;
        
        echo '<div class="sgf-range-filter">';
        echo '<input type="number" class="sgf-filter-range-min" placeholder="Min" data-filter="range" data-field="' . esc_attr($field) . '" data-type="min" data-target="' . $widget_id . '" min="' . $minmax->min_val . '" max="' . $minmax->max_val . '">';
        echo '<input type="number" class="sgf-filter-range-max" placeholder="Max" data-filter="range" data-field="' . esc_attr($field) . '" data-type="max" data-target="' . $widget_id . '" min="' . $minmax->min_val . '" max="' . $minmax->max_val . '">';
        echo '</div>';
    }
    
    private function render_text_field_filter($field, $field_data, $widget_id) {
        echo '<input type="text" class="sgf-filter-text" placeholder="Search ' . esc_attr($field_data['label']) . '" data-filter="text" data-field="' . esc_attr($field) . '" data-target="' . $widget_id . '">';
    }
    
    private function get_gallery_posts($settings) {
        $current_page = isset($_GET['sgf_page']) ? max(1, intval($_GET['sgf_page'])) : 1;
        $search_term = isset($_GET['sgf_search']) ? sanitize_text_field($_GET['sgf_search']) : '';
        
        $items_per_page = $settings['enable_pagination'] === 'yes' 
            ? $settings['items_per_page'] 
            : $settings['posts_per_page'];
        
        $args = [
            'post_type' => $settings['post_type'],
            'post_status' => 'publish',
            'posts_per_page' => $items_per_page,
            'paged' => $current_page,
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS'
                ]
            ]
        ];
        
        // Add search functionality
        if (!empty($search_term)) {
            $search_fields = $settings['search_fields'] ?? ['post_title', 'post_content'];
            
            // Prepare meta query for custom fields
            $meta_query = ['relation' => 'OR'];
            $has_meta_search = false;
            
            foreach ($search_fields as $field) {
                if (in_array($field, ['post_title', 'post_content', 'post_excerpt'])) {
                    // WordPress default fields - use 's' parameter
                    $args['s'] = $search_term;
                } else {
                    // Custom fields - use meta query
                    $meta_query[] = [
                        'key' => $field,
                        'value' => $search_term,
                        'compare' => 'LIKE'
                    ];
                    $has_meta_search = true;
                }
            }
            
            // Add meta query if we have custom field searches
            if ($has_meta_search) {
                if (isset($args['s'])) {
                    // Combine title/content search with meta search
                    $args['_meta_query'] = $meta_query;
                    add_filter('posts_where', [$this, 'modify_search_where'], 10, 2);
                } else {
                    // Only meta search
                    $args['meta_query'][] = $meta_query;
                }
            }
        }
        
        $query = new WP_Query($args);
        
        // Remove filter after query
        if (!empty($search_term) && isset($args['_meta_query'])) {
            remove_filter('posts_where', [$this, 'modify_search_where'], 10);
        }
        
        return [
            'posts' => $query->posts,
            'total_pages' => $query->max_num_pages,
            'current_page' => $current_page,
            'total_posts' => $query->found_posts,
            'items_per_page' => $items_per_page,
            'search_term' => $search_term
        ];
    }
    
    public function modify_search_where($where, $wp_query) {
        global $wpdb;
        
        if (!empty($wp_query->query_vars['_meta_query'])) {
            $meta_query = $wp_query->query_vars['_meta_query'];
            $search_term = $wp_query->query_vars['s'];
            
            // Build meta query part
            $meta_where_parts = [];
            foreach ($meta_query as $meta_condition) {
                if (is_array($meta_condition) && isset($meta_condition['key'])) {
                    $meta_where_parts[] = $wpdb->prepare(
                        "({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s)",
                        $meta_condition['key'],
                        '%' . $wpdb->esc_like($search_term) . '%'
                    );
                }
            }
            
            if (!empty($meta_where_parts)) {
                $meta_where = '(' . implode(' OR ', $meta_where_parts) . ')';
                // Combine with existing search where using OR
                $where = str_replace(
                    'AND ((',
                    "AND (({$meta_where}) OR (",
                    $where
                );
            }
        }
        
        return $where;
    }
    
    private function render_gallery($posts, $settings, $widget_id = '') {
        $columns = $settings['columns'];
        $gap = $settings['gap']['size'];
        $image_size = $settings['image_size'];
        $image_ratio = $settings['image_ratio'];
        
        // CSS for aspect ratio
        $ratio_css = '';
        switch ($image_ratio) {
            case '1:1':
                $ratio_css = 'aspect-ratio: 1/1;';
                break;
            case '4:3':
                $ratio_css = 'aspect-ratio: 4/3;';
                break;
            case '16:9':
                $ratio_css = 'aspect-ratio: 16/9;';
                break;
            case '3:4':
                $ratio_css = 'aspect-ratio: 3/4;';
                break;
            case 'auto':
                $ratio_css = 'height: auto;';
                break;
        }
        
        echo '<div class="smart-gallery-grid sgf-gallery" data-widget="' . esc_attr($widget_id) . '" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . $gap . 'px;">';
        
        foreach ($posts as $post) {
            $this->render_gallery_item($post, $image_size, $ratio_css, $settings);
        }
        
        echo '</div>';
        
        // Add CSS for hover effects
        $this->render_gallery_styles();
    }
    
    private function render_gallery_item($post, $image_size, $ratio_css, $settings) {
        $thumbnail_url = get_the_post_thumbnail_url($post->ID, $image_size);
        $post_title = get_the_title($post->ID);
        $post_url = get_permalink($post->ID);
        
        // Get post data for filtering and searching
        $post_data = $this->get_post_filter_data($post->ID, $settings);
        $search_data = $this->get_post_search_data($post->ID, $settings);
        
        $data_attributes = $this->build_data_attributes($post_data);
        $search_attributes = $this->build_search_attributes($search_data);
        
        echo '<div class="gallery-item" ' . $data_attributes . ' ' . $search_attributes . ' style="position: relative; overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease;">';
        echo '<a href="' . esc_url($post_url) . '" style="display: block; text-decoration: none;">';
        echo '<div class="gallery-image" style="' . $ratio_css . ' overflow: hidden;">';
        echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($post_title) . '" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">';
        echo '</div>';
        echo '<div class="gallery-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; padding: 15px 10px 10px; opacity: 0; transition: opacity 0.3s ease;">';
        echo '<h4 style="margin: 0; font-size: 14px; font-weight: 600;">' . esc_html($post_title) . '</h4>';
        
        // Add some meta info in overlay
        if (!empty($post_data)) {
            echo '<div class="gallery-meta" style="font-size: 12px; margin-top: 5px; opacity: 0.9;">';
            foreach (array_slice($post_data, 0, 2) as $key => $value) {
                if (!empty($value) && !is_array($value)) {
                    echo '<span>' . esc_html($value) . '</span> ';
                }
            }
            echo '</div>';
        }
        
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
    
    private function get_post_filter_data($post_id, $settings) {
        $data = [];
        
        // Get taxonomy data
        if (!empty($settings['filter_taxonomies'])) {
            foreach ($settings['filter_taxonomies'] as $taxonomy) {
                $terms = wp_get_post_terms($post_id, $taxonomy);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $data['taxonomy_' . $taxonomy] = wp_list_pluck($terms, 'term_id');
                }
            }
        }
        
        // Get custom field data
        if (!empty($settings['filter_fields'])) {
            foreach ($settings['filter_fields'] as $field) {
                $value = get_post_meta($post_id, $field, true);
                if (!empty($value)) {
                    $data['field_' . $field] = $value;
                }
            }
        }
        
        return $data;
    }
    
    private function get_post_search_data($post_id, $settings) {
        $data = [];
        $search_fields = $settings['search_fields'] ?? ['post_title', 'post_content'];
        
        foreach ($search_fields as $field) {
            switch ($field) {
                case 'post_title':
                    $data[$field] = get_the_title($post_id);
                    break;
                case 'post_content':
                    $data[$field] = wp_strip_all_tags(get_post_field('post_content', $post_id));
                    break;
                case 'post_excerpt':
                    $data[$field] = wp_strip_all_tags(get_post_field('post_excerpt', $post_id));
                    break;
                default:
                    // Custom field
                    $value = get_post_meta($post_id, $field, true);
                    if (!empty($value) && is_string($value)) {
                        $data[$field] = wp_strip_all_tags($value);
                    }
                    break;
            }
        }
        
        return $data;
    }
    
    private function build_search_attributes($search_data) {
        // Combine all search data into one searchable string
        $search_text = implode(' ', array_filter($search_data, 'strlen'));
        return 'data-search-text="' . esc_attr(strtolower($search_text)) . '"';
    }
    
    private function build_data_attributes($post_data) {
        $attributes = [];
        
        foreach ($post_data as $key => $value) {
            if (is_array($value)) {
                $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr(implode(',', $value)) . '"';
            } else {
                $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
        
        return implode(' ', $attributes);
    }
    
    private function render_gallery_styles() {
        echo '<style>
        .gallery-item:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15) !important;
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1 !important;
        }
        .gallery-item:hover img {
            transform: scale(1.05) !important;
        }
        .gallery-item.sgf-hidden {
            display: none !important;
        }
        
        /* Pagination Styles */
        .sgf-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin: 30px 0;
            flex-wrap: wrap;
            width: 100%;
            clear: both;
        }
        
        /* Search Styles */
        .sgf-search-top {
            width: 100%;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .sgf-search-top .sgf-search-input-container {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
            align-items: center;
            position: relative;
        }
        
        .sgf-search-top .sgf-search-input {
            flex: 1;
            padding: 12px 16px;
            padding-right: 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .sgf-search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .sgf-search-icon:hover {
            background: #f0f0f0;
            color: #007cba;
        }
        
        .sgf-search-form {
            position: relative;
            width: 100%;
        }
        
        .sgf-search-top .sgf-search-button {
            padding: 12px 20px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        
        .sgf-search-top .sgf-search-button:hover {
            background: #005a87;
        }
        
        .sgf-search-header {
            margin-bottom: 15px;
        }
        
        .sgf-search-header h4 {
            margin: 0;
            text-align: center;
            font-size: 18px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .sgf-search-top {
                padding: 15px;
            }
            
            .sgf-search-top .sgf-search-input-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .sgf-search-top .sgf-search-input,
            .sgf-search-top .sgf-search-button {
                width: 100%;
            }
        }
        
        .sgf-page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-size: 14px;
            min-width: 40px;
        }
        
        .sgf-page-btn:hover {
            background: #f0f0f0;
            border-color: #007cba;
        }
        
        .sgf-page-btn.sgf-active {
            background: #007cba;
            color: white;
            border-color: #007cba;
        }
        
        .sgf-page-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .sgf-load-more-btn {
            padding: 12px 24px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        
        .sgf-load-more-btn:hover {
            background: #005a87;
        }
        
        .sgf-load-more-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #666;
        }
        
        .sgf-ellipsis {
            padding: 8px 4px;
            color: #666;
        }
        
        .sgf-pagination-info {
            padding: 8px 12px;
            font-size: 14px;
            color: #666;
        }
        
        .sgf-end-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        .sgf-loading-indicator {
            text-align: center;
            padding: 20px;
            color: #007cba;
        }
        
        @media (max-width: 768px) {
            .sgf-pagination {
                gap: 4px;
            }
            
            .sgf-page-btn {
                padding: 6px 8px;
                font-size: 12px;
                min-width: 32px;
            }
        }
        </style>';
    }
    
    private function render_filter_and_search_script($widget_id, $settings) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const widget = document.getElementById("' . $widget_id . '");
            const gallery = widget.querySelector(".sgf-gallery");
            const items = gallery.querySelectorAll(".gallery-item");
            const filters = widget.querySelectorAll(".sgf-filter-select, .sgf-filter-text, .sgf-filter-range-min, .sgf-filter-range-max");
            const clearButton = widget.querySelector(".sgf-clear-filters");
            
            // Combined filter functionality (client-side for filters, server-side for search)
            function filterItems() {
                const activeFilters = {};
                
                // Collect filter values
                filters.forEach(filter => {
                    const filterType = filter.dataset.filter;
                    const value = filter.value.trim();
                    
                    if (value) {
                        if (filterType === "taxonomy") {
                            activeFilters["taxonomy_" + filter.dataset.taxonomy] = value;
                        } else if (filterType === "field") {
                            activeFilters["field_" + filter.dataset.field] = value;
                        } else if (filterType === "range") {
                            const fieldKey = "field_" + filter.dataset.field;
                            if (!activeFilters[fieldKey]) activeFilters[fieldKey] = {};
                            activeFilters[fieldKey][filter.dataset.type] = parseInt(value);
                        }
                    }
                });
                
                let visibleCount = 0;
                
                items.forEach(item => {
                    let visible = true;
                    
                    // Apply filters (client-side)
                    for (const [key, filterValue] of Object.entries(activeFilters)) {
                        const itemValue = item.dataset[key.replace(/[^a-zA-Z0-9_]/g, "")];
                        
                        if (typeof filterValue === "object") {
                            // Range filter
                            const numValue = parseInt(itemValue);
                            if (filterValue.min && numValue < filterValue.min) visible = false;
                            if (filterValue.max && numValue > filterValue.max) visible = false;
                        } else if (key.startsWith("taxonomy_")) {
                            // Taxonomy filter
                            if (itemValue && !itemValue.split(",").includes(filterValue)) visible = false;
                        } else {
                            // Text/pick filter
                            if (itemValue && itemValue.toLowerCase().indexOf(filterValue.toLowerCase()) === -1) visible = false;
                        }
                        
                        if (!visible) break;
                    }
                    
                    item.classList.toggle("sgf-hidden", !visible);
                    if (visible) visibleCount++;
                });
                
                // Show/hide no results message
                const noResultsDiv = document.getElementById("no-results-' . $widget_id . '");
                if (noResultsDiv) {
                    if (visibleCount === 0 && Object.keys(activeFilters).length > 0) {
                        noResultsDiv.style.display = "block";
                    } else {
                        noResultsDiv.style.display = "none";
                    }
                }
            }
            
            // Add event listeners for filters only
            filters.forEach(filter => {
                filter.addEventListener("change", filterItems);
                filter.addEventListener("input", filterItems);
            });
            
            // Clear filters and search
            if (clearButton) {
                clearButton.addEventListener("click", function() {
                    filters.forEach(filter => {
                        filter.value = "";
                    });
                    
                    // Clear search from URL and reload
                    const url = new URL(window.location);
                    url.searchParams.delete("sgf_search");
                    url.searchParams.delete("sgf_page");
                    window.location.href = url.toString();
                });
            }
        });
        </script>';
    }

    private function render_message($message, $type, $settings, $with_container = true) {
        if (empty($message)) return;
        
        $show_icon = $settings['show_message_icon'] === 'yes';
        $style = $settings['message_style'] ?? 'info';
        
        // Message icons
        $icons = [
            'info' => 'eicon-info-circle',
            'warning' => 'eicon-warning',
            'error' => 'eicon-close-circle',
            'success' => 'eicon-check-circle',
            'neutral' => 'eicon-info',
            'config' => 'eicon-settings',
            'empty' => 'eicon-folder-open',
            'no-filters' => 'eicon-filter'
        ];
        
        // Get appropriate icon
        $icon = $icons[$type] ?? $icons[$style] ?? $icons['info'];
        
        $container_class = 'sgf-message sgf-message-' . $style;
        
        if ($with_container) {
            echo '<div class="' . $container_class . '">';
        } else {
            echo '<div class="' . $container_class . '" style="margin: 20px 0;">';
        }
        
        if ($show_icon) {
            echo '<div class="sgf-message-icon">';
            echo '<i class="' . esc_attr($icon) . '"></i>';
            echo '</div>';
        }
        
        echo '<div class="sgf-message-content">';
        echo '<p>' . wp_kses_post($message) . '</p>';
        echo '</div>';
        
        echo '</div>';
    }

    private function render_pagination($query_data, $settings, $widget_id) {
        if ($settings['enable_pagination'] !== 'yes' || $query_data['total_pages'] <= 1) {
            return;
        }
        
        $pagination_type = $settings['pagination_type'];
        $current_page = $query_data['current_page'];
        $total_pages = $query_data['total_pages'];
        
        echo '<div class="sgf-pagination sgf-pagination-' . $pagination_type . '" data-widget="' . $widget_id . '">';
        
        switch ($pagination_type) {
            case 'numbers':
                $this->render_numbered_pagination($current_page, $total_pages, $widget_id);
                break;
            case 'prev_next':
                $this->render_prev_next_pagination($current_page, $total_pages, $widget_id);
                break;
            case 'load_more':
                $this->render_load_more_pagination($current_page, $total_pages, $widget_id, $settings);
                break;
            case 'infinite':
                $this->render_infinite_scroll($current_page, $total_pages, $widget_id, $settings);
                break;
        }
        
        echo '</div>';
        
        // Add pagination JavaScript
        $this->render_pagination_script($widget_id, $settings, $query_data);
    }

    private function render_numbered_pagination($current_page, $total_pages, $widget_id) {
        // Previous button
        if ($current_page > 1) {
            echo '<button class="sgf-page-btn sgf-prev" data-page="' . ($current_page - 1) . '">‹ Previous</button>';
        }
        
        // Page numbers with ellipsis logic
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        if ($start > 1) {
            echo '<button class="sgf-page-btn" data-page="1">1</button>';
            if ($start > 2) echo '<span class="sgf-ellipsis">...</span>';
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $active_class = $i === $current_page ? ' sgf-active' : '';
            echo '<button class="sgf-page-btn' . $active_class . '" data-page="' . $i . '">' . $i . '</button>';
        }
        
        if ($end < $total_pages) {
            if ($end < $total_pages - 1) echo '<span class="sgf-ellipsis">...</span>';
            echo '<button class="sgf-page-btn" data-page="' . $total_pages . '">' . $total_pages . '</button>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            echo '<button class="sgf-page-btn sgf-next" data-page="' . ($current_page + 1) . '">Next ›</button>';
        }
    }

    private function render_prev_next_pagination($current_page, $total_pages, $widget_id) {
        echo '<div class="sgf-pagination-info">Page ' . $current_page . ' of ' . $total_pages . '</div>';
        
        if ($current_page > 1) {
            echo '<button class="sgf-page-btn sgf-prev" data-page="' . ($current_page - 1) . '">‹ Previous</button>';
        }
        
        if ($current_page < $total_pages) {
            echo '<button class="sgf-page-btn sgf-next" data-page="' . ($current_page + 1) . '">Next ›</button>';
        }
    }

    private function render_load_more_pagination($current_page, $total_pages, $widget_id, $settings) {
        if ($current_page < $total_pages) {
            $remaining_items = ($total_pages - $current_page) * $settings['items_per_page'];
            echo '<button class="sgf-load-more-btn" data-page="' . ($current_page + 1) . '" data-widget="' . $widget_id . '">';
            echo 'Load More Items (' . min($remaining_items, $settings['items_per_page']) . ' more)';
            echo '</button>';
        } else {
            echo '<div class="sgf-end-message">All items loaded</div>';
        }
    }

    private function render_infinite_scroll($current_page, $total_pages, $widget_id, $settings) {
        if ($current_page < $total_pages) {
            echo '<div class="sgf-infinite-trigger" data-page="' . ($current_page + 1) . '" data-widget="' . $widget_id . '" style="height: 1px;"></div>';
            echo '<div class="sgf-loading-indicator" style="text-align: center; padding: 20px; display: none;">Loading more items...</div>';
        } else {
            echo '<div class="sgf-end-message">All items loaded</div>';
        }
    }

    private function render_pagination_script($widget_id, $settings, $query_data) {
        $nonce = wp_create_nonce('sgf_pagination');
        
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const widget = document.getElementById("' . $widget_id . '");
            if (!widget) return;
            
            const gallery = widget.querySelector(".sgf-gallery");
            if (!gallery) return;
            
            // Find pagination element (try as next sibling first, then by widget ID)
            let pagination = widget.nextElementSibling;
            if (!pagination || !pagination.classList.contains("sgf-pagination")) {
                pagination = document.querySelector(`.sgf-pagination[data-widget="' . $widget_id . '"]`);
            }
            
            if (!pagination) return;
            
            // Handle page navigation
            pagination.addEventListener("click", function(e) {
                e.preventDefault();
                
                if (e.target.classList.contains("sgf-page-btn")) {
                    const page = parseInt(e.target.dataset.page);
                    if (page && page > 0) {
                        loadPage(page);
                    }
                } else if (e.target.classList.contains("sgf-load-more-btn")) {
                    const page = parseInt(e.target.dataset.page);
                    if (page && page > 0) {
                        loadMoreItems(page, e.target);
                    }
                }
            });
            
            function loadPage(page) {
                // Show loading state
                gallery.style.opacity = "0.3";
                gallery.style.pointerEvents = "none";
                
                // Update URL and navigate
                const url = new URL(window.location);
                url.searchParams.set("sgf_page", page);
                
                setTimeout(() => {
                    window.location.href = url.toString();
                }, 100);
            }
            
            function loadMoreItems(page, button) {
                button.disabled = true;
                button.textContent = "Loading...";
                button.style.opacity = "0.6";
                
                // Navigate to show more items
                const url = new URL(window.location);
                url.searchParams.set("sgf_page", page);
                
                setTimeout(() => {
                    window.location.href = url.toString();
                }, 500);
            }
            
            // Infinite scroll implementation
            if ("' . $settings['pagination_type'] . '" === "infinite") {
                let loading = false;
                const trigger = widget.querySelector(".sgf-infinite-trigger");
                const loadingIndicator = widget.querySelector(".sgf-loading-indicator");
                
                if (trigger && loadingIndicator) {
                    const observer = new IntersectionObserver(function(entries) {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !loading) {
                                loading = true;
                                loadingIndicator.style.display = "block";
                                
                                const page = parseInt(trigger.dataset.page);
                                if (page && page > 0) {
                                    setTimeout(() => {
                                        const url = new URL(window.location);
                                        url.searchParams.set("sgf_page", page);
                                        window.location.href = url.toString();
                                    }, 1000);
                                }
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    observer.observe(trigger);
                }
            }
        });
        </script>';
    }
}