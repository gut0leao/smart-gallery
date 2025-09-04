<?php
/**
 * ===================================================================
 * 🔥 WP-CLI COMMAND FOR COMPLETE PODS RESET
 * ===================================================================
 * 
 * 🎯 REPLICATES EXACTLY: "Pods Admin > Settings > Cleanup & Reset > Reset Pods entirely"
 * 
 * 📋 USAGE:
 *   ./demo-data/pods-reset.sh        # Interactive script with confirmations
 *   wp eval-file demo-data/pods-reset.php        # Dry run mode (safe analysis only)
 * 
 * ===================================================================
 */

echo "🔥 PODS COMPLETE RESET TOOL\n";
echo "==========================\n\n";

global $wpdb;

// Disable interactive prompts when running through script
if (defined('PODS_EXECUTE_RESET')) {
    define('DOING_AJAX', true);
    $_POST['action'] = 'wp-cli-reset';
    $_GET['force_delete_kit'] = '1'; // Force Elementor kit deletion
    $_REQUEST['force_delete_kit'] = '1';
}

// 📊 INITIAL ANALYSIS
echo "📊 CURRENT ENVIRONMENT ANALYSIS:\n";
echo "============================\n";

// Find Pods CPTs
$all_cpts = get_post_types(['_builtin' => false], 'names');
$pods_cpts = [];
$user_cpts = [];
$internal_cpts = [];

foreach ($all_cpts as $cpt) {
    if (strpos($cpt, '_pods_') === 0) {
        $internal_cpts[] = $cpt;
    } else {
        // Check if it's a CPT created by Pods
        $pod = pods($cpt, false, false);
        if ($pod && $pod->valid()) {
            $user_cpts[] = $cpt;
        } elseif (in_array($cpt, ['car'])) { 
            // Fallback for known CPTs (in case Pods doesn't recognize them)
            $user_cpts[] = $cpt;
        }
    }
    $pods_cpts[] = $cpt;
}

echo "📦 Found CPTs:\n";
echo "   🔧 Pods internal: " . implode(', ', $internal_cpts) . "\n";
echo "   🚗 Custom (Pods): " . implode(', ', $user_cpts) . "\n";

// Count posts
$user_posts = 0;
$internal_posts = 0;

foreach ($user_cpts as $cpt) {
    $count = wp_count_posts($cpt);
    $user_posts += ($count->publish ?? 0) + ($count->draft ?? 0) + ($count->private ?? 0);
}

foreach ($internal_cpts as $cpt) {
    $count = wp_count_posts($cpt);
    $internal_posts += ($count->publish ?? 0) + ($count->draft ?? 0) + ($count->private ?? 0);
}

echo "   📄 Custom posts: $user_posts\n";
echo "   ⚙️  Internal posts: $internal_posts\n";

// Custom taxonomies - automatic detection + manual fallback
$all_taxonomies = get_taxonomies(['_builtin' => false], 'names');
$custom_taxonomies = [];

// Try to automatically detect Pods taxonomies
foreach ($all_taxonomies as $taxonomy) {
    $tax_object = get_taxonomy($taxonomy);
    // Check if it's associated with Pods CPTs or if it's a known taxonomy
    if ($tax_object && (
        array_intersect($tax_object->object_type, $user_cpts) || 
        in_array($taxonomy, ['car_body_type', 'car_brand', 'car_fuel_type', 'car_transmission'])
    )) {
        $custom_taxonomies[] = $taxonomy;
    }
}

$total_terms = 0;

echo "\n🏷️ CUSTOM TAXONOMIES:\n";
foreach ($custom_taxonomies as $taxonomy) {
    if (taxonomy_exists($taxonomy)) {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        $count = is_wp_error($terms) ? 0 : count($terms);
        $total_terms += $count;
        echo "   • $taxonomy: $count terms\n";
    }
}

// Pods options
$pods_options_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'pods%'");
$pods_transients_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pods%'");

echo "\n🔧 OPTIONS AND CONFIGURATIONS:\n";
echo "   • Pods options: $pods_options_count\n";
echo "   • Transients: $pods_transients_count\n";

// Custom tables
$pods_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}pods%'", ARRAY_A);
echo "   • Custom tables: " . count($pods_tables) . "\n";

// Metadata
$car_metadata = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE 'car_%'");

// Attachments associated with CPTs (featured images via _thumbnail_id)
$featured_images = $wpdb->get_var("
    SELECT COUNT(DISTINCT pm.meta_value) 
    FROM {$wpdb->postmeta} pm
    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE pm.meta_key = '_thumbnail_id'
    AND p.post_type IN ('" . implode("','", $user_cpts) . "')
    AND pm.meta_value > 0
");

// Direct attachments (with post_parent)
$direct_attachments = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->posts} p 
    WHERE p.post_type = 'attachment' 
    AND p.post_parent IN (
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type IN ('" . implode("','", $user_cpts) . "')
    )
");

$total_attachments = $featured_images + $direct_attachments;

