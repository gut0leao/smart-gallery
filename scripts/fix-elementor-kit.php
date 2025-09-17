<?php
/**
 * Fix Elementor Default Kit
 * Recreates the missing Elementor Default Kit
 */

echo "🎨 Elementor Default Kit Fix\n";
echo "===========================\n\n";

// Check if Elementor is active
if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!is_plugin_active('elementor/elementor.php')) {
    echo "❌ Elementor plugin is not active\n";
    exit(1);
}

// Check if kit already exists
$kit_id = get_option('elementor_active_kit');
if ($kit_id && get_post($kit_id)) {
    echo "✅ Elementor Default Kit already exists (ID: $kit_id)\n";
    echo "🎯 Kit is properly configured\n";
    exit(0);
}

echo "🔧 Creating new Elementor Default Kit...\n";

// Create new kit
if (class_exists('Elementor\Plugin')) {
    try {
        // Initialize Elementor if needed
        if (method_exists('\Elementor\Plugin', 'instance')) {
            $elementor = \Elementor\Plugin::instance();
            
            if (method_exists($elementor, 'init_common')) {
                $elementor->init_common();
            }
            
            // Try using kits manager
            if (isset($elementor->kits_manager) && method_exists($elementor->kits_manager, 'create_default')) {
                $kit_id = $elementor->kits_manager->create_default();
                
                if ($kit_id && !is_wp_error($kit_id)) {
                    echo "✅ Elementor Default Kit created successfully (ID: $kit_id)\n";
                    
                    // Set as active kit
                    update_option('elementor_active_kit', $kit_id);
                    
                    echo "🎯 Kit activated successfully\n";
                    echo "\n💡 Default Kit is now ready!\n";
                    exit(0);
                } else {
                    if (is_wp_error($kit_id)) {
                        echo "❌ Failed to create Default Kit: " . $kit_id->get_error_message() . "\n";
                    } else {
                        echo "❌ Failed to create Default Kit: Unknown error\n";
                    }
                }
            } else {
                echo "❌ Elementor Kits Manager not available or method not found\n";
            }
        } else {
            echo "❌ Elementor Plugin instance method not found\n";
        }
    } catch (Exception $e) {
        echo "❌ Error creating kit: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Elementor Plugin class not found\n";
}

// Fallback: Create a basic kit manually
echo "\n🔧 Trying manual kit creation...\n";

try {
    // Create a new post as a kit
    $kit_post = array(
        'post_title'    => 'Default Kit',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'elementor_library',
        'meta_input'    => array(
            '_elementor_template_type' => 'kit',
            '_elementor_edit_mode' => 'builder',
        )
    );
    
    $kit_id = wp_insert_post($kit_post);
    
    if ($kit_id && !is_wp_error($kit_id)) {
        // Set as active kit
        update_option('elementor_active_kit', $kit_id);
        
        echo "✅ Manual Default Kit created successfully (ID: $kit_id)\n";
        echo "🎯 Kit activated successfully\n";
        echo "\n💡 Default Kit is now ready!\n";
        exit(0);
    } else {
        if (is_wp_error($kit_id)) {
            echo "❌ Manual creation failed: " . $kit_id->get_error_message() . "\n";
        } else {
            echo "❌ Manual creation failed: Unknown error\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Manual creation error: " . $e->getMessage() . "\n";
}

echo "\n❌ All automatic methods failed\n";
echo "\n🔧 Manual Solution:\n";
echo "   1. Go to WordPress Admin > Elementor > My Templates\n";
echo "   2. Click 'Add New' > 'Theme Builder' > 'Kit'\n";
echo "   3. Name it 'Default Kit' and save\n";
echo "   4. Go to Elementor > Settings > General\n";
echo "   5. Select your new kit as 'Default Kit'\n";
echo "\n";

exit(1);
?>