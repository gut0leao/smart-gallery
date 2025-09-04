<?php
/**
 * Car catalog data import script - Final version
 * 
 * Generates data directly from images and imports to Pods
 * without dependency on external JSON files
 * 
 * Usage: ddev exec wp eval-file demo-data/pods-import.php
 */

// Check if we're in WordPress context
if (!defined('ABSPATH')) {
    die('This script must be run in WordPress context');
}

// Check if Pods is available
if (!class_exists('Pods') || !function_exists('pods')) {
    die('Pods plugin is not active');
}

echo "=== Complete Import - Pod Creation + Data ===\n\n";

// Function to create Pod using Pods API
function create_car_pod() {
    echo "🏗️ Creating Pod 'car'...\n";
    
    // Car Pod configuration
    $pod_data = [
        'name' => 'car',
        'label' => 'Cars',
        'type' => 'post_type',
        'storage' => 'post_type',
        'label_singular' => 'Car',
        'public' => 1,
        'show_ui' => 1,
        'menu_icon' => 'dashicons-car',
        'supports_title' => 1,
        'supports_editor' => 1,
        'supports_thumbnail' => 1,  // Enable featured image support
        'hierarchical' => 0,
        'publicly_queryable' => 1,
        'exclude_from_search' => 0,
        'capability_type' => 'post',
        'has_archive' => 1,
        'rewrite' => 1,
        'query_var' => 1,
        'can_export' => 1,
        'default_status' => 'draft',
        // Enable built-in taxonomies for car CPT
        'built_in_taxonomies_car_brand' => 1,
        'built_in_taxonomies_car_body_type' => 1,
        'built_in_taxonomies_car_fuel_type' => 1,
        'built_in_taxonomies_car_transmission' => 1
    ];
    
    $pod_id = pods_api()->save_pod($pod_data);
    
    if (is_wp_error($pod_id)) {
        echo "   ❌ Error creating Pod: " . $pod_id->get_error_message() . "\n";
        return false;
    }
    
    echo "   ✅ Pod 'car' created (ID: $pod_id)\n";
    
    // Add custom fields
    $fields = [
        [
            'name' => 'price',
            'label' => 'Price',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 2
        ],
        [
            'name' => 'year',
            'label' => 'Year',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 0
        ],
        [
            'name' => 'mileage',
            'label' => 'Mileage',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 0
        ],
        [
            'name' => 'engine_size',
            'label' => 'Engine Size',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 1
        ],
        [
            'name' => 'power_hp',
            'label' => 'Power (HP)',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 0
        ],
        [
            'name' => 'color',
            'label' => 'Color',
            'type' => 'text'
        ],
        [
            'name' => 'doors',
            'label' => 'Doors',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 0
        ],
        [
            'name' => 'model',
            'label' => 'Model',
            'type' => 'text'
        ],
        [
            'name' => 'car_status',
            'label' => 'Status',
            'type' => 'pick',
            'pick_format_type' => 'single',
            'pick_format_single' => 'dropdown',
            'data' => [
                'available' => 'Available',
                'sold' => 'Sold',
                'reserved' => 'Reserved'
            ]
        ],
        [
            'name' => 'condition',
            'label' => 'Condition',
            'type' => 'pick',
            'pick_format_type' => 'single',
            'pick_format_single' => 'dropdown',
            'data' => [
                'new' => 'New',
                'used' => 'Used',
                'seminew' => 'Semi New'
            ]
        ]
        // REMOVED: car_image_filename field - images are handled as WordPress native featured images
    ];
    
    foreach ($fields as $field_data) {
        $field_data['pod'] = 'car';
        $field_data['pod_id'] = $pod_id;
        
        $field_id = pods_api()->save_field($field_data);
        
        if (is_wp_error($field_id)) {
            echo "   ❌ Error creating field {$field_data['name']}: " . $field_id->get_error_message() . "\n";
        } else {
            echo "   ✅ Field '{$field_data['name']}' created\n";
        }
    }
    
    return $pod_id;
}

// Function to upload image and return attachment ID
function upload_car_image($image_filename) {
    $images_dir = __DIR__ . '/images';
    $image_path = $images_dir . '/' . $image_filename;
    
    if (!file_exists($image_path)) {
        return false;
    }
    
    // Check if image was already imported
    $existing_attachment = get_posts([
        'post_type' => 'attachment',
        'meta_query' => [
            [
                'key' => '_wp_attached_file',
                'value' => $image_filename,
                'compare' => 'LIKE'
            ]
        ],
        'posts_per_page' => 1
    ]);
    
    if (!empty($existing_attachment)) {
        return $existing_attachment[0]->ID;
    }
    
    // Upload the image
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $upload_dir = wp_upload_dir();
    $filename = wp_unique_filename($upload_dir['path'], $image_filename);
    $destination = $upload_dir['path'] . '/' . $filename;
    
    // Copy the file
    if (!copy($image_path, $destination)) {
        return false;
    }
    
    // Create the attachment
    $attachment = [
        'post_mime_type' => wp_check_filetype($filename)['type'],
        'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
        'post_content' => '',
        'post_status' => 'inherit'
    ];
    
    $attachment_id = wp_insert_attachment($attachment, $destination);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    // Generate image metadata
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $destination);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    return $attachment_id;
}

