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

// Function to create Dealers Pod
function create_dealers_pod() {
    echo "🏗️ Creating Pod 'dealer'...\n";
    
    // Dealer Pod configuration
    $pod_data = [
        'name' => 'dealer',
        'label' => 'Dealers',
        'type' => 'post_type',
        'storage' => 'post_type',
        'label_singular' => 'Dealer',
        'public' => 1,
        'show_ui' => 1,
        'menu_icon' => 'dashicons-store',
        'supports_title' => 1,
        'supports_editor' => 1,
        'supports_thumbnail' => 1,
        'hierarchical' => 0,
        'publicly_queryable' => 1,
        'exclude_from_search' => 0,
        'capability_type' => 'post',
        'has_archive' => 1,
        'rewrite' => 1,
        'query_var' => 1,
        'can_export' => 1,
        'default_status' => 'draft',
        // Enable shared taxonomies (only car_brand)
        'built_in_taxonomies_car_brand' => 1
    ];
    
    $pod_id = pods_api()->save_pod($pod_data);
    
    if (is_wp_error($pod_id)) {
        /** @var WP_Error $pod_id */
        echo "   ❌ Error creating Pod: " . $pod_id->get_error_message() . "\n";
        return false;
    }
    
    echo "   ✅ Pod 'dealer' created (ID: $pod_id)\n";
    
    // Add custom fields for dealers
    $fields = [
        [
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'text'
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email'
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'paragraph'
        ],
        [
            'name' => 'website',
            'label' => 'Website',
            'type' => 'website'
        ],
        [
            'name' => 'rating',
            'label' => 'Rating',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 1,
            'number_min' => 1,
            'number_max' => 5
        ],
        [
            'name' => 'established_year',
            'label' => 'Established Year',
            'type' => 'number',
            'number_format_type' => 'number',
            'number_decimals' => 0
        ]
    ];
    
    foreach ($fields as $field_data) {
        $field_data['pod'] = 'dealer';
        $field_data['pod_id'] = $pod_id;
        
        $field_id = pods_api()->save_field($field_data);
        
        if (is_wp_error($field_id)) {
            /** @var WP_Error $field_id */
            echo "   ❌ Error creating field {$field_data['name']}: " . $field_id->get_error_message() . "\n";
        } else {
            echo "   ✅ Field '{$field_data['name']}' created\n";
        }
    }
    
    return $pod_id;
}

