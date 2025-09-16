<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Smart Gallery Controls Manager
 * 
 * Handles all Elementor control registration and configuration including:
 * - Content controls (CPT selection, content display)
 * - Layout controls (columns, spacing, posts per page)
 * - Style controls (colors, typography)
 * - Dynamic control population based on selections
 * 
 * @since 1.1.0
 */
class Smart_Gallery_Controls_Manager {

    /**
     * Pods Integration instance
     * 
     * @var Smart_Gallery_Pods_Integration
     */
    private $pods_integration;

    /**
     * Constructor
     * 
     * @param Smart_Gallery_Pods_Integration $pods_integration
     */
    public function __construct($pods_integration) {
        $this->pods_integration = $pods_integration;
    }

    /**
     * Register all widget controls
     * 
     * @param \Elementor\Widget_Base $widget
     */
    public function register_controls($widget) {
        $this->register_content_controls($widget);
        $this->register_layout_controls($widget);
        $this->register_style_controls($widget);
    }

    /**
     * Register Gallery Content controls
     * 
     * @param \Elementor\Widget_Base $widget
     */
    private function register_content_controls($widget) {
        $widget->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Gallery Content', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // CPT Selection Dropdown
        $widget->add_control(
            'selected_cpt',
            [
                'label' => esc_html__('Select a Pod', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->pods_integration->get_available_cpts(),
                'description' => esc_html__('Choose which Pod to display in the gallery', 'smart-gallery'),
            ]
        );

        // Show Post Title Toggle
        $widget->add_control(
            'show_title',
            [
                'label' => esc_html__('Post Title', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'smart-gallery'),
                'label_off' => esc_html__('Hide', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Display post titles in gallery items', 'smart-gallery'),
            ]
        );

        // Show Post Description Toggle
        $widget->add_control(
            'show_description',
            [
                'label' => esc_html__('Post Description', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'smart-gallery'),
                'label_off' => esc_html__('Hide', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Display descriptions in gallery items', 'smart-gallery'),
            ]
        );

        // Description Field Selection (conditional - only when Show Description enabled)
        $widget->add_control(
            'description_field',
            [
                'label' => esc_html__('Description Field', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'content',
                'options' => [
                    'content' => esc_html__('Post Content (cropped)', 'smart-gallery'),
                    'custom_field' => esc_html__('Custom Field', 'smart-gallery'),
                ],
                'description' => esc_html__('Choose what to display as item description', 'smart-gallery'),
                'condition' => [
                    'show_description' => 'yes',
                ],
            ]
        );

        // Custom Field Selection (shown only when custom_field is selected AND Show Description enabled)
        $widget->add_control(
            'custom_description_field',
            [
                'label' => esc_html__('Custom Field Name', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__('field_name', 'smart-gallery'),
                'description' => esc_html__('Enter the name of the custom field to use as description', 'smart-gallery'),
                'condition' => [
                    'show_description' => 'yes',
                    'description_field' => 'custom_field',
                ],
            ]
        );

        // Description Length (conditional - only when Show Description enabled AND Post Content selected)
        $widget->add_control(
            'description_length',
            [
                'label' => esc_html__('Description Length', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 10,
                'max' => 500,
                'step' => 10,
                'description' => esc_html__('Maximum number of characters for description', 'smart-gallery'),
                'condition' => [
                    'show_description' => 'yes',
                    'description_field' => 'content',
                ],
            ]
        );

        // No Results Message
        $widget->add_control(
            'no_results_message',
            [
                'label' => esc_html__('No Results Message', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('No results found...', 'smart-gallery'),
                'description' => esc_html__('Message displayed when no posts match the criteria', 'smart-gallery'),
                'separator' => 'before',
            ]
        );

        $widget->end_controls_section();

        // Search Settings Section
        $this->register_search_controls($widget);
    }

    /**
     * Register Search Settings controls
     * 
     * @param \Elementor\Widget_Base $widget
     */
    private function register_search_controls($widget) {
        $widget->start_controls_section(
            'search_section',
            [
                'label' => esc_html__('Search and Filter Settings', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Enable Search Input
        $widget->add_control(
            'enable_search_input',
            [
                'label' => esc_html__('Enable Search Input', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Show search input to filter gallery content', 'smart-gallery'),
            ]
        );

        // Search Placeholder Text
        $widget->add_control(
            'search_placeholder_text',
            [
                'label' => esc_html__('Search Placeholder Text', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Search here...', 'smart-gallery'),
                'placeholder' => esc_html__('Enter placeholder text', 'smart-gallery'),
                'description' => esc_html__('Text shown inside search input when empty', 'smart-gallery'),
                'condition' => [
                    'enable_search_input' => 'yes',
                ],
            ]
        );

        // Search Position
        $widget->add_control(
            'search_position',
            [
                'label' => esc_html__('Search Position', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'upper_bar',
                'options' => [
                    'upper_bar' => esc_html__('Upper Bar', 'smart-gallery'),
                    'left_bar' => esc_html__('Left Bar', 'smart-gallery'),
                ],
                'description' => esc_html__('Choose where to display the search input and clear button', 'smart-gallery'),
                'condition' => [
                    'enable_search_input' => 'yes',
                ],
            ]
        );

        // Filter Settings Divider
        $widget->add_control(
            'filter_heading',
            [
                'label' => esc_html__('Filter Settings', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Show Filters
        $widget->add_control(
            'show_filters',
            [
                'label' => esc_html__('Show Filters', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => '',
                'description' => esc_html__('Enable custom field filtering in the left sidebar', 'smart-gallery'),
            ]
        );

        // Available Fields for Filtering
        $widget->add_control(
            'available_fields_for_filtering',
            [
                'label' => esc_html__('Available Fields for Filtering', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_all_possible_custom_fields(),
                'description' => esc_html__('Select which custom fields from the chosen CPT will be available for filtering. Only fields with values will appear in the filter interface.', 'smart-gallery'),
                'condition' => [
                    'show_filters' => 'yes',
                    'selected_cpt!' => '', // Only show when a CPT is selected
                ],
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * Register Layout and Presentation controls
     * 
     * @param \Elementor\Widget_Base $widget
     */
    private function register_layout_controls($widget) {
        $widget->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__('Layout and Presentation Settings', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Pagination and Grid Section Divider
        $widget->add_control(
            'pagination_heading',
            [
                'label' => esc_html__('Pagination Settings', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Enable Pagination
        $widget->add_control(
            'enable_pagination',
            [
                'label' => esc_html__('Enable Pagination', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Show pagination controls when there are multiple pages', 'smart-gallery'),
            ]
        );

        // Posts per Page
        $widget->add_control(
            'posts_per_page',
            [
                'label' => esc_html__('Posts per Page', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'description' => esc_html__('Number of posts to display per page', 'smart-gallery'),
            ]
        );

        // Max Page Numbers to Show
        $widget->add_control(
            'max_page_numbers',
            [
                'label' => esc_html__('Max Page Numbers', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 3,
                'max' => 15,
                'step' => 2,
                'condition' => [
                    'enable_pagination' => 'yes',
                    'show_page_numbers' => 'yes',
                ],
                'description' => esc_html__('Maximum number of page buttons to display (odd numbers recommended)', 'smart-gallery'),
            ]
        );

        // Show Previous/Next Buttons
        $widget->add_control(
            'show_prev_next',
            [
                'label' => esc_html__('Show Previous/Next', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
                'description' => esc_html__('Display previous and next navigation buttons', 'smart-gallery'),
            ]
        );

        // Show Page Numbers
        $widget->add_control(
            'show_page_numbers',
            [
                'label' => esc_html__('Show Page Numbers', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
                'description' => esc_html__('Display numbered page buttons', 'smart-gallery'),
            ]
        );

        // Grid Settings Section Divider
        $widget->add_control(
            'grid_heading',
            [
                'label' => esc_html__('Grid Settings', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Columns Control
        $widget->add_responsive_control(
            'columns',
            [
                'label' => esc_html__('Columns', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .smart-gallery-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr) !important;',
                ],
            ]
        );

        // Gap Control
        $widget->add_responsive_control(
            'gap',
            [
                'label' => esc_html__('Gap', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .smart-gallery-grid' => 'gap: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        // Hover Effects
        $widget->add_control(
            'hover_effects_heading',
            [
                'label' => esc_html__('Hover Effects', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Enable Image Hover
        $widget->add_control(
            'enable_image_hover',
            [
                'label' => esc_html__('Enable Image Hover', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Enable zoom effect on featured image hover', 'smart-gallery'),
            ]
        );

        // Enable Content Hover
        $widget->add_control(
            'enable_content_hover',
            [
                'label' => esc_html__('Enable Content Hover', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'smart-gallery'),
                'label_off' => esc_html__('No', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => esc_html__('Hide content by default, show on hover with slide up effect', 'smart-gallery'),
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * Register Style controls
     * 
     * @param \Elementor\Widget_Base $widget
     */
    private function register_style_controls($widget) {
        $widget->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $widget->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .smart-gallery-placeholder' => 'color: {{VALUE}}',
                ],
            ]
        );

        $widget->end_controls_section();

        // ==============================================
        // DEBUG SETTINGS SECTION
        // ==============================================
        $widget->start_controls_section(
            'debug_section',
            [
                'label' => esc_html__('Debug Settings', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Show Gallery Status (Debug)
        $widget->add_control(
            'show_gallery_status_debug',
            [
                'label' => esc_html__('Show Gallery Status (Debug)', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'smart-gallery'),
                'label_off' => esc_html__('Hide', 'smart-gallery'),
                'return_value' => 'yes',
                'default' => '',
                'description' => esc_html__('Display debug status panel below gallery for development and testing', 'smart-gallery'),
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * Get available custom fields for filtering from a specific CPT
     * 
     * @param string $cpt_name Specific CPT to get fields from
     * @return array
     */
    private function get_available_custom_fields($cpt_name = '') {
        $fields = [];
        
        // If no CPT specified, return empty (fields will be populated dynamically)
        if (empty($cpt_name)) {
            return $fields;
        }
        
        // Verify CPT exists and has Pods configuration
        if (!$this->pods_integration->is_pods_available()) {
            return $fields;
        }
        
        // Get fields only from the specified CPT
        $cpt_fields = $this->pods_integration->get_pod_fields($cpt_name);
        
        if (!empty($cpt_fields)) {
            foreach ($cpt_fields as $field_name => $field_config) {
                // Only include fields that are suitable for filtering
                if ($this->is_field_suitable_for_filtering($field_config)) {
                    $fields[$field_name] = $field_config['label'] ?? ucfirst(str_replace('_', ' ', $field_name));
                }
            }
        }
        
        return $fields;
    }

    /**
     * Get all possible custom fields from all CPTs for the select control
     * Note: Fields will be filtered by selected CPT during rendering
     * 
     * @return array
     */
    private function get_all_possible_custom_fields() {
        $fields = [];
        
        if (!$this->pods_integration->is_pods_available()) {
            return $fields;
        }
        
        // Get all available CPTs
        $cpts = $this->pods_integration->get_available_cpts();
        
        if (empty($cpts)) {
            return $fields;
        }
        
        // Get fields from all CPTs and organize them by CPT
        foreach ($cpts as $cpt_key => $cpt_name) {
            // Skip error entries
            if (in_array($cpt_key, ['no_pods', 'api_error', 'no_cpts'])) {
                continue;
            }
            
            $cpt_fields = $this->pods_integration->get_pod_fields($cpt_key);
            
            if (!empty($cpt_fields)) {
                foreach ($cpt_fields as $field_name => $field_config) {
                    // Only include fields that are suitable for filtering
                    if ($this->is_field_suitable_for_filtering($field_config)) {
                        $field_label = $field_config['label'] ?? ucfirst(str_replace('_', ' ', $field_name));
                        // Prefix with CPT name to make it clear which CPT the field belongs to
                        $fields[$field_name] = "[{$cpt_name}] {$field_label}";
                    }
                }
            }
        }
        
        return $fields;
    }

    /**
     * Check if a field is suitable for filtering
     * 
     * @param array $field_config
     * @return bool
     */
    private function is_field_suitable_for_filtering($field_config) {
        // Exclude certain field types that aren't suitable for filtering
        $excluded_types = [
            'file',
            'wysiwyg', 
            'paragraph',
            'code',
            'password'
        ];
        
        $field_type = $field_config['type'] ?? '';
        
        // Exclude fields that are too complex for simple filtering
        if (in_array($field_type, $excluded_types)) {
            return false;
        }
        
        // Include text, number, select, boolean, date fields
        return true;
    }
}
