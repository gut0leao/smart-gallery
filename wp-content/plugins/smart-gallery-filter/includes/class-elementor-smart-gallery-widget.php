<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Elementor_Smart_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'smart_gallery_filter';
    }

    public function get_title() {
        return 'Smart Gallery Filter';
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
                    'filter-bottom' => 'Filter Sidebar Bottom',
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
                'default' => 'Search items...',
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
                        <h4 style="margin: 0 0 10px 0; color: #007cba;">🎯 Smart Gallery Filter</h4>
                        <p style="margin: 0 0 10px 0; font-size: 13px; line-height: 1.4;">
                            Free alternative to Elementor Pro Posts widget with advanced filtering.
                        </p>
                        
                        <h5 style="margin: 10px 0 5px 0; font-size: 12px; color: #666;">Required Dependencies:</h5>
                        <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #666;">
                            <li><strong>Elementor</strong> ✅</li>
                            <li><strong>Pods Framework</strong> ' . (function_exists('pods_api') ? '✅' : '❌ <a href="' . admin_url('plugin-install.php?s=pods&tab=search&type=term') . '" target="_blank">Install</a>') . '</li>
                        </ul>
                        
                        <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                            <a href="https://github.com/gut0leao/smart-gallery-filter" target="_blank">Documentation & Support</a>
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
        
        $posts = $this->get_gallery_posts($settings);
        
        if (empty($posts)) {
            $this->render_message($settings['empty_message'], 'empty', $settings);
            return;
        }
        
        // Generate unique ID for this widget instance
        $widget_id = 'sgf-' . $this->get_id();
        
        // Render the complete gallery with filters
        $this->render_gallery_with_filters($posts, $settings, $widget_id);
        
        // Add no-results message for filters (hidden by default)
        if ($settings['enable_filters'] === 'yes') {
            echo '<div class="sgf-no-filter-results" id="no-results-' . $widget_id . '" style="display: none;">';
            $this->render_message($settings['no_filters_message'], 'no-filters', $settings, false);
            echo '</div>';
        }
    }
    
    private function render_gallery_with_filters($posts, $settings, $widget_id) {
        $enable_filters = $settings['enable_filters'] === 'yes';
        $enable_search = $settings['enable_search'] === 'yes';
        $filter_position = $settings['filter_position'] ?? 'left';
        $search_position = $settings['search_position'] ?? 'top';
        
        // Container class based on filter position
        $container_class = 'sgf-container';
        if ($enable_filters) {
            $container_class .= ' sgf-with-filters sgf-filters-' . $filter_position;
        }
        if ($enable_search && $search_position === 'top') {
            $container_class .= ' sgf-with-search-top';
        }
        
        echo '<div class="' . $container_class . '" id="' . $widget_id . '">';
        
        // Render search at top
        if ($enable_search && $search_position === 'top') {
            $this->render_search($settings, $widget_id, 'top');
        }
        
        // Render filters
        if ($enable_filters) {
            $this->render_filters($settings, $widget_id, $enable_search, $search_position);
        }
        
        // Render gallery
        echo '<div class="sgf-gallery-container">';
        $this->render_gallery($posts, $settings, $widget_id);
        echo '</div>';
        
        echo '</div>';
        
        // Add JavaScript for filtering and search
        if ($enable_filters || $enable_search) {
            $this->render_filter_and_search_script($widget_id, $settings);
        }
    }
    
    private function render_filters($settings, $widget_id, $enable_search = false, $search_position = 'top') {
        $filter_fields = $settings['filter_fields'] ?? [];
        $filter_taxonomies = $settings['filter_taxonomies'] ?? [];
        
        if (empty($filter_fields) && empty($filter_taxonomies) && !($enable_search && in_array($search_position, ['filter-top', 'filter-bottom']))) {
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
        
        // Search at filter bottom
        if ($enable_search && $search_position === 'filter-bottom') {
            $this->render_search($settings, $widget_id, 'filter-bottom');
        }
        
        echo '</div>';
    }
    
    private function render_search($settings, $widget_id, $position) {
        $placeholder = $settings['search_placeholder'] ?? 'Search items...';
        $button_text = $settings['search_button_text'] ?? '';
        $live_search = $settings['search_live'] === 'yes';
        
        $container_class = 'sgf-search-container sgf-search-' . $position;
        
        echo '<div class="' . $container_class . '">';
        
        if ($position === 'top') {
            echo '<div class="sgf-search-header">';
            echo '<h4>Search</h4>';
            echo '</div>';
        } elseif (in_array($position, ['filter-top', 'filter-bottom'])) {
            echo '<div class="sgf-search-group">';
            echo '<label class="sgf-filter-label">Search</label>';
        }
        
        echo '<div class="sgf-search-input-container">';
        echo '<input type="text" class="sgf-search-input" placeholder="' . esc_attr($placeholder) . '" data-target="' . $widget_id . '" data-live="' . ($live_search ? 'yes' : 'no') . '">';
        
        if (!empty($button_text) && !$live_search) {
            echo '<button type="button" class="sgf-search-button" data-target="' . $widget_id . '">' . esc_html($button_text) . '</button>';
        }
        
        echo '</div>';
        
        if (in_array($position, ['filter-top', 'filter-bottom'])) {
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
        $args = [
            'post_type' => $settings['post_type'],
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'],
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS'
                ]
            ]
        ];
        
        return get_posts($args);
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
        
        echo '<div class="smart-gallery-filter-grid sgf-gallery" data-widget="' . esc_attr($widget_id) . '" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . $gap . 'px;">';
        
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
        </style>';
    }
    
    private function render_filter_and_search_script($widget_id, $settings) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const widget = document.getElementById("' . $widget_id . '");
            const gallery = widget.querySelector(".sgf-gallery");
            const items = gallery.querySelectorAll(".gallery-item");
            const filters = widget.querySelectorAll(".sgf-filter-select, .sgf-filter-text, .sgf-filter-range-min, .sgf-filter-range-max");
            const searchInput = widget.querySelector(".sgf-search-input");
            const searchButton = widget.querySelector(".sgf-search-button");
            const clearButton = widget.querySelector(".sgf-clear-filters");
            
            // Combined filter and search functionality
            function filterAndSearchItems() {
                const activeFilters = {};
                const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
                
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
                    
                    // Apply filters
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
                    
                    // Apply search
                    if (visible && searchTerm) {
                        const searchText = item.dataset.searchText || "";
                        if (searchText.indexOf(searchTerm) === -1) {
                            visible = false;
                        }
                    }
                    
                    item.classList.toggle("sgf-hidden", !visible);
                    if (visible) visibleCount++;
                });
                
                // Show/hide no results message
                const noResultsDiv = document.getElementById("no-results-' . $widget_id . '");
                if (noResultsDiv) {
                    if (visibleCount === 0 && (Object.keys(activeFilters).length > 0 || searchTerm)) {
                        noResultsDiv.style.display = "block";
                    } else {
                        noResultsDiv.style.display = "none";
                    }
                }
            }
            
            // Add event listeners for filters
            filters.forEach(filter => {
                filter.addEventListener("change", filterAndSearchItems);
                filter.addEventListener("input", filterAndSearchItems);
            });
            
            // Add event listeners for search
            if (searchInput) {
                const isLiveSearch = searchInput.dataset.live === "yes";
                if (isLiveSearch) {
                    searchInput.addEventListener("input", filterAndSearchItems);
                } else {
                    searchInput.addEventListener("keypress", function(e) {
                        if (e.key === "Enter") {
                            filterAndSearchItems();
                        }
                    });
                }
            }
            
            if (searchButton) {
                searchButton.addEventListener("click", filterAndSearchItems);
            }
            
            // Clear filters and search
            if (clearButton) {
                clearButton.addEventListener("click", function() {
                    filters.forEach(filter => {
                        filter.value = "";
                    });
                    if (searchInput) {
                        searchInput.value = "";
                    }
                    items.forEach(item => {
                        item.classList.remove("sgf-hidden");
                    });
                    
                    // Hide no results message
                    const noResultsDiv = document.getElementById("no-results-' . $widget_id . '");
                    if (noResultsDiv) {
                        noResultsDiv.style.display = "none";
                    }
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
}