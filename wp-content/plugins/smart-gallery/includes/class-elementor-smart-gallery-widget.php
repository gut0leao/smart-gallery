<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Smart_Gallery_Widget extends \Elementor\Widget_Base {

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
     * Get available Pods CPTs
     * 
     * @return array
     */
    private function get_available_cpts() {
        $options = [];

        // Check if Pods is active
        if (!function_exists('pods')) {
            $options['no_pods'] = esc_html__('Pods plugin not found', 'smart-gallery');
            return $options;
        }

        // Get Pods CPTs
        try {
            $pods_api = pods_api();
            $all_pods = $pods_api->load_pods(['type' => 'post_type']);
            
            if (!empty($all_pods)) {
                // Add default option first
                $options[''] = esc_html__('Select a Pod', 'smart-gallery');
                
                foreach ($all_pods as $pod) {
                    if (isset($pod['name']) && isset($pod['label'])) {
                        $options[$pod['name']] = $pod['label'];
                    }
                }
            } else {
                $options['no_cpts'] = esc_html__('Need at least one pod', 'smart-gallery');
            }
        } catch (Exception $e) {
            $options['error'] = esc_html__('Need at least one pod', 'smart-gallery');
        }

        return $options;
    }

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
                'options' => $this->get_available_cpts(),
                'description' => esc_html__('Choose which Pod to display in the gallery', 'smart-gallery'),
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

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__('Layout Settings', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
        
        // Gallery grid placeholder
        echo '<div class="smart-gallery-grid" style="display: grid; grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr); gap: ' . esc_attr($gap_size . $gap_unit) . ';">';
        
        // Show preview boxes based on posts_per_page setting
        $preview_count = min($posts_per_page, 6); // Show max 6 for preview
        for ($i = 1; $i <= $preview_count; $i++) {
            echo '<div class="smart-gallery-item" style="aspect-ratio: 1; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 12px; border: 2px dashed #dee2e6;">';
            echo 'Item ' . $i;
            echo '</div>';
        }
        
        echo '</div>';
        
        // Status message
        echo '<div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; color: #0c5460; font-size: 14px;">';
        echo '<strong>📋 Status:</strong> Basic Elementor Controls implemented successfully!<br>';
        echo '<strong>🚀 Next:</strong> F1.2 - Pods Framework Integration';
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
