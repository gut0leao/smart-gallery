<?php

add_action('init', function() {
    // Force register taxonomies for car post type
    register_taxonomy_for_object_type('car_brand', 'car');
    register_taxonomy_for_object_type('car_body_type', 'car');
    register_taxonomy_for_object_type('car_fuel_type', 'car');
    register_taxonomy_for_object_type('car_transmission', 'car');
}, 999);