// Function to create Car Pod using Pods API
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
        'built_in_taxonomies_car_transmission' => 1,
        'built_in_taxonomies_car_location' => 1
    ];
    
    $pod_id = pods_api()->save_pod($pod_data);
    
    if (is_wp_error($pod_id)) {
        /** @var WP_Error $pod_id */
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
        ],
        [
            'name' => 'dealer_id',
            'label' => 'Dealer',
            'type' => 'pick',
            'pick_object' => 'post_type',
            'pick_val' => 'dealer',
            'pick_format_type' => 'single',
            'pick_format_single' => 'dropdown',
            'pick_limit' => 1,
            'pick_display_format_multi' => 'default',
            'pick_display_format_separator' => ', '
        ]
        // REMOVED: car_image_filename field - images are handled as WordPress native featured images
    ];
    
    foreach ($fields as $field_data) {
        $field_data['pod'] = 'car';
        $field_data['pod_id'] = $pod_id;
        
        $field_id = pods_api()->save_field($field_data);
        
        if (is_wp_error($field_id)) {
            /** @var WP_Error $field_id */
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
            'label_singular' => 'Brand',
            'post_types' => ['car', 'dealer']
        ],
        [
            'name' => 'car_body_type',
            'label' => 'Body Types',
            'label_singular' => 'Body Type',
            'post_types' => ['car']
        ],
        [
            'name' => 'car_fuel_type',
            'label' => 'Fuel Types',
            'label_singular' => 'Fuel Type',
            'post_types' => ['car']
        ],
        [
            'name' => 'car_transmission',
            'label' => 'Transmissions',
            'label_singular' => 'Transmission',
            'post_types' => ['car']
        ],
        [
            'name' => 'car_location',
            'label' => 'Car Locations',
            'label_singular' => 'Car Location',
            'post_types' => ['car']
        ],
        [
            'name' => 'dealer_location',
            'label' => 'Dealer Locations',
            'label_singular' => 'Dealer Location',
            'post_types' => ['dealer']
        ]
    ];
    
    foreach ($taxonomies as $tax_data) {
        // First register the taxonomy with WordPress directly
        if (!taxonomy_exists($tax_data['name'])) {
            register_taxonomy($tax_data['name'], $tax_data['post_types'], [
                'label' => $tax_data['label'],
                'labels' => [
                    'name' => $tax_data['label'],
                    'singular_name' => $tax_data['label_singular'],
                    'add_new_item' => 'Add New ' . $tax_data['label_singular'],
                    'edit_item' => 'Edit ' . $tax_data['label_singular'],
                    'view_item' => 'View ' . $tax_data['label_singular'],
                    'all_items' => 'All ' . $tax_data['label'],
                    'search_items' => 'Search ' . $tax_data['label'],
                    'not_found' => 'No ' . strtolower($tax_data['label']) . ' found.',
                ],
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_admin_column' => true,
                'hierarchical' => false,
                'rewrite' => ['slug' => $tax_data['name']],
                'query_var' => true,
                'show_in_nav_menus' => true,
                'show_tagcloud' => true,
            ]);
            echo "   ✅ Registered taxonomy '{$tax_data['name']}' with WordPress for: " . implode(', ', $tax_data['post_types']) . "\n";
        }
        
        // Now register with Pods for additional management features
        $taxonomy_data = [
            'name' => $tax_data['name'],
            'label' => $tax_data['label'],
            'label_singular' => $tax_data['label_singular'],
            'type' => 'taxonomy',
            'storage' => 'taxonomy',
            'public' => 1,
            'show_ui' => 1,
            'hierarchical' => 0,
            'rewrite' => 1,
            'query_var' => 1,
            'show_tagcloud' => 1,
            'show_in_nav_menus' => 1,
            'show_admin_column' => 1
        ];
        
        // Add post type associations for Pods
        foreach (['car', 'dealer'] as $cpt) {
            if (in_array($cpt, $tax_data['post_types'])) {
                $taxonomy_data["built_in_post_types_$cpt"] = 1;
            } else {
                $taxonomy_data["built_in_post_types_$cpt"] = 0;
            }
        }
        
        $tax_id = pods_api()->save_pod($taxonomy_data);
        
        if (is_wp_error($tax_id)) {
            /** @var WP_Error $tax_id */
            echo "   ⚠️ Pods registration warning for '{$tax_data['name']}': " . $tax_id->get_error_message() . "\n";
        } else {
            echo "   ✅ Taxonomy '{$tax_data['name']}' registered with Pods (ID: $tax_id)\n";
        }
    }
    
    echo "✅ Featured image support enabled\n";
    
    // Fix taxonomy associations after creation
    fix_taxonomy_associations();
    
    return true;
}

/**
 * Fix taxonomy associations with post types
 * This ensures taxonomies are properly associated with CPTs
 */
function fix_taxonomy_associations() {
    echo "\n🔧 Fixing taxonomy associations...\n";
    
    // Define taxonomies and their associations
    $taxonomy_associations = [
        'car_brand' => ['car', 'dealer'],
        'car_body_type' => ['car'],
        'car_fuel_type' => ['car'],
        'car_transmission' => ['car'],
        'car_location' => ['car'],
        'dealer_location' => ['dealer']
    ];

    // Force the associations using register_taxonomy_for_object_type
    foreach ($taxonomy_associations as $taxonomy => $post_types) {
        if (taxonomy_exists($taxonomy)) {
            foreach ($post_types as $post_type) {
                $result = register_taxonomy_for_object_type($taxonomy, $post_type);
                if ($result) {
                    echo "   ✅ $taxonomy associated with $post_type\n";
                }
            }
        }
    }
    
    // Flush rewrite rules to ensure associations are properly registered
    flush_rewrite_rules(false);
    echo "   🔄 Rewrite rules flushed\n";
}

