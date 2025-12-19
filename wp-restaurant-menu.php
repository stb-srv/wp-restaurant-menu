<?php
/**
 * Plugin Name: WP Restaurant Menu
 * Plugin URI: https://github.com/stb-srv/wp-restaurant-menu
 * Description: Modernes WordPress-Plugin zur Verwaltung von Restaurant-Speisekarten mit Lizenz-Server, Dark Mode, Warenkorb-System und Allergenkennzeichnung
 * Version: 1.7.2
 * Author: STB-SRV
 * Author URI: https://stb-srv.de
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-restaurant-menu
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Plugin Constants
define('WP_RESTAURANT_MENU_VERSION', '1.7.2');
define('WP_RESTAURANT_MENU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_RESTAURANT_MENU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_RESTAURANT_MENU_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include Core Classes
require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'includes/class-wpr-license.php';
require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'includes/class-wpr-import-export.php';

/**
 * Main Plugin Class
 */
class WP_Restaurant_Menu {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_wpr_menu_item', array($this, 'save_menu_item_meta'), 10, 2);
        add_shortcode('restaurant_menu', array($this, 'render_menu_shortcode'));
    }
    
    /**
     * Register Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name' => 'Men\u00fcpunkte',
            'singular_name' => 'Men\u00fcpunkt',
            'menu_name' => 'Restaurant Men\u00fc',
            'add_new' => 'Neues Gericht',
            'add_new_item' => 'Neues Gericht hinzuf\u00fcgen',
            'edit_item' => 'Gericht bearbeiten',
            'new_item' => 'Neues Gericht',
            'view_item' => 'Gericht ansehen',
            'search_items' => 'Gerichte suchen',
            'not_found' => 'Keine Gerichte gefunden',
            'not_found_in_trash' => 'Keine Gerichte im Papierkorb',
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-food',
            'menu_position' => 25,
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'show_in_rest' => true,
        );
        
        register_post_type('wpr_menu_item', $args);
    }
    
    /**
     * Register Taxonomies
     */
    public function register_taxonomies() {
        // Categories (hierarchical)
        register_taxonomy('wpr_category', 'wpr_menu_item', array(
            'labels' => array(
                'name' => 'Kategorien',
                'singular_name' => 'Kategorie',
                'add_new_item' => 'Neue Kategorie hinzuf\u00fcgen',
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => false,
            'show_in_rest' => true,
        ));
        
        // Menu Lists (non-hierarchical)
        register_taxonomy('wpr_menu_list', 'wpr_menu_item', array(
            'labels' => array(
                'name' => 'Men\u00fckarten',
                'singular_name' => 'Men\u00fckarte',
                'add_new_item' => 'Neue Men\u00fckarte hinzuf\u00fcgen',
            ),
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => false,
            'show_in_rest' => true,
        ));
    }
    
    /**
     * Add Admin Menus
     */
    public function add_admin_menus() {
        add_submenu_page(
            'edit.php?post_type=wpr_menu_item',
            'Einstellungen',
            '\u2699\ufe0f Einstellungen',
            'manage_options',
            'wpr-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=wpr_menu_item',
            'Lizenz',
            '\ud83d\udd11 Lizenz',
            'manage_options',
            'wpr-license',
            array($this, 'render_license_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=wpr_menu_item',
            'Import / Export',
            '\ud83d\udcc a Import / Export',
            'manage_options',
            'wpr-import-export',
            array($this, 'render_import_export_page')
        );
    }
    
    /**
     * Enqueue Admin Assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wpr_menu_item') !== false || strpos($hook, 'wpr-') !== false) {
            wp_enqueue_style('wpr-admin-styles', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/admin-styles.css', array(), WP_RESTAURANT_MENU_VERSION);
            wp_enqueue_script('wpr-admin-scripts', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/admin-scripts.js', array('jquery'), WP_RESTAURANT_MENU_VERSION, true);
        }
    }
    
    /**
     * Enqueue Frontend Assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style('wpr-menu-styles', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/menu-styles.css', array(), WP_RESTAURANT_MENU_VERSION);
        
        if (WPR_License::has_dark_mode()) {
            wp_enqueue_style('wpr-dark-mode', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/dark-mode.css', array('wpr-menu-styles'), WP_RESTAURANT_MENU_VERSION);
            wp_enqueue_script('wpr-dark-mode-js', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/dark-mode.js', array(), WP_RESTAURANT_MENU_VERSION, true);
        }
        
        wp_enqueue_script('wpr-menu-search', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/menu-search.js', array('jquery'), WP_RESTAURANT_MENU_VERSION, true);
        wp_enqueue_script('wpr-menu-accordion', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/menu-accordion.js', array('jquery'), WP_RESTAURANT_MENU_VERSION, true);
        
        if (WPR_License::has_cart()) {
            wp_enqueue_script('wpr-cart', WP_RESTAURANT_MENU_PLUGIN_URL . 'assets/cart.js', array('jquery'), WP_RESTAURANT_MENU_VERSION, true);
        }
    }
    
    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'wpr_menu_item_details',
            'Gericht-Details',
            array($this, 'render_meta_box'),
            'wpr_menu_item',
            'normal',
            'high'
        );
    }
    
    /**
     * Render Meta Box
     */
    public function render_meta_box($post) {
        wp_nonce_field('wpr_save_menu_item', 'wpr_menu_item_nonce');
        
        $dish_number = get_post_meta($post->ID, '_wpr_dish_number', true);
        $price = get_post_meta($post->ID, '_wpr_price', true);
        $vegan = get_post_meta($post->ID, '_wpr_vegan', true);
        $vegetarian = get_post_meta($post->ID, '_wpr_vegetarian', true);
        $allergens = get_post_meta($post->ID, '_wpr_allergens', true);
        if (!is_array($allergens)) {
            $allergens = array();
        }
        
        require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'admin/meta-box-template.php';
    }
    
    /**
     * Save Menu Item Meta
     */
    public function save_menu_item_meta($post_id, $post) {
        if (!isset($_POST['wpr_menu_item_nonce']) || !wp_verify_nonce($_POST['wpr_menu_item_nonce'], 'wpr_save_menu_item')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['wpr_dish_number'])) {
            update_post_meta($post_id, '_wpr_dish_number', sanitize_text_field($_POST['wpr_dish_number']));
        }
        
        if (isset($_POST['wpr_price'])) {
            update_post_meta($post_id, '_wpr_price', sanitize_text_field($_POST['wpr_price']));
        }
        
        update_post_meta($post_id, '_wpr_vegan', isset($_POST['wpr_vegan']) ? 1 : 0);
        update_post_meta($post_id, '_wpr_vegetarian', isset($_POST['wpr_vegetarian']) ? 1 : 0);
        
        if (isset($_POST['wpr_allergens']) && is_array($_POST['wpr_allergens'])) {
            $allergens = array_map('sanitize_text_field', $_POST['wpr_allergens']);
            update_post_meta($post_id, '_wpr_allergens', $allergens);
        } else {
            delete_post_meta($post_id, '_wpr_allergens');
        }
    }
    
    /**
     * Render Settings Page
     */
    public function render_settings_page() {
        require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Render License Page
     */
    public function render_license_page() {
        require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'admin/license-page.php';
    }
    
    /**
     * Render Import/Export Page
     */
    public function render_import_export_page() {
        require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'admin/import-export-page.php';
    }
    
    /**
     * Render Menu Shortcode
     */
    public function render_menu_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'menu' => '',
            'columns' => get_option('wpr_columns', '2'),
            'show_search' => get_option('wpr_show_search', 'yes'),
            'show_images' => get_option('wpr_show_images', 'yes'),
            'image_position' => get_option('wpr_image_position', 'left'),
            'group_by_category' => get_option('wpr_group_by_category', 'yes'),
        ), $atts);
        
        require_once WP_RESTAURANT_MENU_PLUGIN_DIR . 'public/shortcode-menu.php';
        return wpr_render_menu($atts);
    }
}

// Initialize Plugin
function wp_restaurant_menu_init() {
    return WP_Restaurant_Menu::get_instance();
}
add_action('plugins_loaded', 'wp_restaurant_menu_init');

/**
 * Activation Hook
 */
function wp_restaurant_menu_activate() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options
    add_option('wpr_currency_symbol', '\u20ac');
    add_option('wpr_currency_position', 'after');
    add_option('wpr_show_images', 'yes');
    add_option('wpr_image_position', 'left');
    add_option('wpr_show_search', 'yes');
    add_option('wpr_group_by_category', 'yes');
    add_option('wpr_columns', '2');
    add_option('wpr_license_server_url', 'https://license.stb-srv.de/api.php');
}
register_activation_hook(__FILE__, 'wp_restaurant_menu_activate');

/**
 * Deactivation Hook
 */
function wp_restaurant_menu_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_restaurant_menu_deactivate');