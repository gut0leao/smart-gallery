<?php
/**
 * Test page for Smart Gallery taxonomy filters
 */

// Define WordPress path (adjust if needed)
define('WP_USE_THEMES', false);
require_once('./wp-load.php');

// Check if classes exist
if (!class_exists('Smart_Gallery_Renderer') || !class_exists('Smart_Gallery_Pods_Integration')) {
    die('Smart Gallery plugin not loaded');
}

// Initialize the classes
$pods_integration = new Smart_Gallery_Pods_Integration();
$renderer = new Smart_Gallery_Renderer($pods_integration);

// Test settings - simulating what Elementor would pass
$test_settings = [
    'selected_cpt' => 'car',
    'show_filters' => 'yes',
    'available_taxonomies_for_filtering' => ['car_brand', 'car_location'], // Select some taxonomies
    'available_fields_for_filtering' => [], // Empty to focus on taxonomy
    'posts_per_page' => 12,
    'columns' => 3
];

echo '<!DOCTYPE html>';
echo '<html><head>';
echo '<title>Smart Gallery Taxonomy Test</title>';
echo '<link rel="stylesheet" href="wp-content/plugins/smart-gallery/assets/style.css">';
echo '</head><body>';
echo '<h1>Smart Gallery Taxonomy Filter Test</h1>';

echo '<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">';

// Render the complete gallery widget
try {
    $renderer->render_gallery($test_settings);
} catch (Exception $e) {
    echo '<div style="color: red; padding: 20px; background: #ffeeee; border: 1px solid #ff0000;">';
    echo '<h3>Error:</h3>';
    echo '<p>' . esc_html($e->getMessage()) . '</p>';
    echo '</div>';
}

echo '</div>';
echo '</body></html>';
?>