// Potential orphan posts
$potential_orphans = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->posts} 
    WHERE post_type IN ('" . implode("','", array_merge($user_cpts, $internal_cpts)) . "')
");

echo "   • Car metadata: $car_metadata\n";
echo "   • Featured images: $featured_images\n";
echo "   • Direct attachments: $direct_attachments\n";
echo "   • Total attachments: $total_attachments\n";
echo "   • Total posts to verify: $potential_orphans\n";

// GRAND TOTAL
$pods_definitions_count = count(get_posts(['post_type' => '_pods_pod', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']));
$pods_fields_count = count(get_posts(['post_type' => '_pods_field', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']));
$pods_groups_count = count(get_posts(['post_type' => '_pods_group', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']));

$total_items = $user_posts + $internal_posts + $total_terms + $pods_options_count + $pods_transients_count + count($pods_tables) + $car_metadata + $total_attachments + $pods_definitions_count + $pods_fields_count + $pods_groups_count;

echo "\n📊 GENERAL SUMMARY:\n";
echo "   🔢 Total items to remove: $total_items\n";
echo "   📦 Pod definitions: $pods_definitions_count\n";
echo "   🔧 Pod fields: $pods_fields_count\n";
echo "   📁 Pod groups: $pods_groups_count\n";

// Check if we should only run dry run (default behavior)
// Can be overridden by PHP constant PODS_EXECUTE_RESET
$is_dry_run = !defined('PODS_EXECUTE_RESET') || !PODS_EXECUTE_RESET;

if ($is_dry_run) {
    echo "\n💡 DRY RUN COMPLETE - NO CHANGES MADE\n";
    echo "   🔥 To execute reset, use: ./demo-data/pods-reset.sh\n";
    exit;
}

// ===================================================================
// 🚨 RESET EXECUTION
// ===================================================================

echo "\n🚨 EXECUTING COMPLETE RESET...\n";
echo "===============================\n";

$removed_stats = ['posts' => 0, 'terms' => 0, 'internal' => 0, 'options' => 0, 'tables' => 0, 'meta' => 0];

// 1️⃣ Remove custom posts
echo "\n1️⃣ Removing custom posts...\n";
foreach ($user_cpts as $cpt) {
    $posts = get_posts(['post_type' => $cpt, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']);
    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
        $removed_stats['posts']++;
    }
    echo "   ✅ $cpt: " . count($posts) . " posts removed\n";
}

// 2️⃣ Remove Pods definitions (critical for complete reset)
echo "\n2️⃣ Removing Pods definitions...\n";
$pods_definitions = get_posts(['post_type' => '_pods_pod', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']);
foreach ($pods_definitions as $pod_id) {
    wp_delete_post($pod_id, true);
    $removed_stats['internal']++;
}
echo "   ✅ " . count($pods_definitions) . " Pod definitions removed\n";

// 3️⃣ Remove Pods fields
echo "\n3️⃣ Removing Pods fields...\n";
$pods_fields = get_posts(['post_type' => '_pods_field', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']);
foreach ($pods_fields as $field_id) {
    wp_delete_post($field_id, true);
    $removed_stats['internal']++;
}
echo "   ✅ " . count($pods_fields) . " Pod fields removed\n";

// 4️⃣ Remove Pods groups
echo "\n4️⃣ Removing Pods groups...\n";
$pods_groups = get_posts(['post_type' => '_pods_group', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']);
foreach ($pods_groups as $group_id) {
    wp_delete_post($group_id, true);
    $removed_stats['internal']++;
}
echo "   ✅ " . count($pods_groups) . " Pod groups removed\n";

// 5️⃣ Remove custom taxonomies and terms
echo "\n5️⃣ Removing custom taxonomies...\n";
foreach ($custom_taxonomies as $taxonomy) {
    if (taxonomy_exists($taxonomy)) {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'ids']);
        if (!is_wp_error($terms) && $terms) {
            foreach ($terms as $term_id) {
                wp_delete_term($term_id, $taxonomy);
                $removed_stats['terms']++;
            }
            echo "   ✅ $taxonomy: " . count($terms) . " terms removed\n";
        }
    }
}

// 6️⃣ Remove options and configurations
echo "\n6️⃣ Removing options and configurations...\n";
$pods_options = [
    'pods_framework_version_first', 'pods_framework_version', 'pods_framework_db_version',
    'pods_framework_upgraded_1_x', 'pods_callouts', 'widget_pods_widget_single',
    'widget_pods_widget_list', 'widget_pods_widget_field', 'widget_pods_widget_form'
];

foreach ($pods_options as $option) {
    if (delete_option($option)) {
        $removed_stats['options']++;
    }
}

// Remove all Pods-related options (more comprehensive)
$pods_options_removed = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pods%'");
$transients_removed = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pods%'");
$removed_stats['options'] += $pods_options_removed + $transients_removed;

echo "   ✅ {$removed_stats['options']} options and transients removed\n";

// 7️⃣ Remove custom tables
echo "\n7️⃣ Removing custom tables...\n";
foreach ($pods_tables as $table) {
    $table_name = array_values($table)[0];
    if ($wpdb->query("DROP TABLE IF EXISTS `$table_name`")) {
        $removed_stats['tables']++;
    }
}
echo "   ✅ {$removed_stats['tables']} tables removed\n";

// 8️⃣ Remove metadata
echo "\n8️⃣ Removing custom metadata...\n";
$removed_stats['meta'] = $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'car_%'");
echo "   ✅ {$removed_stats['meta']} metadata removed\n";

// 9️⃣ Final cleanup of orphan posts and attachments
echo "\n9️⃣ Removing attachments and checking orphans...\n";

// Get all attachment IDs before removing posts
$all_attachment_ids = [];

// 1. Featured images associated with CPT posts
$featured_image_ids = $wpdb->get_col("
    SELECT DISTINCT pm.meta_value 
    FROM {$wpdb->postmeta} pm
    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE pm.meta_key = '_thumbnail_id'
    AND p.post_type IN ('" . implode("','", $user_cpts) . "')
    AND pm.meta_value > 0
");

$all_attachment_ids = array_merge($all_attachment_ids, $featured_image_ids);

// 2. Direct attachments (with post_parent pointing to CPT posts)
$direct_attachment_ids = $wpdb->get_col("
    SELECT p.ID 
    FROM {$wpdb->posts} p 
    WHERE p.post_type = 'attachment' 
    AND p.post_parent IN (
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type IN ('" . implode("','", $user_cpts) . "')
    )
");

$all_attachment_ids = array_merge($all_attachment_ids, $direct_attachment_ids);

// 3. Gallery images and other meta attachments
$gallery_attachment_ids = $wpdb->get_col("
    SELECT DISTINCT pm.meta_value 
    FROM {$wpdb->postmeta} pm
    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE p.post_type IN ('" . implode("','", $user_cpts) . "')
    AND pm.meta_key LIKE '%image%'
    AND pm.meta_value REGEXP '^[0-9]+$'
    AND pm.meta_value > 0
");

$all_attachment_ids = array_merge($all_attachment_ids, $gallery_attachment_ids);

// Remove duplicates
$all_attachment_ids = array_unique($all_attachment_ids);

// Remove all found attachments
$attachments_removed = 0;
foreach ($all_attachment_ids as $attachment_id) {
    if (wp_delete_post($attachment_id, true)) {
        $attachments_removed++;
        // Also delete the physical files
        $file_path = get_attached_file($attachment_id);
        if ($file_path && file_exists($file_path)) {
            wp_delete_file($file_path);
        }
    }
}

// Check for orphan posts from CPTs that might have been lost
$orphan_posts = $wpdb->get_results("
    SELECT ID, post_type 
    FROM {$wpdb->posts} 
    WHERE post_type IN ('" . implode("','", array_merge($user_cpts, $internal_cpts)) . "')
    AND post_status != 'auto-draft'
");

$orphans_removed = 0;
foreach ($orphan_posts as $orphan) {
    if (wp_delete_post($orphan->ID, true)) {
        $orphans_removed++;
    }
}

// Remove orphan metadata (without associated post)
$orphan_meta_removed = $wpdb->query("
    DELETE pm FROM {$wpdb->postmeta} pm
    LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE p.ID IS NULL
");

echo "   ✅ $attachments_removed attachments removed (with physical files)\n";
echo "   ✅ $orphans_removed orphan posts removed\n";  
echo "   ✅ $orphan_meta_removed orphan metadata removed\n";

$removed_stats['attachments'] = $attachments_removed;
$removed_stats['orphans'] = $orphans_removed;
$removed_stats['orphan_meta'] = $orphan_meta_removed;

// 🔟 Finalization
echo "\n🔟 Finalizing...\n";
flush_rewrite_rules(true);
wp_cache_flush();
echo "   ✅ Rewrite rules updated\n";
echo "   ✅ Cache cleared\n";

// 🎉 FINAL REPORT
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 COMPLETE PODS RESET EXECUTED SUCCESSFULLY!\n";
echo str_repeat("=", 60) . "\n";
echo "📊 ITEMS REMOVED:\n";
echo "   📄 Custom posts: {$removed_stats['posts']}\n";
echo "   🏷️  Taxonomy terms: {$removed_stats['terms']}\n";
echo "   ⚙️  Internal structures: {$removed_stats['internal']}\n";
echo "   🔧 Options and configurations: {$removed_stats['options']}\n";
echo "   🗄️  Custom tables: {$removed_stats['tables']}\n";
echo "   📝 Metadata: {$removed_stats['meta']}\n";
echo "   🖼️  Attachments: {$removed_stats['attachments']}\n";
echo "   👻 Orphan posts: {$removed_stats['orphans']}\n";
echo "   🧹 Orphan metadata: {$removed_stats['orphan_meta']}\n";

$total_removed = array_sum($removed_stats);
echo "\n🔢 TOTAL REMOVED: $total_removed items\n";
echo "\n✅ Pods has been completely reset!\n";
echo "💡 You can now recreate your CPTs and taxonomies from scratch.\n";
echo str_repeat("=", 60) . "\n";
