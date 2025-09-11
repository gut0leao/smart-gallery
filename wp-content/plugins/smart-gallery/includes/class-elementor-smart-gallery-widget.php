<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Smart_Gallery_Widget extends \Elementor\Widget_Base {

    /**
     * Pods Integration instance
     * 
     * @var Smart_Gallery_Pods_Integration
     */
    private $pods_integration;

    /**
     * Constructor
     */
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        
        // Initialize Pods integration
        $this->pods_integration = new Smart_Gallery_Pods_Integration();
    }

    public function get_name() {
        return 'smart_gallery';
    }

    public function get_title() {
        return esc_html__('Smart Gallery', 'smart-gallery');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_keywords() {
        return ['gallery', 'image', 'photo', 'filter', 'search'];
    }

    /**
     * Render individual gallery item
     * 
     * @param WP_Post $post
     */
    private function render_gallery_item($post) {
        $post_id = $post->ID;
        $post_title = get_the_title($post_id);
        $post_permalink = get_permalink($post_id);
        $featured_image_id = get_post_thumbnail_id($post_id);
        
        // Get featured image or fallback
        if ($featured_image_id) {
            $featured_image = wp_get_attachment_image_src($featured_image_id, 'medium');
            $image_url = $featured_image ? $featured_image[0] : '';
            $image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
        } else {
            $image_url = '';
            $image_alt = '';
        }
        
        echo '<div class="smart-gallery-item" style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: #f8f9fa;">';
        
        // Link wrapper
        echo '<a href="' . esc_url($post_permalink) . '" target="_blank" style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">';
        
        if ($image_url) {
            // Featured image
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt ?: $post_title) . '" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">';
            
            // Overlay with title
            echo '<div class="smart-gallery-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 15px; color: white; transform: translateY(100%); transition: transform 0.3s ease;">';
            echo '<div style="font-size: 14px; font-weight: 500; line-height: 1.3;">' . esc_html($post_title) . '</div>';
            echo '</div>';
        } else {
            // No featured image - show placeholder with title
            echo '<div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #e9ecef; color: #6c757d; text-align: center; padding: 20px;">';
            echo '<div style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;">🖼️</div>';
            echo '<div style="font-size: 12px; font-weight: 500; line-height: 1.3;">' . esc_html($post_title) . '</div>';
            echo '</div>';
        }
        
        echo '</a>';
        echo '</div>';
    }

    /**
     * Get available Pods CPTs
     * 
     * @return array
     */
    protected function register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Gallery Content', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // CPT Selection Dropdown
        $this->add_control(
            'selected_cpt',
            [
                'label' => esc_html__('Select a Pod', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->pods_integration->get_available_cpts(),
                'description' => esc_html__('Choose which Pod to display in the gallery', 'smart-gallery'),
            ]
        );

        $this->end_controls_section();

        // Layout and Presentation Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__('Layout and Presentation Settings', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Posts per Page
        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__('Posts per Page', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'description' => esc_html__('Number of posts to display per page', 'smart-gallery'),
            ]
        );

        // Columns Control
        $this->add_responsive_control(
            'columns',
            [
                'label' => esc_html__('Columns', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .smart-gallery-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr) !important;',
                ],
            ]
        );

        // Gap Control
        $this->add_responsive_control(
            'gap',
            [
                'label' => esc_html__('Gap', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .smart-gallery-grid' => 'gap: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .smart-gallery-placeholder' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Extract settings
        $selected_cpt = $settings['selected_cpt'] ?? '';
        $posts_per_page = $settings['posts_per_page'] ?? 12;
        $columns = $settings['columns'] ?? 3;
        $gap = $settings['gap'] ?? ['size' => 20, 'unit' => 'px'];
        
        echo '<div class="smart-gallery-widget">';
        
        // Display current configuration
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
        
        // Gap
        echo '<div>';
        echo '<strong style="color: #6c757d;">Gap:</strong><br>';
        $gap_size = is_array($gap) ? $gap['size'] : $gap;
        $gap_unit = is_array($gap) ? $gap['unit'] : 'px';
        echo '<span style="color: #495057;">' . esc_html($gap_size . $gap_unit) . '</span>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        // Pods Integration Status
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
        
        // Gallery grid - Real posts or placeholder
        echo '<div class="smart-gallery-grid" style="display: grid; grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr); gap: ' . esc_attr($gap_size . $gap_unit) . ';">';
        
        if (!empty($selected_cpt) && $this->pods_integration->is_pods_available()) {
            // Display real posts from selected Pod
            $pod_posts = $this->pods_integration->get_pod_posts($selected_cpt, $posts_per_page, 1);
            
            if (!is_wp_error($pod_posts) && !empty($pod_posts['posts'])) {
                foreach ($pod_posts['posts'] as $post) {
                    $this->render_gallery_item($post);
                }
            } else {
                // No posts found - show message
                echo '<div class="smart-gallery-no-posts" style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">';
                echo '<div style="color: #6c757d; font-size: 16px;">';
                echo '<strong>📭 No posts found</strong><br>';
                echo '<span style="font-size: 14px;">No published posts in the selected pod.</span>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            // Show placeholder boxes when no pod selected
            $preview_count = min($posts_per_page, 6); // Show max 6 for preview
            for ($i = 1; $i <= $preview_count; $i++) {
                echo '<div class="smart-gallery-item smart-gallery-placeholder" style="aspect-ratio: 1; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; border: 2px dashed #dee2e6;">';
                echo 'Item ' . $i;
                echo '</div>';
            }
        }
        
        echo '</div>';
        
        // Status message
        echo '<div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; color: #0c5460; font-size: 14px;">';
        echo '<strong>📋 Status:</strong> F1.1 - Basic Gallery Display implemented successfully!<br>';
        
        if (!empty($selected_cpt) && $this->pods_integration->is_pods_available()) {
            $pod_posts = $this->pods_integration->get_pod_posts($selected_cpt, $posts_per_page, 1);
            if (!is_wp_error($pod_posts) && !empty($pod_posts['posts'])) {
                echo '<strong>✅ Showing:</strong> Real featured images from ' . esc_html($selected_cpt) . ' posts<br>';
            }
        } else {
            echo '<strong>⚠️ Preview mode:</strong> Select a pod to see real featured images<br>';
        }
        
        echo '<strong>🚀 Next:</strong> F2.1 - Hover Effects & Descriptions';
        echo '</div>';
        
        echo '</div>';
    }

    protected function content_template() {
        ?>
        <div class="smart-gallery-widget">
            <div class="smart-gallery-config" style="padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
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
                        <strong style="color: #6c757d;">Posts per Page:</strong><br>
                        <span style="color: #495057;">{{{ settings.posts_per_page }}} posts</span>
                    </div>
                    
                    <div>
                        <strong style="color: #6c757d;">Columns:</strong><br>
                        <span style="color: #495057;">{{{ settings.columns }}} columns</span>
                    </div>
                    
                    <div>
                        <strong style="color: #6c757d;">Gap:</strong><br>
                        <span style="color: #495057;">{{{ settings.gap.size }}}{{{ settings.gap.unit }}}</span>
                    </div>
                    
                </div>
            </div>
            
            <div class="smart-gallery-grid" style="display: grid; grid-template-columns: repeat({{{ settings.columns }}}, 1fr); gap: {{{ settings.gap.size }}}{{{ settings.gap.unit }}};">
                <# for (var i = 1; i <= Math.min(settings.posts_per_page, 6); i++) { #>
                    <div class="smart-gallery-item" style="aspect-ratio: 1; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; border: 2px dashed #dee2e6;">
                        Item {{{ i }}}
                    </div>
                <# } #>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; color: #0c5460; font-size: 14px;">
                <strong>📋 Status:</strong> Basic Elementor Controls implemented successfully!<br>
                <strong>🚀 Next:</strong> F1.2 - Pods Framework Integration
            </div>
        </div>
        <?php
    }
}
