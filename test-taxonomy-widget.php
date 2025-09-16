<?php
/**
 * Test taxonomy filters - direct widget creation
 */

// This should be run from WordPress admin area or via browser
if (!defined('ABSPATH')) {
    echo "Loading WordPress...";
    require_once('../wp-load.php');
}

echo "<h1>Smart Gallery Widget Test - Taxonomy Filters</h1>";

// Get Cars CPT and simulate widget settings
$settings = array(
    'selected_cpt' => 'cars',
    'available_fields_for_filtering' => array(), // No custom fields
    'available_taxonomies_for_filtering' => array('car_brand', 'car_location'), // Include taxonomies
    'posts_per_page' => 12,
    'gallery_layout' => 'grid'
);

// Create widget instance
if (class_exists('Smart_Gallery_Widget')) {
    echo "<div style='display: flex;'>";
    echo "<div style='width: 300px; border-right: 1px solid #ccc; padding-right: 20px;'>";
    echo "<h2>Filter Sidebar</h2>";
    
    // Manually instantiate and render the filters
    if (class_exists('Smart_Gallery_Renderer')) {
        $renderer = new Smart_Gallery_Renderer();
        
        // Use reflection to call protected method
        $reflection = new ReflectionClass($renderer);
        $method = $reflection->getMethod('render_filters_interface');
        $method->setAccessible(true);
        
        try {
            echo "<div id='widget-test'>";
            $method->invoke($renderer, $settings, ''); // Empty search term
            echo "</div>";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    echo "</div>";
    echo "<div style='padding-left: 20px;'>";
    echo "<h2>Gallery Area</h2>";
    echo "<p>Widget sidebar should show taxonomy filters for Car Brand and Car Location if working correctly.</p>";
    echo "</div>";
    echo "</div>";
}

?>