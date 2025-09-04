<?php
class Smart_Gallery_Filter {
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        add_action('elementor/widgets/widgets_registered', [$this, 'register_widget']);
    }

    public function admin_notice_missing_elementor() {
        echo '<div class="notice notice-warning is-dismissible"><p>Smart Gallery Filter requires Elementor to work.</p></div>';
    }

    public function register_widget() {
        require_once __DIR__ . '/class-elementor-smart-gallery-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new Elementor_Smart_Gallery_Widget());
    }
}

// Initialize the plugin
new Smart_Gallery_Filter();
