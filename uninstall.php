<?php
/**
 * Uninstall Script
 * 
 * Cleanup when plugin is deleted
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die('Direct access not permitted.');
}

// Delete all menu items
$menu_items = get_posts(array(
    'post_type' => 'wpr_menu_item',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

foreach ($menu_items as $item) {
    wp_delete_post($item->ID, true);
}

// Delete taxonomies
$taxonomies = array('wpr_category', 'wpr_menu_list');
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));
    
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy);
        }
    }
}

// Delete options
$options = array(
    'wpr_currency_symbol',
    'wpr_currency_position',
    'wpr_show_images',
    'wpr_image_position',
    'wpr_show_search',
    'wpr_group_by_category',
    'wpr_columns',
    'wpr_license_key',
    'wpr_license_type',
    'wpr_license_domain',
    'wpr_license_server_url',
    'wpr_license_last_check',
    'wpr_dark_mode_enabled',
    'wpr_dark_mode_scope',
    'wpr_dark_mode_method',
    'wpr_dark_mode_position',
);

foreach ($options as $option) {
    delete_option($option);
}

// Flush rewrite rules
flush_rewrite_rules();