// Function to create dealers with sample data
function create_sample_dealers() {
    echo "\n🏢 Creating sample dealers...\n";
    
    $dealers_data = [
        [
            'title' => 'Premium Motors',
            'content' => 'Luxury car dealership specializing in premium vehicles with over 20 years of experience.',
            'phone' => '(555) 123-4567',
            'email' => 'info@premiummotors.com',
            'address' => '123 Premium Ave, Downtown, NY 10001',
            'website' => 'https://premiummotors.com',
            'rating' => 4.8,
            'established_year' => 2000,
            'dealer_location' => 'New York',
            'car_brand' => ['BMW', 'Mercedes-Benz', 'Audi'] // Luxury brands
        ],
        [
            'title' => 'City Auto Center',
            'content' => 'Your trusted neighborhood dealer with competitive prices and excellent service.',
            'phone' => '(555) 234-5678',
            'email' => 'sales@cityauto.com',
            'address' => '456 Main Street, City Center, CA 90210',
            'website' => 'https://cityautocenter.com',
            'rating' => 4.5,
            'established_year' => 1995,
            'dealer_location' => 'California',
            'car_brand' => ['Toyota', 'Honda', 'Nissan'] // General brands
        ],
        [
            'title' => 'Sports Car Depot',
            'content' => 'Specialists in high-performance sports cars and exotic vehicles.',
            'phone' => '(555) 345-6789',
            'email' => 'contact@sportscardepot.com',
            'address' => '789 Speed Lane, Racing District, FL 33101',
            'website' => 'https://sportscardepot.com',
            'rating' => 4.9,
            'established_year' => 2010,
            'dealer_location' => 'Florida',
            'car_brand' => ['Ferrari', 'Porsche', 'Lamborghini'] // Sports brands
        ],
        [
            'title' => 'Family Auto Sales',
            'content' => 'Quality used cars for families, with financing options and warranties.',
            'phone' => '(555) 456-7890',
            'email' => 'help@familyauto.com',
            'address' => '321 Family Road, Suburbia, TX 75001',
            'website' => 'https://familyautosales.com',
            'rating' => 4.3,
            'established_year' => 1988,
            'dealer_location' => 'Texas',
            'car_brand' => ['Ford', 'Chevrolet', 'Hyundai'] // Family brands
        ],
        [
            'title' => 'Electric Future Motors',
            'content' => 'Leading dealer in electric and hybrid vehicles, promoting sustainable transportation.',
            'phone' => '(555) 567-8901',
            'email' => 'info@electricfuture.com',
            'address' => '654 Green Street, Eco District, WA 98101',
            'website' => 'https://electricfuturemotors.com',
            'rating' => 4.7,
            'established_year' => 2015,
            'dealer_location' => 'Washington',
            'car_brand' => ['Tesla', 'Prius', 'Leaf'] // Electric brands
        ]
    ];
    
    $dealer_ids = [];
    
    foreach ($dealers_data as $dealer_data) {
        $post_data = [
            'post_title' => $dealer_data['title'],
            'post_content' => $dealer_data['content'],
            'post_status' => 'publish',
            'post_type' => 'dealer'
        ];
        
        $dealer_id = wp_insert_post($post_data);
        
        if (!is_wp_error($dealer_id)) {
            $dealer_ids[] = $dealer_id;
            
            // Add custom fields
            $custom_fields = ['phone', 'email', 'address', 'website', 'rating', 'established_year'];
            foreach ($custom_fields as $field) {
                if (isset($dealer_data[$field])) {
                    update_post_meta($dealer_id, $field, $dealer_data[$field]);
                }
            }
            
            // Add taxonomies
            if (isset($dealer_data['dealer_location'])) {
                $location_term = get_or_create_term($dealer_data['dealer_location'], 'dealer_location');
                if ($location_term) {
                    wp_set_object_terms($dealer_id, [$location_term->term_id], 'dealer_location');
                }
            }
            
            // Add car brands that this dealer specializes in
            if (isset($dealer_data['car_brand'])) {
                $brand_term_ids = [];
                foreach ($dealer_data['car_brand'] as $brand_name) {
                    $brand_term = get_or_create_term($brand_name, 'car_brand');
                    if ($brand_term) {
                        $brand_term_ids[] = $brand_term->term_id;
                    }
                }
                if (!empty($brand_term_ids)) {
                    wp_set_object_terms($dealer_id, $brand_term_ids, 'car_brand');
                }
            }
            
            echo "   ✅ Created dealer: {$dealer_data['title']}\n";
        }
    }
    
    echo "   📊 Created " . count($dealer_ids) . " dealers\n";
    return $dealer_ids;
}

// Check if 'car' Pod already exists
$existing_pod = pods_api()->load_pod(['name' => 'car']);

