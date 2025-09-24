<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor Smart Gallery Widget
 * 
 * Clean modular implementation using:
 * - Smart_Gallery_Pods_Integration for all Pods operations
 * - Smart_Gallery_Controls_Manager for Elementor controls  
 * - Smart_Gallery_Renderer for HTML output generation
 * 
 * @since 1.1.0
 */
class Elementor_Smart_Gallery_Widget extends \Elementor\Widget_Base {

    /**
     * Pods Integration instance
     * 
     * @var Smart_Gallery_Pods_Integration
     */
    private $pods_integration;

    /**
     * Controls Manager instance
     * 
     * @var Smart_Gallery_Controls_Manager
     */
    private $controls_manager;

    /**
     * Renderer instance
     * 
     * @var Smart_Gallery_Renderer
     */
    private $renderer;

    /**
     * Constructor
     */
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        
        // Initialize modular components
        $this->pods_integration = new Smart_Gallery_Pods_Integration();
        $this->controls_manager = new Smart_Gallery_Controls_Manager($this->pods_integration);
        $this->renderer = new Smart_Gallery_Renderer($this->pods_integration);
    }

    /**
     * Get widget name
     * 
     * @return string
     */
    public function get_name() {
        return 'smart_gallery';
    }

    /**
     * Get widget title
     * 
     * @return string
     */
    public function get_title() {
        return esc_html__('Smart Gallery', 'smart-gallery');
    }

    /**
     * Get widget icon
     * 
     * @return string
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    /**
     * Get widget categories
     * 
     * @return array
     */
    public function get_categories() {
        return ['general'];
    }

    /**
     * Get widget keywords
     * 
     * @return array
     */
    public function get_keywords() {
        return ['gallery', 'image', 'photo', 'filter', 'search', 'pods', 'custom post type'];
    }

    /**
     * Register widget controls using Controls Manager
     */
    protected function register_controls() {
        $this->controls_manager->register_controls($this);
    }

    /**
     * Render widget output on the frontend using Renderer
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $this->renderer->render_gallery($settings);
    }

    /**
     * Render widget output in the editor using Renderer
     * 
     * @return void
     */
    protected function content_template() {
        echo $this->renderer->render_content_template();
    }
}
