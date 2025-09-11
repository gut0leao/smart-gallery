<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Smart Gallery Renderer
 * 
 * Handles all HTML rendering and output generation including:
 * - Gallery item rendering with content
 * - Configuration panels and status displays
 * - Placeholder items and empty states
 * - Elementor template generation
 * 
 * @since 1.1.0
 */
class Smart_Gallery_Renderer {

    /**
     * Pods Integration instance
     * 
     * @var Smart_Gallery_Pods_Integration
     */
    private $pods_integration;

    /**
     * Constructor
     * 
     * @param Smart_Gallery_Pods_Integration $pods_integration
     */
    public function __construct($pods_integration) {
        $this->pods_integration = $pods_integration;
    }

    /**
     * Render complete gallery widget
     * 
     * @param array $settings
     */
    public function render_gallery($settings) {
        // Extract settings
        $selected_cpt = $settings['selected_cpt'] ?? '';
        $posts_per_page = $settings['posts_per_page'] ?? 12;
        $columns = $settings['columns'] ?? 3;
        $gap = $settings['gap'] ?? ['size' => 20, 'unit' => 'px'];
        $enable_search = $settings['enable_search_input'] ?? 'yes';
        $search_position = $settings['search_position'] ?? 'upper_bar';
        
        // Get current page for pagination - support for pretty permalinks
        $current_page = 1;
        
        // Try different methods to get current page
        if (get_query_var('paged')) {
            $current_page = absint(get_query_var('paged'));
        } elseif (get_query_var('page')) {
            $current_page = absint(get_query_var('page'));
        } elseif (isset($_GET['paged'])) {
            $current_page = absint($_GET['paged']);
        } else {
            // Check for pretty permalink pattern /page/2/
            global $wp;
            if (preg_match('/\/page\/(\d+)\/?$/', $_SERVER['REQUEST_URI'], $matches)) {
                $current_page = absint($matches[1]);
            }
        }
        
        // Get search term from URL
        $search_term = isset($_GET['search_term']) ? sanitize_text_field($_GET['search_term']) : '';
        
        // Reset to page 1 when search term changes
        if (!empty($search_term) && !isset($_GET['paged'])) {
            $current_page = 1;
        }
        
        // Ensure page is at least 1
        $current_page = max(1, $current_page);
        
        echo '<div class="smart-gallery-widget" data-page="' . esc_attr($current_page) . '" data-search="' . esc_attr($search_term) . '">';
        
        $this->render_configuration_panel($settings);
        $this->render_pods_status($selected_cpt, $posts_per_page);
        
        // Render search interface based on position
        if ($enable_search === 'yes' && $search_position === 'upper_bar') {
            $this->render_search_interface($settings, $search_term, 'upper_bar');
        }
        
        // Render main content area
        echo '<div class="smart-gallery-main-content">';
        
        // Left bar (future: filters + search if position is left_bar)
        if ($enable_search === 'yes' && $search_position === 'left_bar') {
            echo '<div class="smart-gallery-left-bar">';
            $this->render_search_interface($settings, $search_term, 'left_bar');
            echo '</div>';
        }
        
        // Gallery grid
        echo '<div class="smart-gallery-content">';
        $this->render_gallery_grid($settings, $current_page, $search_term);
        echo '</div>';
        
        echo '</div>'; // End main content
        
        $this->render_status_message($settings);
        
        echo '</div>';
    }

