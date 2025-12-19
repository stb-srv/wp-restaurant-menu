<?php
/**
 * License Management Class
 * 
 * @version 2.3.1
 * @package WP_Restaurant_Menu
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class WPR_License {
    
    private static $license_data = null;
    private static $pricing_data = null;
    
    /**
     * Get License Server URL
     */
    public static function get_server_url() {
        return get_option('wpr_license_server_url', 'https://license.stb-srv.de/api.php');
    }
    
    /**
     * Get Current License Type
     */
    public static function get_license_type() {
        return get_option('wpr_license_type', 'free');
    }
    
    /**
     * Get License Key
     */
    public static function get_license_key() {
        return get_option('wpr_license_key', '');
    }
    
    /**
     * Get Current Domain
     */
    public static function get_current_domain() {
        return parse_url(home_url(), PHP_URL_HOST);
    }
    
    /**
     * Validate License Key Format
     */
    public static function validate_key_format($key) {
        // Format: WPR-XXXXX-XXXXX-XXXXX or WPR-XXXXX-XXXXX-XXXXX-XXXXX
        $pattern = '/^WPR-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}(-[A-Z0-9]{5})?$/';
        return preg_match($pattern, strtoupper($key)) === 1;
    }
    
    /**
     * Check License with Server
     */
    public static function check_license($key = null) {
        if ($key === null) {
            $key = self::get_license_key();
        }
        
        if (empty($key)) {
            return array(
                'valid' => false,
                'type' => 'free',
                'max_items' => 20,
                'features' => array(),
                'error' => 'No license key provided',
            );
        }
        
        if (!self::validate_key_format($key)) {
            return array(
                'valid' => false,
                'type' => 'free',
                'max_items' => 20,
                'features' => array(),
                'error' => 'Invalid license key format',
            );
        }
        
        $server_url = self::get_server_url();
        $domain = self::get_current_domain();
        
        $url = add_query_arg(array(
            'action' => 'check_license',
            'key' => strtoupper($key),
            'domain' => $domain,
        ), $server_url);
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'WP-Restaurant-Menu/' . WP_RESTAURANT_MENU_VERSION,
            ),
        ));
        
        if (is_wp_error($response)) {
            return array(
                'valid' => false,
                'type' => 'free',
                'max_items' => 20,
                'features' => array(),
                'error' => $response->get_error_message(),
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['valid'])) {
            return array(
                'valid' => false,
                'type' => 'free',
                'max_items' => 20,
                'features' => array(),
                'error' => 'Invalid server response',
            );
        }
        
        self::$license_data = $data;
        update_option('wpr_license_last_check', time());
        
        return $data;
    }
    
    /**
     * Activate License
     */
    public static function activate_license($key) {
        $result = self::check_license($key);
        
        if ($result['valid']) {
            update_option('wpr_license_key', strtoupper($key));
            update_option('wpr_license_type', $result['type']);
            update_option('wpr_license_domain', self::get_current_domain());
            
            return array(
                'success' => true,
                'message' => 'Lizenz erfolgreich aktiviert!',
                'data' => $result,
            );
        }
        
        return array(
            'success' => false,
            'message' => $result['error'] ?? 'Lizenz ung\u00fcltig',
            'data' => $result,
        );
    }
    
    /**
     * Deactivate License
     */
    public static function deactivate_license() {
        delete_option('wpr_license_key');
        update_option('wpr_license_type', 'free');
        delete_option('wpr_license_domain');
        self::$license_data = null;
        
        return array(
            'success' => true,
            'message' => 'Lizenz deaktiviert',
        );
    }
    
    /**
     * Get License Data (cached)
     */
    public static function get_license_data() {
        if (self::$license_data === null) {
            $key = self::get_license_key();
            if (!empty($key)) {
                $last_check = get_option('wpr_license_last_check', 0);
                // Cache for 24 hours
                if (time() - $last_check > 86400) {
                    self::$license_data = self::check_license();
                } else {
                    self::$license_data = array(
                        'valid' => true,
                        'type' => self::get_license_type(),
                        'max_items' => self::get_max_items(),
                        'features' => self::get_features(),
                    );
                }
            } else {
                self::$license_data = array(
                    'valid' => false,
                    'type' => 'free',
                    'max_items' => 20,
                    'features' => array(),
                );
            }
        }
        
        return self::$license_data;
    }
    
    /**
     * Get Max Items
     */
    public static function get_max_items() {
        $type = self::get_license_type();
        $limits = array(
            'free' => 20,
            'free_plus' => 60,
            'pro' => 200,
            'pro_plus' => 200,
            'ultimate' => 900,
        );
        
        return $limits[$type] ?? 20;
    }
    
    /**
     * Get Current Item Count
     */
    public static function get_current_count() {
        $count = wp_count_posts('wpr_menu_item');
        return (int) $count->publish;
    }
    
    /**
     * Check if can add more items
     */
    public static function can_add_item() {
        if (self::has_unlimited_items()) {
            return true;
        }
        
        return self::get_current_count() < self::get_max_items();
    }
    
    /**
     * Get Features for License Type
     */
    public static function get_features() {
        $type = self::get_license_type();
        $features_map = array(
            'free' => array(),
            'free_plus' => array(),
            'pro' => array(),
            'pro_plus' => array('dark_mode', 'cart'),
            'ultimate' => array('dark_mode', 'cart', 'unlimited'),
        );
        
        return $features_map[$type] ?? array();
    }
    
    /**
     * Check if has Dark Mode
     */
    public static function has_dark_mode() {
        $features = self::get_features();
        return in_array('dark_mode', $features, true);
    }
    
    /**
     * Check if has Cart
     */
    public static function has_cart() {
        $features = self::get_features();
        return in_array('cart', $features, true);
    }
    
    /**
     * Check if has Unlimited Items
     */
    public static function has_unlimited_items() {
        $features = self::get_features();
        return in_array('unlimited', $features, true);
    }
    
    /**
     * Get Pricing from Server
     */
    public static function get_pricing() {
        if (self::$pricing_data !== null) {
            return self::$pricing_data;
        }
        
        $transient_key = 'wpr_pricing_data';
        $cached = get_transient($transient_key);
        
        if ($cached !== false) {
            self::$pricing_data = $cached;
            return $cached;
        }
        
        $server_url = self::get_server_url();
        $url = add_query_arg('action', 'get_pricing', $server_url);
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
        ));
        
        if (is_wp_error($response)) {
            return self::get_default_pricing();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['pricing'])) {
            return self::get_default_pricing();
        }
        
        self::$pricing_data = $data['pricing'];
        set_transient($transient_key, $data['pricing'], 3600); // Cache 1 hour
        
        return $data['pricing'];
    }
    
    /**
     * Get Default Pricing (fallback)
     */
    private static function get_default_pricing() {
        return array(
            'free' => array(
                'price' => 0,
                'currency' => '\u20ac',
                'label' => 'FREE',
                'description' => 'Perfekt zum Testen und f\u00fcr kleine Restaurants',
                'max_items' => 20,
                'features' => array(),
            ),
            'free_plus' => array(
                'price' => 15,
                'currency' => '\u20ac',
                'label' => 'FREE+',
                'description' => 'Erweiterte Kapazit\u00e4t f\u00fcr mittelgro\u00dfe Men\u00fcs',
                'max_items' => 60,
                'features' => array(),
            ),
            'pro' => array(
                'price' => 29,
                'currency' => '\u20ac',
                'label' => 'PRO',
                'description' => 'Professionelle L\u00f6sung f\u00fcr umfangreiche Speisekarten',
                'max_items' => 200,
                'features' => array(),
            ),
            'pro_plus' => array(
                'price' => 49,
                'currency' => '\u20ac',
                'label' => 'PRO+',
                'description' => 'PRO + Dark Mode + Warenkorb-System',
                'max_items' => 200,
                'features' => array('Dark Mode', 'Warenkorb'),
            ),
            'ultimate' => array(
                'price' => 79,
                'currency' => '\u20ac',
                'label' => 'ULTIMATE',
                'description' => 'Alle Features + unbegrenzte Gerichte',
                'max_items' => 900,
                'features' => array('Dark Mode', 'Warenkorb', 'Unlimited'),
            ),
        );
    }
    
    /**
     * Test Server Connection
     */
    public static function test_server() {
        $server_url = self::get_server_url();
        $url = add_query_arg('action', 'status', $server_url);
        
        $response = wp_remote_get($url, array(
            'timeout' => 5,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Server nicht erreichbar: ' . $response->get_error_message(),
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data && isset($data['status']) && $data['status'] === 'online') {
            return array(
                'success' => true,
                'message' => 'Server online (Version ' . ($data['version'] ?? 'unknown') . ')',
                'data' => $data,
            );
        }
        
        return array(
            'success' => false,
            'message' => 'Server antwortet nicht korrekt',
        );
    }
    
    /**
     * Refresh Pricing Data
     */
    public static function refresh_pricing() {
        delete_transient('wpr_pricing_data');
        self::$pricing_data = null;
        return self::get_pricing();
    }
}