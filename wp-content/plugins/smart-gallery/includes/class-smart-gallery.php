<?php
class Smart_Gallery {
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        // Check if Pods is active (required for proper functionality)
        if (!function_exists('pods_api')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_pods']);
            return;
        }

        add_action('elementor/widgets/widgets_registered', [$this, 'register_widget']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function admin_notice_missing_elementor() {
        $install_url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=elementor'),
            'install-plugin_elementor'
        );
        
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>Smart Gallery</strong> requires Elementor Page Builder to work.</p>';
        echo '<p><a href="' . esc_url($install_url) . '" class="button button-primary">Install Elementor Now</a> ';
        echo 'or <a href="https://wordpress.org/plugins/elementor/" target="_blank">Learn More</a></p>';
        echo '</div>';
    }

    public function admin_notice_missing_pods() {
        $install_url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=pods'),
            'install-plugin_pods'
        );
        
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>Smart Gallery</strong> requires Pods Framework to work properly.</p>';
        echo '<p>This plugin is specifically designed to work with Custom Post Types and custom fields created by Pods.</p>';
        echo '<p><a href="' . esc_url($install_url) . '" class="button button-primary">Install Pods Framework Now</a> ';
        echo 'or <a href="https://wordpress.org/plugins/pods/" target="_blank">Learn More</a></p>';
        echo '</div>';
    }

    public function register_widget() {
        // Load modular components in proper order
        require_once __DIR__ . '/class-smart-gallery-pods-integration.php';
        require_once __DIR__ . '/class-smart-gallery-controls-manager.php';
        require_once __DIR__ . '/class-smart-gallery-renderer.php';
        require_once __DIR__ . '/class-elementor-smart-gallery-widget.php';
        
        // Register the widget
        \Elementor\Plugin::instance()->widgets_manager->register(new Elementor_Smart_Gallery_Widget());
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'smart-gallery',
            plugin_dir_url(dirname(__FILE__)) . 'assets/style.css',
            [],
            '1.0.0'
        );
    }
}

// Initialize the plugin
new Smart_Gallery();