if (empty($existing_pod)) {
    // Create Pod and taxonomies
    $pod_created = create_car_pod();
    $dealer_pod_created = create_dealers_pod();
    create_car_taxonomies();
    
    if (!$pod_created || !$dealer_pod_created) {
        echo "❌ Failed to create Pods. Stopping execution.\n";
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

$old_dealers = get_posts([
    'post_type' => 'dealer',
    'post_status' => 'any', 
    'numberposts' => -1,
    'fields' => 'ids'
]);

foreach ($old_cars as $car_id) {
    wp_delete_post($car_id, true);
}

foreach ($old_dealers as $dealer_id) {
    wp_delete_post($dealer_id, true);
}

echo "   Removed: " . count($old_cars) . " old cars, " . count($old_dealers) . " old dealers\n";

// Create sample dealers first
$dealer_ids = create_sample_dealers();

// Generate data directly from images
function generate_car_data_from_images() {
    $images_dir = __DIR__ . '/images';
    $images = glob($images_dir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    if (empty($images)) {
        return [];
    }
    
    $cars_data = [];
    $car_locations = ['New York', 'California', 'Florida', 'Texas', 'Washington', 'Nevada', 'Arizona'];
    
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
                'car_location' => $car_locations[array_rand($car_locations)], // Car availability location
                'post_title' => $title,
                'post_content' => "<p>Excellent <strong>{$title}</strong> in pristine condition.</p>\n<p><strong>Specifications:</strong></p>\n<ul>\n<li>Engine: {$engine_size}L " . $fuels[array_rand($fuels)] . "</li>\n<li>Power: {$power_hp} HP</li>\n<li>Transmission: " . $transmissions[array_rand($transmissions)] . "</li>\n<li>Doors: {$doors}</li>\n<li>Color: {$color}</li>\n</ul>\n<p>Well-maintained vehicle with complete service history and documentation.</p>",
                'car_brand' => [['name' => $brand, 'slug' => $brand, 'taxonomy' => 'car_brand']],
                'car_body_type' => [['name' => $body_type, 'slug' => $body_type, 'taxonomy' => 'car_body_type']],
                'car_fuel_type' => [['name' => $fuels[array_rand($fuels)], 'slug' => $fuels[array_rand($fuels)], 'taxonomy' => 'car_fuel_type']],
                'car_transmission' => [['name' => $transmissions[array_rand($transmissions)], 'slug' => $transmissions[array_rand($transmissions)], 'taxonomy' => 'car_transmission']],
                'car_location_tax' => [['name' => $car_locations[array_rand($car_locations)], 'slug' => $car_locations[array_rand($car_locations)], 'taxonomy' => 'car_location']]
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
            /** @var WP_Error $result */
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
function import_car_simple($car_data, $available_dealers = []) {
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
    
    // Associate with a dealer that specializes in the car's brand
    if (!empty($available_dealers) && isset($car_data['car_brand'][0]['name'])) {
        $car_brand = $car_data['car_brand'][0]['name'];
        $matching_dealers = [];
        
        // Find dealers that specialize in this brand
        foreach ($available_dealers as $dealer_id) {
            $dealer_brands = wp_get_post_terms($dealer_id, 'car_brand', ['fields' => 'names']);
            if (!empty($dealer_brands) && in_array($car_brand, $dealer_brands)) {
                $matching_dealers[] = $dealer_id;
            }
        }
        
        // If no dealer specializes in this brand, use any dealer
        if (empty($matching_dealers)) {
            $matching_dealers = $available_dealers;
        }
        
        if (!empty($matching_dealers)) {
            $selected_dealer = $matching_dealers[array_rand($matching_dealers)];
            // Use Pods API to save relationship field
            $car_pod = pods('car', $post_id);
            if ($car_pod) {
                $car_pod->save('dealer_id', $selected_dealer);
            } else {
                // Fallback to update_post_meta if Pods object fails
                update_post_meta($post_id, 'dealer_id', $selected_dealer);
            }
        }
    }

    // Add car taxonomies
    $taxonomies_data = [
        'car_brand' => 'car_brand',
        'car_body_type' => 'car_body_type', 
        'car_fuel_type' => 'car_fuel_type',
        'car_transmission' => 'car_transmission',
        'car_location_tax' => 'car_location'
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
                    /** @var WP_Error $result */
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
        $car_id = import_car_simple($car_data, $dealer_ids);
        
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
$taxonomies = ['car_brand', 'car_body_type', 'car_fuel_type', 'car_transmission', 'car_location', 'dealer_location'];
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    if (!is_wp_error($terms)) {
        echo "   - $taxonomy: " . count($terms) . " terms\n";
    }
}

echo "\n🎉 Import completed!\n\n";
echo "📋 Next steps:\n";
echo "1. Go to wp-admin/edit.php?post_type=car to see the cars\n";
echo "2. Go to wp-admin/edit.php?post_type=dealer to see the dealers\n";
echo "3. Test search and filters on frontend\n";
echo "4. Cars are associated with dealers by location\n";

// Show some examples of created content
echo "\n📝 Sample dealers created:\n";
$recent_dealers = get_posts([
    'post_type' => 'dealer',
    'numberposts' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
]);

foreach ($recent_dealers as $dealer) {
    $location_terms = wp_get_post_terms($dealer->ID, 'dealer_location', ['fields' => 'names']);
    $brand_terms = wp_get_post_terms($dealer->ID, 'car_brand', ['fields' => 'names']);
    $location = !empty($location_terms) ? $location_terms[0] : 'No location';
    $brands = !empty($brand_terms) ? implode(', ', array_slice($brand_terms, 0, 2)) : 'No brands';
    echo "   - {$dealer->post_title} ({$location}) - Specializes in: {$brands}\n";
}

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
