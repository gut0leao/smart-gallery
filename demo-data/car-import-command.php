<?php
/**
 * Car Demo Data Import Command for WordPress
 * 
 * Creates a complete car catalog demo using Pods Framework:
 * - Custom Post Type: Car
 * - Taxonomies: Brand, Body Type, Fuel Type, Transmission
 * - Sample data from CSV with images
 * 
 * Usage: wp car-demo import [--dry-run] [--file=cars.csv]
 */

/**
 * Main import command class
 */
class Car_Import_Command {

    /**
     * Import cars from CSV file
     *
     * ## OPTIONS
     *
     * [--file=<file>]
     * : Path to CSV file
     * ---
     * default: demo-data/cars.csv
     * ---
     *
     * [--dry-run]
     * : Run without making changes
     *
     * ## EXAMPLES
     *
     *     wp car-demo import
     *     wp car-demo import --file=custom-cars.csv
     *     wp car-demo import --dry-run
     *
     * @when after_wp_load
     */
    public function import( $args, $assoc_args ) {
        $file = $assoc_args['file'] ?? 'demo-data/cars.csv';
        $dry_run = isset( $assoc_args['dry-run'] );

        if ( $dry_run ) {
            WP_CLI::line( '🔍 DRY RUN MODE - No changes will be made' );
        }

        // Validate file exists
        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "CSV file not found: {$file}" );
        }

        WP_CLI::line( "📂 Reading CSV file: {$file}" );

        // Run import steps in order
        $this->setup_post_types( $dry_run );
        $this->setup_taxonomies( $dry_run );
        $this->connect_taxonomies_to_cpt( $dry_run );
        $this->import_cars_from_csv( $file, $dry_run );

        WP_CLI::success( 'Import completed!' );
    }

    /**
     * Create Car Custom Post Type with Pods
     */
    private function setup_post_types( $dry_run = false ) {
        WP_CLI::line( '🚗 Setting up Car post type...' );

        if ( $dry_run ) {
            WP_CLI::line( '   [DRY RUN] Would create "car" post type' );
            return;
        }

        // Ensure Pods is available
        if ( ! function_exists( 'pods_api' ) ) {
            WP_CLI::error( 'Pods plugin is not active!' );
        }

        $pods_api = pods_api();

        // Skip if already exists
        if ( $pods_api->load_pod( [ 'name' => 'car' ] ) ) {
            WP_CLI::line( '   ⚠️  Car post type already exists' );
            return;
        }

        // Create car post type with all necessary fields
        $car_pod_params = [
            'name' => 'car',
            'label' => 'Cars',
            'type' => 'post_type',
            'storage' => 'meta',
            'options' => [
                'label_singular' => 'Car',
                'public' => '1',
                'show_ui' => '1',
                'supports_title' => '1',
                'supports_editor' => '1',
                'supports_thumbnail' => '1',
                'supports_excerpt' => '1',
                'supports_custom_fields' => '1',
                'menu_icon' => 'dashicons-car',
                'rewrite' => '1',
                'rewrite_slug' => 'cars',
                'has_archive' => '1',
                'show_in_rest' => '1',
            ],
            'fields' => $this->get_car_fields()
        ];

        $result = $pods_api->save_pod( $car_pod_params );
        
        if ( $result ) {
            WP_CLI::line( '   ✅ Car post type created successfully' );
        } else {
            WP_CLI::error( 'Failed to create Car post type' );
        }
    }

    /**
     * Get car custom fields configuration
     */
    private function get_car_fields() {
        return [
            [
                'name' => 'price',
                'label' => 'Price',
                'type' => 'currency',
                'options' => [
                    'currency_format' => 'usd',
                    'currency_format_sign' => '$',
                    'currency_format_placement' => 'before',
                ]
            ],
            [
                'name' => 'year',
                'label' => 'Year',
                'type' => 'number',
                'options' => [ 'number_format_type' => 'number', 'number_decimals' => '0' ]
            ],
            [
                'name' => 'mileage',
                'label' => 'Mileage',
                'type' => 'number',
                'options' => [ 'number_format_type' => 'number', 'number_decimals' => '0' ]
            ],
            [
                'name' => 'engine_size',
                'label' => 'Engine Size',
                'type' => 'number',
                'options' => [ 'number_format_type' => 'number', 'number_decimals' => '1' ]
            ],
            [
                'name' => 'power_hp',
                'label' => 'Power (HP)',
                'type' => 'number',
                'options' => [ 'number_format_type' => 'number', 'number_decimals' => '0' ]
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
                'options' => [ 'number_format_type' => 'number', 'number_decimals' => '0' ]
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
                'options' => [
                    'pick_format_type' => 'single',
                    'pick_format_single' => 'dropdown',
                    'pick_custom' => 'available|Available\nsold|Sold\nreserved|Reserved'
                ]
            ],
            [
                'name' => 'condition',
                'label' => 'Condition',
                'type' => 'pick',
                'options' => [
                    'pick_format_type' => 'single',
                    'pick_format_single' => 'dropdown',
                    'pick_custom' => 'new|New\nseminew|Semi-New\nused|Used'
                ]
            ]
        ];
    }

    /**
     * Create taxonomies for car categorization
     */
    private function setup_taxonomies( $dry_run = false ) {
        WP_CLI::line( '🏷️  Setting up taxonomies...' );

        if ( $dry_run ) {
            WP_CLI::line( '   [DRY RUN] Would create taxonomies: brand, body_type, fuel_type, transmission' );
            return;
        }

        $pods_api = pods_api();
        $taxonomies = $this->get_taxonomy_definitions();

        foreach ( $taxonomies as $taxonomy ) {
            // Skip if already exists
            if ( $pods_api->load_pod( [ 'name' => $taxonomy['name'] ] ) ) {
                WP_CLI::line( "   ⚠️  Taxonomy {$taxonomy['name']} already exists" );
                continue;
            }

            // Create taxonomy
            $tax_params = [
                'name' => $taxonomy['name'],
                'label' => $taxonomy['label'],
                'type' => 'taxonomy',
                'storage' => 'none',
                'options' => [
                    'label_singular' => $taxonomy['label_singular'],
                    'public' => '1',
                    'show_ui' => '1',
                    'show_tagcloud' => '1',
                    'hierarchical' => '0',
                    'rewrite' => '1',
                    'rewrite_slug' => str_replace( 'car_', '', $taxonomy['name'] ),
                    'show_in_rest' => '1',
                ]
            ];

            $result = $pods_api->save_pod( $tax_params );
            
            if ( $result ) {
                WP_CLI::line( "   ✅ Created taxonomy: {$taxonomy['name']}" );
            } else {
                WP_CLI::line( "   ❌ Failed to create taxonomy: {$taxonomy['name']}" );
            }
        }
    }

    /**
     * Get taxonomy definitions
     */
    private function get_taxonomy_definitions() {
        return [
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
    }

    /**
     * Connect taxonomies to car post type
     */
    private function connect_taxonomies_to_cpt( $dry_run = false ) {
        WP_CLI::line( '🔗 Connecting taxonomies to car post type...' );
        
        if ( $dry_run ) {
            WP_CLI::line( '   [DRY RUN] Would connect all taxonomies to car post type' );
            return;
        }

        $taxonomy_names = $this->get_taxonomy_names();
        
        foreach ( $taxonomy_names as $taxonomy_name ) {
            // Ensure taxonomy is registered in WordPress
            $this->ensure_taxonomy_registration( $taxonomy_name );
            
            // Connect to car post type
            register_taxonomy_for_object_type( $taxonomy_name, 'car' );
            WP_CLI::line( "   ✅ Connected {$taxonomy_name} to car post type" );
        }
        
        // Refresh WordPress
        flush_rewrite_rules();
        WP_CLI::line( "   🎉 All taxonomies connected successfully!" );
    }

    /**
     * Ensure taxonomy is registered in WordPress (fallback for Pods)
     */
    private function ensure_taxonomy_registration( $taxonomy_name ) {
        if ( taxonomy_exists( $taxonomy_name ) ) {
            return; // Already registered
        }

        WP_CLI::line( "   ⚠️  Registering {$taxonomy_name} directly in WordPress..." );
        
        // Create readable label from taxonomy name
        $label = ucwords( str_replace( ['car_', '_'], ['', ' '], $taxonomy_name ) );
        
        register_taxonomy( $taxonomy_name, 'car', [
            'labels' => [
                'name' => $label,
                'singular_name' => $label,
            ],
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => false,
            'rewrite' => [ 'slug' => str_replace( 'car_', '', $taxonomy_name ) ],
        ] );
    }

    /**
     * Get list of taxonomy names
     */
    private function get_taxonomy_names() {
        return [ 'car_brand', 'car_body_type', 'car_fuel_type', 'car_transmission' ];
    }

    /**
     * Import cars from CSV file
     */
    private function import_cars_from_csv( $file, $dry_run = false ) {
        WP_CLI::line( '📊 Importing cars from CSV...' );

        // Open and validate CSV file
        $handle = fopen( $file, 'r' );
        if ( ! $handle ) {
            WP_CLI::error( 'Could not open CSV file' );
        }

        $header = fgetcsv( $handle );
        if ( ! $header ) {
            WP_CLI::error( 'Could not read CSV header' );
        }

        // Process each row
        $imported = 0;
        $skipped = 0;

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $car_data = array_combine( $header, $row );
            
            if ( $dry_run ) {
                WP_CLI::line( "   [DRY RUN] Would import: {$car_data['car_name']}" );
                $imported++;
                continue;
            }

            try {
                $this->create_car_post( $car_data );
                WP_CLI::line( "   ✅ Imported: {$car_data['car_name']}" );
                $imported++;
            } catch ( Exception $e ) {
                WP_CLI::line( "   ❌ Failed: {$car_data['car_name']} - {$e->getMessage()}" );
                $skipped++;
            }
        }

        fclose( $handle );

        // Show summary
        WP_CLI::line( '' );
        WP_CLI::line( "📈 Import Summary:" );
        WP_CLI::line( "   Imported: {$imported}" );
        WP_CLI::line( "   Skipped: {$skipped}" );
    }

    /**
     * Create a single car post from CSV data
     */
    private function create_car_post( $car_data ) {
        // Check for duplicates
        $existing = get_posts( [
            'post_type' => 'car',
            'title' => $car_data['car_name'],
            'posts_per_page' => 1,
            'post_status' => 'any'
        ] );

        if ( $existing ) {
            throw new Exception( 'Car already exists' );
        }

        // Create the post
        $post_id = wp_insert_post( [
            'post_title' => $car_data['car_name'],
            'post_content' => $car_data['description'],
            'post_type' => 'car',
            'post_status' => 'publish',
            'meta_input' => $this->prepare_car_meta( $car_data )
        ] );

        if ( is_wp_error( $post_id ) ) {
            throw new Exception( $post_id->get_error_message() );
        }

        // Set taxonomies
        $this->assign_car_taxonomies( $post_id, $car_data );

        // Set featured image
        $this->set_featured_image( $post_id, $car_data['featured_image'] );

        return $post_id;
    }

    /**
     * Prepare car metadata from CSV data
     */
    private function prepare_car_meta( $car_data ) {
        return [
            'price' => intval( $car_data['price'] ),
            'year' => intval( $car_data['year'] ),
            'mileage' => intval( $car_data['mileage'] ),
            'engine_size' => floatval( $car_data['engine_size'] ),
            'power_hp' => intval( $car_data['power_hp'] ),
            'color' => $car_data['color'],
            'doors' => intval( $car_data['doors'] ),
            'model' => $car_data['model'],
            'car_status' => $car_data['car_status'],
            'condition' => $car_data['condition'],
            'featured_image' => $car_data['featured_image'],
        ];
    }

    /**
     * Assign taxonomies to car post
     */
    private function assign_car_taxonomies( $post_id, $car_data ) {
        $taxonomy_mappings = [
            'car_brand' => $car_data['brand'],
            'car_body_type' => $car_data['body_type'],
            'car_fuel_type' => $car_data['fuel_type'],
            'car_transmission' => $car_data['transmission']
        ];

        foreach ( $taxonomy_mappings as $taxonomy => $term_name ) {
            $this->set_taxonomy_term( $post_id, $term_name, $taxonomy );
        }
    }

    /**
     * Set taxonomy term for a post (creates term if needed)
     */
    private function set_taxonomy_term( $post_id, $term_name, $taxonomy ) {
        if ( empty( $term_name ) ) {
            return;
        }

        // Try to find existing term
        $term = get_term_by( 'name', $term_name, $taxonomy );
        
        if ( ! $term ) {
            // Create new term
            $term_result = wp_insert_term( $term_name, $taxonomy );
            
            if ( is_wp_error( $term_result ) ) {
                WP_CLI::line( "   ⚠️  Failed to create term '{$term_name}' in {$taxonomy}: " . $term_result->get_error_message() );
                return;
            }
            
            $term_id = $term_result['term_id'];
        } else {
            $term_id = $term->term_id;
        }

        // Assign term to post
        $result = wp_set_object_terms( $post_id, $term_id, $taxonomy );
        
        if ( is_wp_error( $result ) ) {
            WP_CLI::line( "   ⚠️  Failed to assign term '{$term_name}' to post {$post_id}: " . $result->get_error_message() );
        }
    }

    /**
     * Set featured image from file
     */
    private function set_featured_image( $post_id, $image_filename ) {
        $image_path = ABSPATH . 'demo-data/images/' . $image_filename;
        
        if ( ! file_exists( $image_path ) ) {
            return false;
        }

        // Check if image already exists in media library
        $existing_image = get_posts( [
            'post_type' => 'attachment',
            'meta_query' => [
                [
                    'key' => '_wp_attached_file',
                    'value' => $image_filename,
                    'compare' => 'LIKE'
                ]
            ]
        ] );

        if ( $existing_image ) {
            set_post_thumbnail( $post_id, $existing_image[0]->ID );
            return true;
        }

        // Upload image
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $upload = wp_upload_bits( $image_filename, null, file_get_contents( $image_path ) );
        
        if ( $upload['error'] ) {
            return false;
        }

        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title' => pathinfo( $image_filename, PATHINFO_FILENAME ),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
        
        if ( is_wp_error( $attachment_id ) ) {
            return false;
        }

        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );
        
        set_post_thumbnail( $post_id, $attachment_id );
        
        return true;
    }

    /**
     * Clean up all imported cars and related data
     *
     * ## OPTIONS
     *
     * [--confirm]
     * : Confirm deletion
     *
     * ## EXAMPLES
     *
     *     wp car-demo cleanup --confirm
     *
     * @when after_wp_load
     */
    public function cleanup( $args, $assoc_args ) {
        if ( ! isset( $assoc_args['confirm'] ) ) {
            WP_CLI::error( 'This will delete all cars and related data. Use --confirm to proceed.' );
        }

        WP_CLI::line( '🗑️  Cleaning up imported cars...' );

        // Delete all car posts
        $cars = get_posts( [
            'post_type' => 'car',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ] );

        foreach ( $cars as $car ) {
            wp_delete_post( $car->ID, true );
        }

        WP_CLI::line( "   ✅ Deleted " . count($cars) . " car posts" );

        // Clean up taxonomies
        $taxonomies = [ 'car_brand', 'car_body_type', 'car_fuel_type', 'car_transmission' ];
        foreach ( $taxonomies as $taxonomy ) {
            $terms = get_terms( [
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ] );
            
            if ( ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    wp_delete_term( $term->term_id, $taxonomy );
                }
            }
        }

        WP_CLI::success( 'Cleanup completed!' );
    }
    
    /**
     * Debug taxonomies connections
     *
     * ## EXAMPLES
     *
     *     wp car-demo debug
     *
     * @when after_wp_load
     */
    public function debug( $args, $assoc_args ) {
        WP_CLI::line( '🔍 Debugging taxonomy connections...' );
        
        $taxonomies = [ 'car_brand', 'car_body_type', 'car_fuel_type', 'car_transmission' ];
        $pods_api = pods_api();
        
        foreach ( $taxonomies as $taxonomy_name ) {
            $taxonomy_pod = $pods_api->load_pod( [ 'name' => $taxonomy_name ] );
            
            if ( $taxonomy_pod ) {
                WP_CLI::line( "📋 {$taxonomy_name}:" );
                WP_CLI::line( "   ID: {$taxonomy_pod['id']}" );
                WP_CLI::line( "   Type: {$taxonomy_pod['type']}" );
                WP_CLI::line( "   Object: " . ( isset( $taxonomy_pod['object'] ) ? json_encode( $taxonomy_pod['object'] ) : 'not set' ) );
                
                // Show full pod data for debugging
                if ( isset( $taxonomy_pod['object'] ) && !empty( $taxonomy_pod['object'] ) ) {
                    WP_CLI::line( "   Full Object Data: " . var_export( $taxonomy_pod['object'], true ) );
                }
                
                // Check WordPress registration
                $wp_tax = get_taxonomy( $taxonomy_name );
                if ( $wp_tax ) {
                    WP_CLI::line( "   WP Object Types: " . json_encode( $wp_tax->object_type ) );
                } else {
                    WP_CLI::line( "   ❌ Not registered in WordPress" );
                }
                
                // Check direct WordPress registration
                if ( taxonomy_exists( $taxonomy_name ) ) {
                    $taxonomy_obj = get_taxonomy( $taxonomy_name );
                    WP_CLI::line( "   WP Taxonomy Exists: YES" );
                    WP_CLI::line( "   WP Post Types: " . json_encode( $taxonomy_obj->object_type ) );
                } else {
                    WP_CLI::line( "   WP Taxonomy Exists: NO" );
                }
                
                WP_CLI::line( "" );
            } else {
                WP_CLI::line( "❌ {$taxonomy_name} not found" );
            }
        }
        
        // Check car CPT
        $car_pod = $pods_api->load_pod( [ 'name' => 'car' ] );
        if ( $car_pod ) {
            WP_CLI::line( "🚗 Car CPT:" );
            WP_CLI::line( "   ID: {$car_pod['id']}" );
            WP_CLI::line( "   Type: {$car_pod['type']}" );
            
            // Show full car pod object data
            if ( isset( $car_pod['object'] ) ) {
                WP_CLI::line( "   Object: " . json_encode( $car_pod['object'] ) );
            }
            
            // Check WordPress CPT registration
            if ( post_type_exists( 'car' ) ) {
                $post_type_obj = get_post_type_object( 'car' );
                WP_CLI::line( "   WP CPT Exists: YES" );
                WP_CLI::line( "   WP CPT Taxonomies: " . json_encode( get_object_taxonomies( 'car' ) ) );
            } else {
                WP_CLI::line( "   WP CPT Exists: NO" );
            }
        }
        
        WP_CLI::success( 'Debug completed!' );
    }
}

// For direct usage without WP-CLI command registration
if ( class_exists( 'WP_CLI' ) ) {
    WP_CLI::add_command( 'car-demo', 'Car_Import_Command' );
}
