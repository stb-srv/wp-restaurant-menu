<?php
/**
 * Settings Page
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Handle form submission
if (isset($_POST['wpr_save_settings']) && check_admin_referer('wpr_settings_action', 'wpr_settings_nonce')) {
    update_option('wpr_currency_symbol', sanitize_text_field($_POST['wpr_currency_symbol']));
    update_option('wpr_currency_position', sanitize_text_field($_POST['wpr_currency_position']));
    update_option('wpr_show_images', sanitize_text_field($_POST['wpr_show_images']));
    update_option('wpr_image_position', sanitize_text_field($_POST['wpr_image_position']));
    update_option('wpr_show_search', sanitize_text_field($_POST['wpr_show_search']));
    update_option('wpr_group_by_category', sanitize_text_field($_POST['wpr_group_by_category']));
    update_option('wpr_columns', sanitize_text_field($_POST['wpr_columns']));
    
    if (WPR_License::has_dark_mode()) {
        update_option('wpr_dark_mode_enabled', sanitize_text_field($_POST['wpr_dark_mode_enabled']));
        update_option('wpr_dark_mode_scope', sanitize_text_field($_POST['wpr_dark_mode_scope']));
        update_option('wpr_dark_mode_method', sanitize_text_field($_POST['wpr_dark_mode_method']));
        update_option('wpr_dark_mode_position', sanitize_text_field($_POST['wpr_dark_mode_position']));
    }
    
    echo '<div class="notice notice-success"><p>Einstellungen gespeichert!</p></div>';
}

// Get current values
$currency_symbol = get_option('wpr_currency_symbol', '€');
$currency_position = get_option('wpr_currency_position', 'after');
$show_images = get_option('wpr_show_images', 'yes');
$image_position = get_option('wpr_image_position', 'left');
$show_search = get_option('wpr_show_search', 'yes');
$group_by_category = get_option('wpr_group_by_category', 'yes');
$columns = get_option('wpr_columns', '2');

$dark_mode_enabled = get_option('wpr_dark_mode_enabled', 'no');
$dark_mode_scope = get_option('wpr_dark_mode_scope', 'menu');
$dark_mode_method = get_option('wpr_dark_mode_method', 'manual');
$dark_mode_position = get_option('wpr_dark_mode_position', 'bottom-right');
?>

<div class="wrap">
    <h1>🍴 Restaurant Menü - Einstellungen</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wpr_settings_action', 'wpr_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th colspan="2"><h2>Währungseinstellungen</h2></th>
            </tr>
            <tr>
                <th><label for="wpr_currency_symbol">Währungssymbol</label></th>
                <td>
                    <select name="wpr_currency_symbol" id="wpr_currency_symbol">
                        <option value="€" <?php selected($currency_symbol, '€'); ?>>€ (Euro)</option>
                        <option value="EUR" <?php selected($currency_symbol, 'EUR'); ?>>EUR</option>
                        <option value="EURO" <?php selected($currency_symbol, 'EURO'); ?>>EURO</option>
                        <option value="$" <?php selected($currency_symbol, '$'); ?>>$ (Dollar)</option>
                        <option value="£" <?php selected($currency_symbol, '£'); ?>>£ (Pfund)</option>
                        <option value="CHF" <?php selected($currency_symbol, 'CHF'); ?>>CHF (Franken)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_currency_position">Währungsposition</label></th>
                <td>
                    <select name="wpr_currency_position" id="wpr_currency_position">
                        <option value="before" <?php selected($currency_position, 'before'); ?>>Vor Preis (€ 12,50)</option>
                        <option value="after" <?php selected($currency_position, 'after'); ?>>Nach Preis (12,50 €)</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th colspan="2"><h2>Bild-Einstellungen</h2></th>
            </tr>
            <tr>
                <th><label for="wpr_show_images">Bilder anzeigen</label></th>
                <td>
                    <select name="wpr_show_images" id="wpr_show_images">
                        <option value="yes" <?php selected($show_images, 'yes'); ?>>Ja</option>
                        <option value="no" <?php selected($show_images, 'no'); ?>>Nein</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_image_position">Bild-Position</label></th>
                <td>
                    <select name="wpr_image_position" id="wpr_image_position">
                        <option value="top" <?php selected($image_position, 'top'); ?>>Oben</option>
                        <option value="left" <?php selected($image_position, 'left'); ?>>Links</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th colspan="2"><h2>Layout-Einstellungen</h2></th>
            </tr>
            <tr>
                <th><label for="wpr_show_search">Suchfunktion</label></th>
                <td>
                    <select name="wpr_show_search" id="wpr_show_search">
                        <option value="yes" <?php selected($show_search, 'yes'); ?>>Ja</option>
                        <option value="no" <?php selected($show_search, 'no'); ?>>Nein</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_group_by_category">Nach Kategorien gruppieren</label></th>
                <td>
                    <select name="wpr_group_by_category" id="wpr_group_by_category">
                        <option value="yes" <?php selected($group_by_category, 'yes'); ?>>Ja (Accordion)</option>
                        <option value="no" <?php selected($group_by_category, 'no'); ?>>Nein</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_columns">Spalten-Layout</label></th>
                <td>
                    <select name="wpr_columns" id="wpr_columns">
                        <option value="1" <?php selected($columns, '1'); ?>>1 Spalte</option>
                        <option value="2" <?php selected($columns, '2'); ?>>2 Spalten</option>
                        <option value="3" <?php selected($columns, '3'); ?>>3 Spalten</option>
                    </select>
                </td>
            </tr>
            
            <?php if (WPR_License::has_dark_mode()): ?>
            <tr>
                <th colspan="2"><h2>🌙 Dark Mode (PRO+ Feature)</h2></th>
            </tr>
            <tr>
                <th><label for="wpr_dark_mode_enabled">Dark Mode aktivieren</label></th>
                <td>
                    <select name="wpr_dark_mode_enabled" id="wpr_dark_mode_enabled">
                        <option value="yes" <?php selected($dark_mode_enabled, 'yes'); ?>>Ja</option>
                        <option value="no" <?php selected($dark_mode_enabled, 'no'); ?>>Nein</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_dark_mode_scope">Bereich</label></th>
                <td>
                    <select name="wpr_dark_mode_scope" id="wpr_dark_mode_scope">
                        <option value="global" <?php selected($dark_mode_scope, 'global'); ?>>Global (ganze Website)</option>
                        <option value="menu" <?php selected($dark_mode_scope, 'menu'); ?>>Nur Menü-Bereich</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_dark_mode_method">Umschalt-Methode</label></th>
                <td>
                    <select name="wpr_dark_mode_method" id="wpr_dark_mode_method">
                        <option value="manual" <?php selected($dark_mode_method, 'manual'); ?>>Manuell (Toggle Button)</option>
                        <option value="auto" <?php selected($dark_mode_method, 'auto'); ?>>Automatisch (System)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpr_dark_mode_position">Toggle Position</label></th>
                <td>
                    <select name="wpr_dark_mode_position" id="wpr_dark_mode_position">
                        <option value="bottom-right" <?php selected($dark_mode_position, 'bottom-right'); ?>>Unten rechts</option>
                        <option value="bottom-left" <?php selected($dark_mode_position, 'bottom-left'); ?>>Unten links</option>
                    </select>
                </td>
            </tr>
            <?php else: ?>
            <tr>
                <th colspan="2">
                    <h2>🌙 Dark Mode</h2>
                    <p style="color: #999;">🔒 Nur verfügbar ab PRO+ Lizenz</p>
                </th>
            </tr>
            <?php endif; ?>
        </table>
        
        <p class="submit">
            <input type="submit" name="wpr_save_settings" class="button button-primary" value="Einstellungen speichern">
        </p>
    </form>
</div>