// Function to create taxonomies
function create_car_taxonomies() {
    echo "\n🏷️ Creating taxonomies...\n";
    
    $taxonomies = [
        [
            'name' => 'car_brand',
            'label' => 'Brands',
            'label_singular' => 'Brand'
        ],
        [
            'name' => 'car_body_type',
            'label' => 'Body Types',
            'label_singular' => 'Body Type'
        ],
        [
            'name' => 'car_fuel_type',
            'label' => 'Fuel Types',
            'label_singular' => 'Fuel Type'
        ],
        [
            'name' => 'car_transmission',
            'label' => 'Transmissions',
            'label_singular' => 'Transmission'
        ]
    ];
    
    foreach ($taxonomies as $tax_data) {
        $taxonomy_data = [
            'name' => $tax_data['name'],
            'label' => $tax_data['label'],
            'label_singular' => $tax_data['label_singular'],
            'type' => 'taxonomy',
            'storage' => 'taxonomy',
            'object' => 'post_type',
            'built_in_post_types_car' => 1,
            'public' => 1,
            'show_ui' => 1,
            'hierarchical' => 0,
            'rewrite' => 1,
            'query_var' => 1,
            'show_tagcloud' => 1,
            'show_in_nav_menus' => 1,
            'show_admin_column' => 1
        ];
        
        $tax_id = pods_api()->save_pod($taxonomy_data);
        
        if (is_wp_error($tax_id)) {
            echo "   ❌ Error creating taxonomy {$tax_data['name']}: " . $tax_id->get_error_message() . "\n";
        } else {
            echo "   ✅ Taxonomy '{$tax_data['name']}' created (ID: $tax_id)\n";
        }
    }
    
    // Force WordPress to register the taxonomies immediately
    echo "   🔄 Registering taxonomies in WordPress...\n";
    
    // Re-initialize Pods to register new taxonomies
    pods_init();
    
    // Manual registration as fallback
    foreach ($taxonomies as $tax_data) {
        if (!taxonomy_exists($tax_data['name'])) {
            register_taxonomy($tax_data['name'], 'car', [
                'label' => $tax_data['label'],
                'labels' => [
                    'name' => $tax_data['label'],
                    'singular_name' => $tax_data['label_singular']
                ],
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_admin_column' => true,
                'hierarchical' => false,
                'rewrite' => ['slug' => $tax_data['name']],
                'query_var' => true,
            ]);
            echo "   ✅ Manually registered taxonomy '{$tax_data['name']}'\n";
        }
    }
}

// Enable featured image support for current theme
add_theme_support('post-thumbnails');
add_theme_support('post-thumbnails', array('car')); // Specifically for car CPT

echo "✅ Featured image support enabled\n";

// Check if 'car' Pod already exists
$existing_pod = pods_api()->load_pod(['name' => 'car']);

if (empty($existing_pod)) {
    // Create Pod and taxonomies
    $pod_created = create_car_pod();
    create_car_taxonomies();
    
    if (!$pod_created) {
        echo "❌ Failed to create Pod. Stopping execution.\n";
        exit(1);
    }
    
    echo "\n⏳ Waiting 2 seconds for WordPress to register new post types...\n";
    sleep(2);
    
    // Flush rewrite rules to register new post types
    flush_rewrite_rules();
    echo "   ✅ Rewrite rules updated\n";
    
    // Refresh taxonomy cache now that taxonomies are registered
    refresh_taxonomy_cache();
    echo "   ✅ Taxonomy cache refreshed\n";
    
} else {
    echo "ℹ️ Pod 'car' already exists, skipping creation...\n";
    
    // Still refresh cache in case taxonomies were created in this session
    refresh_taxonomy_cache();
}

// Remove old data first (only if CPT exists)
echo "\n🧹 Cleaning old data...\n";
$old_cars = get_posts([
    'post_type' => 'car',
    'post_status' => 'any',
    'numberposts' => -1,
    'fields' => 'ids'
]);

foreach ($old_cars as $car_id) {
    wp_delete_post($car_id, true);
}

echo "   Removed: " . count($old_cars) . " old cars\n";

