<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Elementor_Smart_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'smart_gallery_filter';
    }

    public function get_title() {
        return 'Smart Gallery Filter';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Configurações',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'taxonomy',
            [
                'label' => 'Taxonomia (Pods)',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_pods_taxonomies(),
            ]
        );

        $this->add_control(
            'root_term',
            [
                'label' => 'Termo Raiz',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [],
                'condition' => [
                    'taxonomy!' => '',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_pods_taxonomies() {
        $options = ['' => 'Selecione uma taxonomia'];
        
        if (!function_exists('pods_api')) {
            return $options;
        }
        
        $api = pods_api();
        $taxonomies = $api->load_pods(['type' => 'taxonomy']);
        
        foreach ($taxonomies as $taxonomy) {
            $options[$taxonomy['name']] = $taxonomy['label'];
        }
        
        return $options;
    }
    
    private function get_taxonomy_terms($taxonomy) {
        $options = ['' => 'Selecione um termo'];
        
        if (empty($taxonomy)) {
            return $options;
        }
        
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }
        }
        
        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        echo '<div class="smart-gallery-filter">';
        echo '<h3>Smart Gallery Filter</h3>';
        
        if (empty($settings['taxonomy']) || empty($settings['root_term'])) {
            echo '<p>Configure a taxonomia e o termo raiz nas configurações do widget.</p>';
            echo '</div>';
            return;
        }
        
        $posts = $this->get_filtered_posts($settings['taxonomy'], $settings['root_term']);
        
        if (empty($posts)) {
            echo '<p>Nenhum post encontrado.</p>';
        } else {
            echo '<ul>';
            foreach ($posts as $post) {
                echo '<li>' . esc_html($post->post_title) . '</li>';
            }
            echo '</ul>';
        }
        
        echo '</div>';
    }
    
    private function get_filtered_posts($taxonomy, $root_term_id) {
        // Busca o termo raiz e seus descendentes
        $term_ids = [$root_term_id];
        $children = get_term_children($root_term_id, $taxonomy);
        if (!is_wp_error($children)) {
            $term_ids = array_merge($term_ids, $children);
        }
        
        // Busca posts associados a esses termos
        $args = [
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_ids,
                    'include_children' => true,
                ],
            ],
        ];
        
        return get_posts($args);
    }
}