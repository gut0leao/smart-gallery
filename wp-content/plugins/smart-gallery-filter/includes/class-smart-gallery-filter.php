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

        // Check if Pods is active (recommended but not required)
        if (!function_exists('pods')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_pods']);
        }

        add_action('elementor/widgets/widgets_registered', [$this, 'register_widget']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function admin_notice_missing_elementor() {
        echo '<div class="notice notice-warning is-dismissible"><p>Smart Gallery Filter requires Elementor to work.</p></div>';
    }

    public function admin_notice_missing_pods() {
        echo '<div class="notice notice-info is-dismissible"><p>Smart Gallery Filter works better with Pods Framework installed for advanced Custom Post Types management.</p></div>';
    }

    public function register_widget() {
        require_once __DIR__ . '/class-elementor-smart-gallery-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new Elementor_Smart_Gallery_Widget());
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'smart-gallery-filter',
            plugin_dir_url(dirname(__FILE__)) . 'assets/style.css',
            [],
            '1.0.0'
        );
    }
}

// Initialize the plugin
new Smart_Gallery_Filter();
