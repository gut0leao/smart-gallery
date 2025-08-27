<?php
class Smart_Gallery_Filter {
    public function __construct() {
        // Hooks para inicializar o plugin
        add_action('init', [$this, 'init']);
        add_action('elementor/widgets/register', [$this, 'register_widget']);
    }

    public function init() {
        // Inicialização custom post types, taxonomias, Pods, etc
    }

    public function register_widget($widgets_manager) {
        // Registro do widget Elementor
        // $widgets_manager->register(new \Elementor\Widget_Smart_Gallery_Filter());
    }
}

// Inicializa o plugin
new Smart_Gallery_Filter();
