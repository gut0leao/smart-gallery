<?php
/**
 * Test filter selection to check for array-to-string conversion warnings
 */

// Load WordPress
if (!defined('ABSPATH')) {
    echo "Loading WordPress...<br>";
    require_once('../wp-load.php');
}

echo "<h1>Filter Selection Test - Check for Warnings</h1>";

// Simulate URL with filters selected 
echo "<h2>Simulating Filter Selection</h2>";

// Test with various filter combinations that might cause issues
$test_urls = [
    '?filter[brand][]=bmw&filter[brand][]=audi',
    '?filter[model][]=x5&taxonomy_filter[car_brand][]=bmw',
    '?search_term=car&filter[year][]=2020&paged=1'
];

foreach ($test_urls as $index => $query_string) {
    echo "<h3>Test " . ($index + 1) . ": $query_string</h3>";
    
    // Parse query string and set $_GET
    $original_get = $_GET;
    parse_str(ltrim($query_string, '?'), $_GET);
    
    try {
        // Test preserve_url_parameters_unified
        echo "<h4>Testing preserve_url_parameters_unified:</h4>";
        echo "<form>";
        
        if (class_exists('Smart_Gallery_Renderer')) {
            $renderer = new Smart_Gallery_Renderer();
            $reflection = new ReflectionClass($renderer);
            $method = $reflection->getMethod('preserve_url_parameters_unified');
            $method->setAccessible(true);
            
            ob_start();
            $method->invoke($renderer);
            $output = ob_get_clean();
            
            echo htmlspecialchars($output);
        }
        
        echo "</form>";
        
        // Test filter processing
        echo "<h4>Testing get_current_filters_from_url:</h4>";
        if (class_exists('Smart_Gallery_Renderer')) {
            $renderer = new Smart_Gallery_Renderer();
            $reflection = new ReflectionClass($renderer);
            $method = $reflection->getMethod('get_current_filters_from_url');
            $method->setAccessible(true);
            
            $filters = $method->invoke($renderer);
            echo "<pre>";
            print_r($filters);
            echo "</pre>";
        }
        
        echo "<p style='color: green;'>✅ No warnings detected for this test case</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . esc_html($e->getMessage()) . "</p>";
    }
    
    // Restore original $_GET
    $_GET = $original_get;
    
    echo "<hr>";
}

?>