// Generate data directly from images
function generate_car_data_from_images() {
    $images_dir = __DIR__ . '/images';
    $images = glob($images_dir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    if (empty($images)) {
        return [];
    }
    
    $cars_data = [];
    $dealers = range(2000, 2014); // Dealer IDs
    
    foreach ($images as $image_path) {
        $filename = basename($image_path);
        $name_parts = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('-', $name_parts);
        
        if (count($parts) >= 4) {
            $brand = $parts[0];
            $model = implode(' ', array_slice($parts, 1, -2));
            $body_type = $parts[count($parts) - 2];
            $year = $parts[count($parts) - 1];
            
            $price = rand(15000, 150000);
            $mileage = rand(5000, 80000);
            $engine_size = number_format(rand(10, 60) / 10, 1);
            $power_hp = rand(120, 500);
            $colors = ['black', 'white', 'red', 'blue', 'silver', 'gray', 'green', 'yellow', 'orange'];
            $color = $colors[array_rand($colors)];
            $doors = rand(2, 5);
            $statuses = ['available', 'sold', 'reserved'];
            $conditions = ['new', 'used', 'seminew'];
            $fuels = ['gasoline', 'diesel', 'hybrid', 'electric'];
            $transmissions = ['automatic', 'manual', 'cvt'];
            
            $title = ucwords($brand . ' ' . $model . ' ' . ucfirst($body_type) . ' ' . $year);
            
            $cars_data[] = [
                'price' => $price,
                'year' => $year,
                'mileage' => $mileage,
                'engine_size' => $engine_size,
                'power_hp' => $power_hp,
                'color' => $color,
                'doors' => $doors,
                'model' => $model,
                'car_status' => $statuses[array_rand($statuses)],
                'condition' => $conditions[array_rand($conditions)],
                'car_image' => $filename,
                'dealer_id' => $dealers[array_rand($dealers)],
                'post_title' => $title,
                'post_content' => "<p>Excellent <strong>{$title}</strong> in pristine condition.</p>\n<p><strong>Specifications:</strong></p>\n<ul>\n<li>Engine: {$engine_size}L " . $fuels[array_rand($fuels)] . "</li>\n<li>Power: {$power_hp} HP</li>\n<li>Transmission: " . $transmissions[array_rand($transmissions)] . "</li>\n<li>Doors: {$doors}</li>\n<li>Color: {$color}</li>\n</ul>\n<p>Well-maintained vehicle with complete service history and documentation.</p>",
                'car_brand' => [['name' => $brand, 'slug' => $brand, 'taxonomy' => 'car_brand']],
                'car_body_type' => [['name' => $body_type, 'slug' => $body_type, 'taxonomy' => 'car_body_type']],
                'car_fuel_type' => [['name' => $fuels[array_rand($fuels)], 'slug' => $fuels[array_rand($fuels)], 'taxonomy' => 'car_fuel_type']],
                'car_transmission' => [['name' => $transmissions[array_rand($transmissions)], 'slug' => $transmissions[array_rand($transmissions)], 'taxonomy' => 'car_transmission']]
            ];
        }
    }
    
    return $cars_data;
}

$cars_data = generate_car_data_from_images();

if (empty($cars_data)) {
    echo "❌ No images found in images/ directory\n";
    exit(1);
}

echo "📊 Generated data: " . count($cars_data) . " cars from images\n\n";

// Function to create or get taxonomy term
function get_or_create_term($term_name, $taxonomy) {
    // Static cache to avoid repeated taxonomy existence checks
    static $taxonomy_cache = [];
    static $warnings_shown = [];
    
    // Special case for cache refresh
    if ($term_name === '__REFRESH_CACHE__') {
        $taxonomy_cache = [];
        return false;
    }
    
    // Check if we already verified this taxonomy exists
    if (!isset($taxonomy_cache[$taxonomy])) {
        $taxonomy_cache[$taxonomy] = taxonomy_exists($taxonomy);
        
        // Only show warning once per taxonomy
        if (!$taxonomy_cache[$taxonomy] && !isset($warnings_shown[$taxonomy])) {
            echo "   ⚠️ Taxonomy '$taxonomy' doesn't exist yet (will be registered automatically)\n";
            $warnings_shown[$taxonomy] = true;
        }
    }
    
    // If we know taxonomy doesn't exist, try one more time before giving up
    if (!$taxonomy_cache[$taxonomy]) {
        // Double-check taxonomy existence (may have been registered since last check)
        if (taxonomy_exists($taxonomy)) {
            $taxonomy_cache[$taxonomy] = true;
        } else {
            return false;
        }
    }
    
    $term = get_term_by('name', $term_name, $taxonomy);
    
    if (!$term) {
        $result = wp_insert_term($term_name, $taxonomy);
        if (!is_wp_error($result)) {
            $term = get_term($result['term_id'], $taxonomy);
        } else {
            echo "   ❌ Error creating term '$term_name' in taxonomy '$taxonomy': " . $result->get_error_message() . "\n";
            return false;
        }
    }
    
    return $term;
}

// Function to refresh taxonomy cache after they are created
function refresh_taxonomy_cache() {
    // Clear the static cache by calling with special flag
    get_or_create_term('__REFRESH_CACHE__', '__REFRESH_CACHE__');
}

// Function to import a car using wp_insert_post
function import_car_simple($car_data) {
    // Create the post
    $post_data = [
        'post_title' => $car_data['post_title'],
        'post_content' => $car_data['post_content'],
        'post_status' => 'publish',
        'post_type' => 'car',
        'post_name' => sanitize_title($car_data['post_title'])
    ];
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return false;
    }
    
    // Upload image and set as WordPress native featured image
    if (isset($car_data['car_image'])) {
        $attachment_id = upload_car_image($car_data['car_image']);
        if ($attachment_id) {
            // Set as WordPress native featured image (not custom field)
            $result = set_post_thumbnail($post_id, $attachment_id);
            if ($result) {
                echo "   🖼️ Featured image (ID: $attachment_id) associated with post $post_id\n";
            } else {
                echo "   ⚠️ Error setting featured image for post $post_id\n";
            }
        } else {
            echo "   ⚠️ Failed to upload image {$car_data['car_image']}\n";
        }
    }
    
    // Add custom fields using meta (WITHOUT including car_image to avoid conflict)
    $custom_fields = [
        'price', 'year', 'mileage', 'engine_size', 'power_hp', 
        'color', 'doors', 'model', 'car_status', 'condition'
        // REMOVED 'car_image' to avoid conflict with featured image
    ];
    
    foreach ($custom_fields as $field) {
        if (isset($car_data[$field])) {
            update_post_meta($post_id, $field, $car_data[$field]);
        }
    }
    
    // Add car taxonomies
    $taxonomies_data = [
        'car_brand' => 'car_brand',
        'car_body_type' => 'car_body_type', 
        'car_fuel_type' => 'car_fuel_type',
        'car_transmission' => 'car_transmission'
    ];
    
    foreach ($taxonomies_data as $data_key => $taxonomy) {
        if (isset($car_data[$data_key]) && is_array($car_data[$data_key])) {
            $term_ids = [];
            foreach ($car_data[$data_key] as $term_data) {
                $term = get_or_create_term($term_data['name'], $taxonomy);
                if ($term && !is_wp_error($term)) {
                    $term_ids[] = (int) $term->term_id;
                }
            }
            
            if (!empty($term_ids)) {
                $result = wp_set_object_terms($post_id, $term_ids, $taxonomy);
                if (is_wp_error($result)) {
                    echo "   ⚠️ Error associating taxonomy $taxonomy: " . $result->get_error_message() . "\n";
                }
            }
        }
    }
    
    return $post_id;
}

