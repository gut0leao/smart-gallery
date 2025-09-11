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
    }
}
