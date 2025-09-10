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

    protected function register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'smart-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'placeholder_text',
            [
                'label' => esc_html__('Placeholder Text', 'smart-gallery'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Smart Gallery Widget - Ready for Development', 'smart-gallery'),
                'placeholder' => esc_html__('Enter placeholder text', 'smart-gallery'),
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
        
        echo '<div class="smart-gallery-widget">';
        echo '<div class="smart-gallery-placeholder" style="padding: 40px; text-align: center; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px;">';
        echo '<h3 style="margin: 0 0 10px; color: #6c757d;">🖼️ Smart Gallery</h3>';
        echo '<p style="margin: 0; color: #6c757d;">' . esc_html($settings['placeholder_text']) . '</p>';
        echo '<p style="margin: 10px 0 0; font-size: 12px; color: #adb5bd;">Ready for clean development!</p>';
        echo '</div>';
        echo '</div>';
    }

    protected function content_template() {
        ?>
        <div class="smart-gallery-widget">
            <div class="smart-gallery-placeholder" style="padding: 40px; text-align: center; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px;">
                <h3 style="margin: 0 0 10px; color: #6c757d;">🖼️ Smart Gallery</h3>
                <p style="margin: 0; color: #6c757d;">{{{ settings.placeholder_text }}}</p>
                <p style="margin: 10px 0 0; font-size: 12px; color: #adb5bd;">Ready for clean development!</p>
            </div>
        </div>
        <?php
    }
}