// Import cars
echo "\n🚗 Importing cars with images...\n";

$cars_imported = 0;
$cars_errors = 0;
$images_uploaded = 0;

// Get all cars from images (196)
$sample_data = $cars_data; // All cars

foreach ($sample_data as $i => $car_data) {
    try {
        $car_id = import_car_simple($car_data);
        
        if ($car_id) {
            $cars_imported++;
            
            // Count uploaded images (check if has featured image)
            if (has_post_thumbnail($car_id)) {
                $images_uploaded++;
            }
            
            if ($cars_imported % 10 == 0) {
                echo "   Imported: $cars_imported cars, $images_uploaded images\n";
            }
        } else {
            $cars_errors++;
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error importing: " . $car_data['post_title'] . " - " . $e->getMessage() . "\n";
        $cars_errors++;
    }
}

echo "\n✅ Cars: $cars_imported imported, $images_uploaded images associated, $cars_errors errors\n\n";

// Show taxonomy statistics
echo "🏷️ Terms created:\n";
$taxonomies = ['car_brand', 'car_body_type', 'car_fuel_type', 'car_transmission'];
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    if (!is_wp_error($terms)) {
        echo "   - $taxonomy: " . count($terms) . " terms\n";
    }
}

echo "\n🎉 Import completed!\n\n";
echo "📋 Next steps:\n";
echo "1. Go to wp-admin/edit.php?post_type=car to see the cars\n";
echo "2. Test search and filters on frontend\n";
echo "3. To import all cars, modify \$sample_data = \$cars_data in the script\n";

// Show some examples of created cars
echo "\n📝 Last imported cars:\n";
$recent_cars = get_posts([
    'post_type' => 'car',
    'numberposts' => 5,
    'meta_key' => 'price',
    'orderby' => 'date',
    'order' => 'DESC'
]);

foreach ($recent_cars as $car) {
    $price = get_post_meta($car->ID, 'price', true);
    $year = get_post_meta($car->ID, 'year', true);
    echo "   - {$car->post_title} (\${$price}, {$year})\n";
}

echo "\n✅ Process completed successfully!\n";