    /**
     * Render configuration panel
     * 
     * @param array $settings
     */
    public function render_configuration_panel($settings) {
        $selected_cpt = $settings['selected_cpt'] ?? '';
        $show_title = $settings['show_title'] ?? 'yes';
        $show_description = $settings['show_description'] ?? 'yes';
        $description_field = $settings['description_field'] ?? 'content';
        $custom_field_name = $settings['custom_description_field'] ?? '';
        $posts_per_page = $settings['posts_per_page'] ?? 12;
        $columns = $settings['columns'] ?? 3;
        $gap = $settings['gap'] ?? ['size' => 20, 'unit' => 'px'];
        $gap_size = is_array($gap) ? $gap['size'] : $gap;
        $gap_unit = is_array($gap) ? $gap['unit'] : 'px';
        $enable_image_hover = $settings['enable_image_hover'] ?? 'yes';
        $enable_content_hover = $settings['enable_content_hover'] ?? 'yes';
        $no_results_message = $settings['no_results_message'] ?? 'No results found...';
        $enable_search = $settings['enable_search_input'] ?? 'yes';
        $search_position = $settings['search_position'] ?? 'upper_bar';
        $search_placeholder = $settings['search_placeholder_text'] ?? 'Search here...';

        echo '<div class="smart-gallery-config" style="padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        echo '<h4 style="margin: 0 0 15px; color: #495057; font-size: 16px;">🔧 Gallery Configuration</h4>';
        
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; font-size: 14px;">';
        
        // Selected CPT
        echo '<div>';
        echo '<strong style="color: #6c757d;">Selected Pod:</strong><br>';
        if (empty($selected_cpt)) {
            echo '<span style="color: #dc3545;">⚠️ No pod selected</span>';
        } else {
            echo '<span style="color: #28a745;">✅ ' . esc_html($selected_cpt) . '</span>';
        }
        echo '</div>';
        
        // Show Title Status
        echo '<div>';
        echo '<strong style="color: #6c757d;">Show Title:</strong><br>';
        $title_status = $show_title === 'yes' ? '✅ Enabled' : '❌ Disabled';
        $title_color = $show_title === 'yes' ? '#28a745' : '#6c757d';
        echo '<span style="color: ' . $title_color . ';">' . $title_status . '</span>';
        echo '</div>';
        
        // Show Description Status
        echo '<div>';
        echo '<strong style="color: #6c757d;">Show Description:</strong><br>';
        $desc_status = $show_description === 'yes' ? '✅ Enabled' : '❌ Disabled';
        $desc_color = $show_description === 'yes' ? '#28a745' : '#6c757d';
        echo '<span style="color: ' . $desc_color . ';">' . $desc_status . '</span>';
        echo '</div>';
        
        // Description Field (only if Show Description enabled)
        if ($show_description === 'yes') {
            echo '<div>';
            echo '<strong style="color: #6c757d;">Description Field:</strong><br>';
            if ($description_field === 'custom_field' && !empty($custom_field_name)) {
                echo '<span style="color: #17a2b8;">🔧 ' . esc_html($custom_field_name) . '</span>';
            } else {
                $field_label = $description_field === 'content' ? 'Post Content' : ucfirst($description_field);
                echo '<span style="color: #495057;">📝 ' . esc_html($field_label) . '</span>';
                
                // Show length info only for Post Content
                if ($description_field === 'content') {
                    $desc_length = $settings['description_length'] ?? 50;
                    echo '<br><span style="color: #6c757d; font-size: 12px;">(' . esc_html($desc_length) . ' chars max)</span>';
                }
            }
            echo '</div>';
        }
        
        // Posts per page
        echo '<div>';
        echo '<strong style="color: #6c757d;">Posts per Page:</strong><br>';
        echo '<span style="color: #495057;">' . esc_html($posts_per_page) . ' posts</span>';
        echo '</div>';
        
        // Columns
        echo '<div>';
        echo '<strong style="color: #6c757d;">Columns:</strong><br>';
        echo '<span style="color: #495057;">' . esc_html($columns) . ' columns</span>';
        echo '</div>';
        
        // Hover Effects
        echo '<div>';
        echo '<strong style="color: #6c757d;">Hover Effects:</strong><br>';
        $hover_status = [];
        if ($enable_image_hover === 'yes') {
            $hover_status[] = '🖼️ Image Zoom';
        }
        if ($enable_content_hover === 'yes') {
            $hover_status[] = '📝 Content Reveal';
        }
        if (empty($hover_status)) {
            echo '<span style="color: #6c757d;">❌ Static gallery</span>';
        } else {
            echo '<span style="color: #28a745;">' . implode(' + ', $hover_status) . '</span>';
        }
        echo '</div>';
        
        // No Results Message
        echo '<div>';
        echo '<strong style="color: #6c757d;">No Results Message:</strong><br>';
        echo '<span style="color: #495057;">"' . esc_html($no_results_message) . '"</span>';
        echo '</div>';
        
        // Search Settings
        echo '<div>';
        echo '<strong style="color: #6c757d;">Search Input:</strong><br>';
        if ($enable_search === 'yes') {
            $position_label = $search_position === 'upper_bar' ? 'Upper Bar' : 'Left Bar';
            echo '<span style="color: #28a745;">✅ Enabled (' . esc_html($position_label) . ')</span>';
            echo '<br><span style="color: #6c757d; font-size: 12px;">Placeholder: "' . esc_html($search_placeholder) . '"</span>';
        } else {
            echo '<span style="color: #6c757d;">❌ Disabled</span>';
        }
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render Pods integration status
     * 
     * @param string $selected_cpt
     * @param int $posts_per_page
     */
    public function render_pods_status($selected_cpt, $posts_per_page) {
        echo '<div class="smart-gallery-pods-status" style="padding: 15px; background: #e8f4f8; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">';
        echo '<h5 style="margin: 0 0 10px; color: #0c5460;">🔌 Pods Integration Status</h5>';
        
        if (!$this->pods_integration->is_pods_available()) {
            echo '<div style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 6px; margin-bottom: 10px;">';
            echo '<strong>❌ Pods Not Available</strong><br>';
            echo 'The Pods plugin is not active or not found.';
            echo '</div>';
        } else {
            echo '<div style="color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 10px;">';
            echo '<strong>✅ Pods Available</strong><br>';
            echo 'Pods plugin is active and ready.';
            echo '</div>';
            
            // Show Pod analysis if one is selected
            if (!empty($selected_cpt)) {
                $pod_posts = $this->pods_integration->get_pod_posts($selected_cpt, $posts_per_page, 1);
                $pod_fields = $this->pods_integration->get_pod_fields($selected_cpt);
                $pod_taxonomies = $this->pods_integration->get_pod_taxonomies($selected_cpt);
                
                if (is_wp_error($pod_posts)) {
                    echo '<div style="color: #856404; background: #fff3cd; padding: 10px; border-radius: 6px; margin-bottom: 5px;">';
                    echo '<strong>⚠️ Posts Status:</strong> ' . esc_html($pod_posts->get_error_message());
                    echo '</div>';
                } else {
                    echo '<div style="color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 5px;">';
                    echo '<strong>📄 Posts Found:</strong> ' . esc_html($pod_posts['total']) . ' total posts';
                    echo '</div>';
                }
                
                echo '<div style="color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 6px; margin-bottom: 5px;">';
                echo '<strong>🔧 Custom Fields:</strong> ' . count($pod_fields) . ' fields detected';
                echo '</div>';
                
                echo '<div style="color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 6px;">';
                echo '<strong>🏷️ Taxonomies:</strong> ' . count($pod_taxonomies) . ' taxonomies detected';
                echo '</div>';
            }
        }
        
        echo '</div>';
    }

    /**
     * Render gallery grid
     * 
     * @param array $settings
     * @param int $current_page
     * @param string $search_term
     */
    public function render_gallery_grid($settings, $current_page = 1, $search_term = '') {
        $selected_cpt = $settings['selected_cpt'] ?? '';
        $posts_per_page = $settings['posts_per_page'] ?? 12;
        $columns = $settings['columns'] ?? 3;
        $gap = $settings['gap'] ?? ['size' => 20, 'unit' => 'px'];
        $gap_size = is_array($gap) ? $gap['size'] : $gap;
        $gap_unit = is_array($gap) ? $gap['unit'] : 'px';

        echo '<div class="smart-gallery-grid" style="display: grid; grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr); gap: ' . esc_attr($gap_size . $gap_unit) . ';">';
        
        if (!empty($selected_cpt) && $this->pods_integration->is_pods_available()) {
            // Display real posts from selected Pod with pagination and search
            $pod_posts = $this->pods_integration->get_pod_posts($selected_cpt, $posts_per_page, $current_page, $search_term);
            
            if (!is_wp_error($pod_posts) && !empty($pod_posts['posts'])) {
                foreach ($pod_posts['posts'] as $post) {
                    $this->render_gallery_item($post, $settings);
                }
                
                // Render pagination after the grid if enabled and there are multiple pages
                echo '</div>'; // Close grid
                
                if ($this->should_show_pagination($settings, $pod_posts)) {
                    $this->render_pagination($settings, $pod_posts, $current_page, $search_term);
                }
                
                return; // Early return to avoid closing grid again
            } else {
                $this->render_no_posts_message($settings, $search_term);
            }
        } else {
            $this->render_placeholder_items($posts_per_page, $settings);
        }
        
        echo '</div>';
    }

    /**
     * Render search interface
     * 
     * @param array $settings
     * @param string $search_term
     * @param string $position
     */
    public function render_search_interface($settings, $search_term = '', $position = 'upper_bar') {
        $placeholder_text = $settings['search_placeholder_text'] ?? esc_html__('Search here...', 'smart-gallery');
        $position_class = 'smart-gallery-search-' . $position;
        
        // Get current URL for form action
        $current_url = remove_query_arg(['search_term', 'paged'], $_SERVER['REQUEST_URI']);
        
        echo '<div class="smart-gallery-search-container ' . esc_attr($position_class) . '">';
        echo '<form method="get" action="' . esc_url($current_url) . '" class="smart-gallery-search-form">';
        
        // Preserve other query parameters
        foreach ($_GET as $key => $value) {
            if (!in_array($key, ['search_term', 'paged'])) {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }
        }
        
        echo '<div class="smart-gallery-search-group">';
        
        // Search Input Container with internal button
        echo '<div class="smart-gallery-search-input-container">';
        echo '<input type="text" name="search_term" value="' . esc_attr($search_term) . '" placeholder="' . esc_attr($placeholder_text) . '" class="smart-gallery-search-input">';
        echo '<button type="submit" class="smart-gallery-search-button-internal" title="' . esc_attr__('Search', 'smart-gallery') . '">';
        echo '<span class="search-button-icon">🔍</span>';
        echo '</button>';
        echo '</div>';
        
        // Clear button - only show when there's a search term
        if (!empty($search_term)) {
            echo '<button type="button" class="smart-gallery-clear-button" onclick="window.location.href=\'' . esc_url($current_url) . '\'" title="' . esc_attr__('Clear search', 'smart-gallery') . '">';
            echo '<span class="clear-button-icon">🗑️</span>';
            echo '<span class="clear-button-text">' . esc_html__('Clear', 'smart-gallery') . '</span>';
            echo '</button>';
        }
        
        echo '</div>'; // End search-group
        echo '</form>';
        echo '</div>';
    }

    /**
     * Render individual gallery item
     * 
     * @param WP_Post $post
     * @param array $settings
     */
    public function render_gallery_item($post, $settings) {
        $post_id = $post->ID;
        $post_title = get_the_title($post_id);
        $post_permalink = get_permalink($post_id);
        $featured_image_id = get_post_thumbnail_id($post_id);
        
        // Get visibility settings
        $show_title = $settings['show_title'] ?? 'yes';
        $show_description = $settings['show_description'] ?? 'yes';
        
        // Get hover settings
        $enable_image_hover = $settings['enable_image_hover'] ?? 'yes';
        $enable_content_hover = $settings['enable_content_hover'] ?? 'yes';
        
        // Build CSS classes for hover effects
        $item_classes = 'smart-gallery-item';
        if ($enable_image_hover === 'yes') {
            $item_classes .= ' hover-image-enabled';
        }
        if ($enable_content_hover === 'yes') {
            $item_classes .= ' hover-content-enabled';
        }
        
        // Get description using Pods integration (only if enabled)
        $description = $show_description === 'yes' ? $this->pods_integration->get_post_description($post, $settings) : '';
        
        // Get featured image or fallback
        if ($featured_image_id) {
            $featured_image = wp_get_attachment_image_src($featured_image_id, 'medium');
            $image_url = $featured_image ? $featured_image[0] : '';
            $image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
        } else {
            $image_url = '';
            $image_alt = '';
        }
        
        echo '<div class="' . esc_attr($item_classes) . '" style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: #f8f9fa;">';
        
        // Link wrapper
        echo '<a href="' . esc_url($post_permalink) . '" target="_blank" style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">';
        
        if ($image_url) {
            // Featured image
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt ?: $post_title) . '" style="width: 100%; height: 100%; object-fit: cover;">';
            
            // Overlay with title and description (if enabled)
            if ($show_title === 'yes' || ($show_description === 'yes' && !empty($description))) {
                echo '<div class="smart-gallery-overlay smart-gallery-content" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 15px; color: white;">';
                
                if ($show_title === 'yes') {
                    echo '<div style="font-size: 14px; font-weight: 500; line-height: 1.3; margin-bottom: 5px;">' . esc_html($post_title) . '</div>';
                }
                
                if ($show_description === 'yes' && !empty($description)) {
                    echo '<div style="font-size: 12px; opacity: 0.9; line-height: 1.3;">' . esc_html($description) . '</div>';
                }
                
                echo '</div>';
            }
        } else {
            // No featured image - show placeholder with title and description (if enabled)
            echo '<div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #e9ecef; color: #6c757d; text-align: center; padding: 20px;">';
            echo '<div style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;">🖼️</div>';
            
            if ($show_title === 'yes') {
                echo '<div style="font-size: 12px; font-weight: 500; line-height: 1.3; margin-bottom: 5px;">' . esc_html($post_title) . '</div>';
            }
            
            if ($show_description === 'yes' && !empty($description)) {
                echo '<div style="font-size: 10px; opacity: 0.7; line-height: 1.3;">' . esc_html($description) . '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</a>';
        echo '</div>';
    }

    /**
     * Render placeholder items
     * 
     * @param int $posts_per_page
     * @param array $settings
     */
    public function render_placeholder_items($posts_per_page, $settings) {
        $show_title = $settings['show_title'] ?? 'yes';
        $show_description = $settings['show_description'] ?? 'yes';
        $preview_count = min($posts_per_page, 6); // Show max 6 for preview

        for ($i = 1; $i <= $preview_count; $i++) {
            echo '<div class="smart-gallery-item smart-gallery-placeholder" style="aspect-ratio: 1; background: #e9ecef; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; border: 2px dashed #dee2e6; text-align: center; padding: 10px;">';
            echo '<div style="font-size: 24px; margin-bottom: 8px; opacity: 0.5;">🖼️</div>';
            
            if ($show_title === 'yes') {
                echo '<div style="font-weight: 500; margin-bottom: 5px;">Post ' . $i . '</div>';
            }
            
            if ($show_description === 'yes') {
                echo '<div style="font-size: 10px; opacity: 0.7;">Sample description...</div>';
            }
            
            echo '</div>';
        }
    }

    /**
     * Render no posts message
     * 
     * @param array $settings
     * @param string $search_term
     */
    public function render_no_posts_message($settings, $search_term = '') {
        $no_results_message = $settings['no_results_message'] ?? esc_html__('No results found...', 'smart-gallery');
        
        // Customize message for search context
        if (!empty($search_term)) {
            $search_message = sprintf(esc_html__('No results found for "%s"', 'smart-gallery'), esc_html($search_term));
        } else {
            $search_message = $no_results_message;
        }
        
        echo '<div class="smart-gallery-no-posts" style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">';
        echo '<div style="color: #6c757d; font-size: 16px;">';
        echo esc_html($search_message);
        
        // Add search tip when searching
        if (!empty($search_term)) {
            echo '<div style="margin-top: 10px; font-size: 14px; color: #868e96;">';
            echo esc_html__('Try a different search term or clear the search to see all posts.', 'smart-gallery');
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render status message
     * 
     * @param array $settings
     */
    public function render_status_message($settings) {
        $selected_cpt = $settings['selected_cpt'] ?? '';
        $posts_per_page = $settings['posts_per_page'] ?? 12;

        echo '<div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; color: #0c5460; font-size: 14px;">';
        echo '<strong>📋 Status:</strong> F3.1 - Text Search functionality implemented successfully!<br>';
        
        if (!empty($selected_cpt) && $this->pods_integration->is_pods_available()) {
            $pod_posts = $this->pods_integration->get_pod_posts($selected_cpt, $posts_per_page, 1, '');
            if (!is_wp_error($pod_posts) && !empty($pod_posts['posts'])) {
                echo '<strong>✅ Showing:</strong> Real content from ' . esc_html($selected_cpt) . ' posts with search capability<br>';
            }
        } else {
            echo '<strong>⚠️ Preview mode:</strong> Select a pod to see real content with search functionality<br>';
        }
        
        echo '<strong>🚀 Next:</strong> F3.2 - Custom Fields Filtering';
        echo '</div>';
    }

    /**
     * Render Elementor content template
     * 
     * @return string
     */
    public function render_content_template() {
        return '
        <div class="smart-gallery-widget">
            <div class="smart-gallery-config" style="padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
                <h4 style="margin: 0 0 15px; color: #495057; font-size: 16px;">🔧 Gallery Configuration</h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; font-size: 14px;">
                    
                    <div>
                        <strong style="color: #6c757d;">Selected Pod:</strong><br>
                        <# if (settings.selected_cpt) { #>
                            <span style="color: #28a745;">✅ {{{ settings.selected_cpt }}}</span>
                        <# } else { #>
                            <span style="color: #dc3545;">⚠️ No pod selected</span>
                        <# } #>
                    </div>
                    
                    <div>
                        <strong style="color: #6c757d;">Show Title:</strong><br>
                        <# var titleStatus = settings.show_title === "yes" ? "✅ Enabled" : "❌ Disabled"; #>
                        <# var titleColor = settings.show_title === "yes" ? "#28a745" : "#6c757d"; #>
                        <span style="color: {{{ titleColor }}};">{{{ titleStatus }}}</span>
                    </div>
                    
                    <div>
                        <strong style="color: #6c757d;">Show Description:</strong><br>
                        <# var descStatus = settings.show_description === "yes" ? "✅ Enabled" : "❌ Disabled"; #>
                        <# var descColor = settings.show_description === "yes" ? "#28a745" : "#6c757d"; #>
                        <span style="color: {{{ descColor }}};">{{{ descStatus }}}</span>
                    </div>
                    
                    <# if (settings.show_description === "yes") { #>
                    <div>
                        <strong style="color: #6c757d;">Description Field:</strong><br>
                        <# if (settings.description_field === "custom_field" && settings.custom_description_field) { #>
                            <span style="color: #17a2b8;">🔧 {{{ settings.custom_description_field }}}</span>
                        <# } else { #>
                            <# var fieldLabel = settings.description_field === "content" ? "Post Content" : settings.description_field; #>
                            <span style="color: #495057;">📝 {{{ fieldLabel }}}</span>
                        <# } #>
                    </div>
                    <# } #>
                    
                    <div>
                        <strong style="color: #6c757d;">Posts per Page:</strong><br>
                        <span style="color: #495057;">{{{ settings.posts_per_page }}} posts</span>
                    </div>
                    
                    <div>
                        <strong style="color: #6c757d;">Columns:</strong><br>
                        <span style="color: #495057;">{{{ settings.columns }}} columns</span>
                    </div>
                    
                </div>
            </div>
            
            <div class="smart-gallery-grid" style="display: grid; grid-template-columns: repeat({{{ settings.columns }}}, 1fr); gap: {{{ settings.gap.size }}}{{{ settings.gap.unit }}};">
                <# for (var i = 1; i <= Math.min(settings.posts_per_page, 6); i++) { #>
                    <div class="smart-gallery-item" style="aspect-ratio: 1; background: #e9ecef; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; border: 2px dashed #dee2e6; text-align: center; padding: 10px;">
                        <div style="font-size: 24px; margin-bottom: 8px; opacity: 0.5;">🖼️</div>
                        <# if (settings.show_title === "yes") { #>
                            <div style="font-weight: 500; margin-bottom: 5px;">Post {{{ i }}}</div>
                        <# } #>
                        <# if (settings.show_description === "yes") { #>
                            <div style="font-size: 10px; opacity: 0.7;">Sample description...</div>
                        <# } #>
                    </div>
                <# } #>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; color: #0c5460; font-size: 14px;">
                <strong>📋 Status:</strong> Phase 2 Complete - F2.1 Pagination System implemented<br>
                <strong>🚀 Next:</strong> Phase 3 - F3.1 Text Search
            </div>
        </div>';
    }

    /**
     * Check if pagination should be displayed
     * 
     * @param array $settings
     * @param array $pod_posts
     * @return bool
     */
    private function should_show_pagination($settings, $pod_posts) {
        $enable_pagination = $settings['enable_pagination'] ?? 'yes';
        $total_pages = $pod_posts['pages'] ?? 0;
        
        return ($enable_pagination === 'yes') && ($total_pages > 1);
    }

    /**
     * Render pagination controls
     * 
     * @param array $settings
     * @param array $pod_posts
     * @param int $current_page
     * @param string $search_term
     */
    private function render_pagination($settings, $pod_posts, $current_page, $search_term = '') {
        $total_pages = $pod_posts['pages'] ?? 0;
        $show_prev_next = $settings['show_prev_next'] ?? 'yes';
        $show_page_numbers = $settings['show_page_numbers'] ?? 'yes';
        $max_page_numbers = intval($settings['max_page_numbers'] ?? 5);
        
        echo '<div class="smart-gallery-pagination">';
        
        // Previous Button
        if ($show_prev_next === 'yes' && $current_page > 1) {
            $prev_url = $this->get_pagination_url($current_page - 1, $search_term);
            echo '<a href="' . esc_url($prev_url) . '" class="pagination-button pagination-prev">';
            echo '<span style="margin-right: 5px;">←</span>' . esc_html__('Previous', 'smart-gallery');
            echo '</a>';
        }
        
        // Page Numbers
        if ($show_page_numbers === 'yes') {
            $this->render_page_numbers($current_page, $total_pages, $max_page_numbers, $search_term);
        }
        
        // Next Button
        if ($show_prev_next === 'yes' && $current_page < $total_pages) {
            $next_url = $this->get_pagination_url($current_page + 1, $search_term);
            echo '<a href="' . esc_url($next_url) . '" class="pagination-button pagination-next">';
            echo esc_html__('Next', 'smart-gallery') . '<span style="margin-left: 5px;">→</span>';
            echo '</a>';
        }
        
        echo '</div>';
    }

    /**
     * Render numbered page buttons
     * 
     * @param int $current_page
     * @param int $total_pages
     * @param int $max_page_numbers
     * @param string $search_term
     */
    private function render_page_numbers($current_page, $total_pages, $max_page_numbers, $search_term = '') {
        // Calculate range of page numbers to show
        $half_range = floor($max_page_numbers / 2);
        $start_page = max(1, $current_page - $half_range);
        $end_page = min($total_pages, $current_page + $half_range);
        
        // Adjust range if we're near the beginning or end
        if ($end_page - $start_page + 1 < $max_page_numbers) {
            if ($start_page === 1) {
                $end_page = min($total_pages, $start_page + $max_page_numbers - 1);
            } else {
                $start_page = max(1, $end_page - $max_page_numbers + 1);
            }
        }
        
        // Show first page and ellipsis if needed
        if ($start_page > 1) {
            $this->render_page_number_button(1, $current_page, $search_term);
            if ($start_page > 2) {
                echo '<span class="pagination-ellipsis" style="padding: 0 8px; color: #6c757d;">...</span>';
            }
        }
        
        // Show page range
        for ($i = $start_page; $i <= $end_page; $i++) {
            $this->render_page_number_button($i, $current_page, $search_term);
        }
        
        // Show last page and ellipsis if needed
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<span class="pagination-ellipsis" style="padding: 0 8px; color: #6c757d;">...</span>';
            }
            $this->render_page_number_button($total_pages, $current_page, $search_term);
        }
    }

    /**
     * Render individual page number button
     * 
     * @param int $page_number
     * @param int $current_page
     * @param string $search_term
     */
    private function render_page_number_button($page_number, $current_page, $search_term = '') {
        $is_current = ($page_number === $current_page);
        
        if ($is_current) {
            echo '<span class="pagination-button pagination-current">';
            echo esc_html($page_number);
            echo '</span>';
        } else {
            $page_url = $this->get_pagination_url($page_number, $search_term);
            echo '<a href="' . esc_url($page_url) . '" class="pagination-button pagination-page">';
            echo esc_html($page_number);
            echo '</a>';
        }
    }

    /**
     * Generate pagination URL for specific page
     * 
     * @param int $page_number
     * @param string $search_term
     * @return string
     */
    private function get_pagination_url($page_number, $search_term = '') {
        global $wp_rewrite;
        
        // Get current URL
        $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Parse URL to separate base URL from query string
        $parsed_url = parse_url($current_url);
        $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
        $query = $parsed_url['query'] ?? '';
        
        // Parse existing query parameters
        parse_str($query, $query_params);
        
        // Remove existing pagination parameters
        unset($query_params['paged'], $query_params['page']);
        
        // Add search term if provided
        if (!empty($search_term)) {
            $query_params['search_term'] = $search_term;
        } else {
            unset($query_params['search_term']);
        }
        
        if ($wp_rewrite->using_permalinks()) {
            // Handle pretty permalinks
            
            // Remove existing /page/X/ pattern from URL
            $base_url = preg_replace('/\/page\/\d+\/?$/', '/', $base_url);
            
            // Ensure URL ends with slash
            if (substr($base_url, -1) !== '/') {
                $base_url .= '/';
            }
            
            $new_query = !empty($query_params) ? '?' . http_build_query($query_params) : '';
            
            if ($page_number <= 1) {
                // For page 1, return clean base URL
                return $base_url . $new_query;
            } else {
                // For other pages, add /page/X/ pattern
                return rtrim($base_url, '/') . '/page/' . absint($page_number) . '/' . $new_query;
            }
        } else {
            // Handle non-pretty permalinks (query parameters)
            
            if ($page_number > 1) {
                $query_params['paged'] = absint($page_number);
            }
            
            $new_query = http_build_query($query_params);
            return $base_url . ($new_query ? '?' . $new_query : '');
        }
    }
}
