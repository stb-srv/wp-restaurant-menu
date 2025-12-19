<?php
/**
 * Import/Export Class
 * 
 * @package WP_Restaurant_Menu
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class WPR_Import_Export {
    
    /**
     * Export all menu items to JSON
     */
    public static function export_menu() {
        $items = get_posts(array(
            'post_type' => 'wpr_menu_item',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ));
        
        $export_data = array(
            'version' => WP_RESTAURANT_MENU_VERSION,
            'exported_at' => current_time('c'),
            'items' => array(),
        );
        
        foreach ($items as $item) {
            $post_id = $item->ID;
            
            // Get taxonomies
            $categories = wp_get_post_terms($post_id, 'wpr_category', array('fields' => 'slugs'));
            $menu_lists = wp_get_post_terms($post_id, 'wpr_menu_list', array('fields' => 'slugs'));
            
            // Get meta data
            $dish_number = get_post_meta($post_id, '_wpr_dish_number', true);
            $price = get_post_meta($post_id, '_wpr_price', true);
            $vegan = get_post_meta($post_id, '_wpr_vegan', true);
            $vegetarian = get_post_meta($post_id, '_wpr_vegetarian', true);
            $allergens = get_post_meta($post_id, '_wpr_allergens', true);
            
            // Get featured image URL
            $image_url = '';
            if (has_post_thumbnail($post_id)) {
                $image_url = get_the_post_thumbnail_url($post_id, 'full');
            }
            
            $export_data['items'][] = array(
                'title' => $item->post_title,
                'content' => $item->post_content,
                'dish_number' => $dish_number,
                'price' => $price,
                'vegan' => (bool) $vegan,
                'vegetarian' => (bool) $vegetarian,
                'allergens' => is_array($allergens) ? $allergens : array(),
                'categories' => is_array($categories) ? $categories : array(),
                'menu_lists' => is_array($menu_lists) ? $menu_lists : array(),
                'image_url' => $image_url,
                'menu_order' => $item->menu_order,
            );
        }
        
        return $export_data;
    }
    
    /**
     * Download export as JSON file
     */
    public static function download_export() {
        $data = self::export_menu();
        $json = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $filename = 'restaurant-menu-export-' . date('Y-m-d-His') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        
        echo $json;
        exit;
    }
    
    /**
     * Import menu items from JSON
     */
    public static function import_menu($json_data, $overwrite = false) {
        $data = json_decode($json_data, true);
        
        if (!$data || !isset($data['items'])) {
            return array(
                'success' => false,
                'message' => 'Ung\u00fcltiges JSON-Format',
            );
        }
        
        $imported = 0;
        $skipped = 0;
        $errors = array();
        
        foreach ($data['items'] as $item_data) {
            try {
                // Check if item already exists
                $existing = get_page_by_title($item_data['title'], OBJECT, 'wpr_menu_item');
                
                if ($existing && !$overwrite) {
                    $skipped++;
                    continue;
                }
                
                // Create or update post
                $post_data = array(
                    'post_title' => sanitize_text_field($item_data['title']),
                    'post_content' => wp_kses_post($item_data['content']),
                    'post_type' => 'wpr_menu_item',
                    'post_status' => 'publish',
                    'menu_order' => isset($item_data['menu_order']) ? (int) $item_data['menu_order'] : 0,
                );
                
                if ($existing && $overwrite) {
                    $post_data['ID'] = $existing->ID;
                    $post_id = wp_update_post($post_data);
                } else {
                    $post_id = wp_insert_post($post_data);
                }
                
                if (is_wp_error($post_id)) {
                    $errors[] = 'Fehler bei "' . $item_data['title'] . '": ' . $post_id->get_error_message();
                    continue;
                }
                
                // Update meta data
                if (isset($item_data['dish_number'])) {
                    update_post_meta($post_id, '_wpr_dish_number', sanitize_text_field($item_data['dish_number']));
                }
                if (isset($item_data['price'])) {
                    update_post_meta($post_id, '_wpr_price', sanitize_text_field($item_data['price']));
                }
                update_post_meta($post_id, '_wpr_vegan', isset($item_data['vegan']) && $item_data['vegan'] ? 1 : 0);
                update_post_meta($post_id, '_wpr_vegetarian', isset($item_data['vegetarian']) && $item_data['vegetarian'] ? 1 : 0);
                
                if (isset($item_data['allergens']) && is_array($item_data['allergens'])) {
                    update_post_meta($post_id, '_wpr_allergens', array_map('sanitize_text_field', $item_data['allergens']));
                }
                
                // Set taxonomies
                if (isset($item_data['categories']) && is_array($item_data['categories'])) {
                    wp_set_object_terms($post_id, $item_data['categories'], 'wpr_category');
                }
                if (isset($item_data['menu_lists']) && is_array($item_data['menu_lists'])) {
                    wp_set_object_terms($post_id, $item_data['menu_lists'], 'wpr_menu_list');
                }
                
                // Handle image (if URL provided)
                if (!empty($item_data['image_url'])) {
                    self::import_image($post_id, $item_data['image_url']);
                }
                
                $imported++;
                
            } catch (Exception $e) {
                $errors[] = 'Fehler bei "' . ($item_data['title'] ?? 'Unbekannt') . '": ' . $e->getMessage();
            }
        }
        
        $message = sprintf(
            '%d Gerichte importiert, %d \u00fcbersprungen',
            $imported,
            $skipped
        );
        
        if (!empty($errors)) {
            $message .= '\n\nFehler:\n' . implode('\n', $errors);
        }
        
        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        );
    }
    
    /**
     * Import image from URL
     */
    private static function import_image($post_id, $image_url) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        $file_array = array(
            'name' => basename($image_url),
            'tmp_name' => $tmp,
        );
        
        $id = media_handle_sideload($file_array, $post_id);
        
        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            return false;
        }
        
        set_post_thumbnail($post_id, $id);
        return true;
    }
    
    /**
     * Validate JSON structure
     */
    public static function validate_import_data($json_data) {
        $data = json_decode($json_data, true);
        
        if (!$data) {
            return array(
                'valid' => false,
                'message' => 'Ung\u00fcltiges JSON',
            );
        }
        
        if (!isset($data['items']) || !is_array($data['items'])) {
            return array(
                'valid' => false,
                'message' => 'Keine "items" im JSON gefunden',
            );
        }
        
        $count = count($data['items']);
        
        return array(
            'valid' => true,
            'message' => sprintf('%d Gerichte gefunden', $count),
            'count' => $count,
            'version' => $data['version'] ?? 'unknown',
        );
    }
}