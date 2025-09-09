<?php
/*
Plugin Name: Smart Gallery
Plugin URI: https://github.com/gut0leao/smart-gallery
Description: Gallery widget for Elementor with advanced filtering and search. Requires Elementor Page Builder and Pods Framework for Custom Post Types and custom fields management.
Version: 1.0.0
Author: gut0leao
Author URI: https://github.com/gut0leao
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
Elementor tested up to: 3.15
Elementor Pro tested up to: 3.15

Dependencies (Required):
- Elementor Page Builder
- Pods Framework

Text Domain: smart-gallery
Domain Path: /languages
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define('SGF_VERSION', '1.0.0');
define('SGF_PLUGIN_FILE', __FILE__);
define('SGF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SGF_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Plugin activation hook
register_activation_hook(__FILE__, 'sgf_activation_check');

function sgf_activation_check() {
    // Check if Elementor is installed and active
    if (!is_plugin_active('elementor/elementor.php') && !class_exists('Elementor\Plugin')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #d63384;">Smart Gallery - Activation Error</h2>
                <p><strong>This plugin requires Elementor Page Builder to work.</strong></p>
                <p>Please install and activate Elementor first:</p>
                <ol>
                    <li>Go to <strong>Plugins > Add New</strong></li>
                    <li>Search for <strong>"Elementor"</strong></li>
                    <li>Install and activate <strong>Elementor Page Builder</strong></li>
                    <li>Then try activating Smart Gallery again</li>
                </ol>
                <p><a href="' . admin_url('plugins.php') . '" style="text-decoration: none; background: #007cba; color: white; padding: 8px 16px; border-radius: 4px;">← Back to Plugins</a></p>
            </div>', 
            'Plugin Dependency Error', 
            ['back_link' => true]
        );
    }
    
    // Check if Pods is installed and active
    if (!is_plugin_active('pods/init.php') && !function_exists('pods_api')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #d63384;">Smart Gallery - Activation Error</h2>
                <p><strong>This plugin requires Pods Framework to work properly.</strong></p>
                <p>Smart Gallery is specifically designed for Custom Post Types and custom fields created with Pods.</p>
                <p>Please install and activate Pods Framework first:</p>
                <ol>
                    <li>Go to <strong>Plugins > Add New</strong></li>
                    <li>Search for <strong>"Pods"</strong></li>
                    <li>Install and activate <strong>Pods - Custom Content Types and Fields</strong></li>
                    <li>Then try activating Smart Gallery again</li>
                </ol>
                <p><a href="' . admin_url('plugins.php') . '" style="text-decoration: none; background: #007cba; color: white; padding: 8px 16px; border-radius: 4px;">← Back to Plugins</a></p>
            </div>', 
            'Plugin Dependency Error', 
            ['back_link' => true]
        );
    }
}

// Load main plugin files
require_once __DIR__ . '/includes/class-smart-gallery.php';
