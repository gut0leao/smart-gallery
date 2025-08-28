<?php
/**
 * Car import functionality for WordPress
 */

/**
 * Import cars from CSV file to WordPress
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

        // Check if file exists
        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "CSV file not found: {$file}" );
        }

        WP_CLI::line( "📂 Reading CSV file: {$file}" );

        // Setup CPT and taxonomies
        $this->setup_post_types( $dry_run );
        $this->setup_taxonomies( $dry_run );

        // Import cars
        $this->import_cars_from_csv( $file, $dry_run );

        WP_CLI::success( 'Import completed!' );
    }

    /**
     * Setup car custom post type in Pods
     */
    private function setup_post_types( $dry_run = false ) {
        WP_CLI::line( '🚗 Setting up Car post type in Pods...' );

        if ( $dry_run ) {
            WP_CLI::line( '   [DRY RUN] Would create "car" post type in Pods' );
            return;
        }

        // Check if Pods is active
        if ( ! function_exists( 'pods_api' ) ) {
            WP_CLI::error( 'Pods plugin is not active!' );
        }

        $pods_api = pods_api();

        // Check if car post type already exists
        $existing_pod = $pods_api->load_pod( [ 'name' => 'car' ] );
        
        if ( $existing_pod ) {
            WP_CLI::line( '   ⚠️  Car post type already exists in Pods' );
            return;
        }

        // Create Car post type in Pods
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
            'fields' => [
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
                    'options' => [
                        'number_format_type' => 'number',
                        'number_decimals' => '0'
                    ]
                ],
                [
                    'name' => 'mileage',
                    'label' => 'Mileage',
                    'type' => 'number',
                    'options' => [
                        'number_format_type' => 'number',
                        'number_decimals' => '0'
                    ]
                ],
                [
                    'name' => 'engine_size',
                    'label' => 'Engine Size',
                    'type' => 'number',
                    'options' => [
                        'number_format_type' => 'number',
                        'number_decimals' => '1'
                    ]
                ],
                [
                    'name' => 'power_hp',
                    'label' => 'Power (HP)',
                    'type' => 'number',
                    'options' => [
                        'number_format_type' => 'number',
                        'number_decimals' => '0'
                    ]
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
                    'options' => [
                        'number_format_type' => 'number',
                        'number_decimals' => '0'
                    ]
                ],
                [
                    'name' => 'model',
                    'label' => 'Model',
                    'type' => 'text'
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'pick',
                    'options' => [
                        'pick_format_type' => 'single',
                        'pick_format_single' => 'dropdown',
                        'pick_custom' => 'available|Available
sold|Sold
reserved|Reserved'
                    ]
                ],
                [
                    'name' => 'condition',
                    'label' => 'Condition',
                    'type' => 'pick',
                    'options' => [
                        'pick_format_type' => 'single',
                        'pick_format_single' => 'dropdown',
                        'pick_custom' => 'new|New
seminew|Semi-New
used|Used'
                    ]
                ]
            ]
        ];

        $car_pod_id = $pods_api->save_pod( $car_pod_params );
        
        if ( ! $car_pod_id ) {
            WP_CLI::error( 'Failed to create Car post type in Pods' );
        }

        WP_CLI::line( '   ✅ Car post type created in Pods' );
    }

    /**
     * Setup taxonomies in Pods
     */
    private function setup_taxonomies( $dry_run = false ) {
        WP_CLI::line( '🏷️  Setting up taxonomies in Pods...' );

        if ( $dry_run ) {
            WP_CLI::line( '   [DRY RUN] Would create taxonomies in Pods: brand, category, fuel_type, transmission' );
            return;
        }

        $pods_api = pods_api();

        $taxonomies = [
            [
                'name' => 'car_brand',
                'label' => 'Car Brands',
                'label_singular' => 'Car Brand',
                'object' => [ 'car' ]
            ],
            [
                'name' => 'car_category', 
                'label' => 'Car Categories',
                'label_singular' => 'Car Category',
                'object' => [ 'car' ]
            ],
            [
                'name' => 'car_fuel_type',
                'label' => 'Fuel Types',
                'label_singular' => 'Fuel Type', 
                'object' => [ 'car' ]
            ],
            [
                'name' => 'car_transmission',
                'label' => 'Transmissions',
                'label_singular' => 'Transmission',
                'object' => [ 'car' ]
            ]
        ];

        foreach ( $taxonomies as $taxonomy ) {
            // Check if taxonomy already exists
            $existing_tax = $pods_api->load_pod( [ 'name' => $taxonomy['name'] ] );
            
            if ( $existing_tax ) {
                WP_CLI::line( "   ⚠️  Taxonomy {$taxonomy['name']} already exists" );
                continue;
            }

            $tax_params = [
                'name' => $taxonomy['name'],
                'label' => $taxonomy['label'],
                'type' => 'taxonomy',
                'storage' => 'none',
                'object' => $taxonomy['object'],
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

            $tax_id = $pods_api->save_pod( $tax_params );
            
            if ( ! $tax_id ) {
                WP_CLI::line( "   ❌ Failed to create taxonomy: {$taxonomy['name']}" );
            } else {
                WP_CLI::line( "   ✅ Created taxonomy: {$taxonomy['name']}" );
            }
        }
    }

    /**
     * Import cars from CSV
     */
    private function import_cars_from_csv( $file, $dry_run = false ) {
        WP_CLI::line( '📊 Importing cars from CSV...' );

        $handle = fopen( $file, 'r' );
        if ( ! $handle ) {
            WP_CLI::error( 'Could not open CSV file' );
        }

        // Read header
        $header = fgetcsv( $handle );
        if ( ! $header ) {
            WP_CLI::error( 'Could not read CSV header' );
        }

        $imported = 0;
        $skipped = 0;

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $data = array_combine( $header, $row );
            
            if ( $dry_run ) {
                WP_CLI::line( "   [DRY RUN] Would import: {$data['name']}" );
                $imported++;
                continue;
            }

            try {
                $this->create_car_post( $data );
                WP_CLI::line( "   ✅ Imported: {$data['name']}" );
                $imported++;
            } catch ( Exception $e ) {
                WP_CLI::line( "   ❌ Failed: {$data['name']} - {$e->getMessage()}" );
                $skipped++;
            }
        }

        fclose( $handle );

        WP_CLI::line( '' );
        WP_CLI::line( "📈 Import Summary:" );
        WP_CLI::line( "   Imported: {$imported}" );
        WP_CLI::line( "   Skipped: {$skipped}" );
    }

    /**
     * Create a car post from CSV data
     */
    private function create_car_post( $data ) {
        // Check if post already exists
        $existing = get_posts( [
            'post_type' => 'car',
            'title' => $data['name'],
            'posts_per_page' => 1,
            'post_status' => 'any'
        ] );

        if ( $existing ) {
            throw new Exception( 'Car already exists' );
        }

        // Create post
        $post_id = wp_insert_post( [
            'post_title' => $data['name'],
            'post_content' => $data['description'],
            'post_type' => 'car',
            'post_status' => 'publish',
            'meta_input' => [
                'price' => intval( $data['price'] ),
                'year' => intval( $data['year'] ),
                'mileage' => intval( $data['mileage'] ),
                'engine_size' => floatval( $data['engine_size'] ),
                'power_hp' => intval( $data['power_hp'] ),
                'color' => $data['color'],
                'doors' => intval( $data['doors'] ),
                'model' => $data['model'],
                'status' => $data['status'],
                'condition' => $data['condition'],
                'featured_image' => $data['featured_image'],
            ]
        ] );

        if ( is_wp_error( $post_id ) ) {
            throw new Exception( $post_id->get_error_message() );
        }

        // Set taxonomies
        wp_set_object_terms( $post_id, $data['brand'], 'car_brand' );
        wp_set_object_terms( $post_id, $data['category'], 'car_category' );
        wp_set_object_terms( $post_id, $data['fuel_type'], 'car_fuel_type' );
        wp_set_object_terms( $post_id, $data['transmission'], 'car_transmission' );

        // Set featured image if exists
        $this->set_featured_image( $post_id, $data['featured_image'] );

        return $post_id;
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

        WP_CLI::line( "   ✅ Deleted {count($cars)} car posts" );

        // Clean up taxonomies
        $taxonomies = [ 'car_brand', 'car_category', 'car_fuel_type', 'car_transmission' ];
        foreach ( $taxonomies as $taxonomy ) {
            $terms = get_terms( [
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ] );
            
            foreach ( $terms as $term ) {
                wp_delete_term( $term->term_id, $taxonomy );
            }
        }

        WP_CLI::success( 'Cleanup completed!' );
    }
}

// For direct usage without WP-CLI command registration
if ( class_exists( 'WP_CLI' ) ) {
    WP_CLI::add_command( 'car-demo', 'Car_Import_Command' );
}
