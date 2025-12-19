<?php
/**
 * License Page
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Handle license activation
if (isset($_POST['wpr_activate_license']) && check_admin_referer('wpr_license_action', 'wpr_license_nonce')) {
    $key = sanitize_text_field($_POST['license_key']);
    $result = WPR_License::activate_license($key);
    
    if ($result['success']) {
        echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
    }
}

// Handle license deactivation
if (isset($_POST['wpr_deactivate_license']) && check_admin_referer('wpr_license_action', 'wpr_license_nonce')) {
    $result = WPR_License::deactivate_license();
    echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
}

// Handle server test
if (isset($_POST['wpr_test_server']) && check_admin_referer('wpr_license_action', 'wpr_license_nonce')) {
    $result = WPR_License::test_server();
    
    if ($result['success']) {
        echo '<div class="notice notice-success"><p>✅ ' . esc_html($result['message']) . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>❌ ' . esc_html($result['message']) . '</p></div>';
    }
}

// Handle pricing refresh
if (isset($_POST['wpr_refresh_pricing']) && check_admin_referer('wpr_license_action', 'wpr_license_nonce')) {
    WPR_License::refresh_pricing();
    echo '<div class="notice notice-success"><p>Preise aktualisiert!</p></div>';
}

// Get current license data
$license_data = WPR_License::get_license_data();
$license_type = WPR_License::get_license_type();
$license_key = WPR_License::get_license_key();
$max_items = WPR_License::get_max_items();
$current_count = WPR_License::get_current_count();
$features = WPR_License::get_features();
$pricing = WPR_License::get_pricing();
$server_url = WPR_License::get_server_url();
$current_domain = WPR_License::get_current_domain();
?>

<div class="wrap">
    <h1>🔑 Restaurant Menü - Lizenz</h1>
    
    <div style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #0073aa;">
        <h2>Aktueller Status</h2>
        <table class="widefat">
            <tr>
                <th>Lizenz-Typ</th>
                <td><strong><?php echo esc_html(strtoupper($license_type)); ?></strong></td>
            </tr>
            <tr>
                <th>Gerichte</th>
                <td>
                    <?php echo esc_html($current_count); ?> / <?php echo esc_html($max_items); ?>
                    <?php if (WPR_License::has_unlimited_items()): ?>
                        (Unbegrenzt)
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Features</th>
                <td>
                    <?php if (empty($features)): ?>
                        Basis-Features
                    <?php else: ?>
                        <?php echo esc_html(implode(', ', $features)); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Server-URL</th>
                <td><code><?php echo esc_html($server_url); ?></code></td>
            </tr>
            <tr>
                <th>Domain</th>
                <td><code><?php echo esc_html($current_domain); ?></code></td>
            </tr>
        </table>
    </div>
    
    <h2>Verfügbare Lizenzen</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        <?php foreach ($pricing as $type => $data): ?>
        <div style="background: #fff; padding: 20px; border: 2px solid <?php echo ($type === $license_type) ? '#0073aa' : '#ddd'; ?>; border-radius: 8px;">
            <h3><?php echo esc_html($data['label']); ?>
                <?php if ($type === $license_type): ?>
                    <span style="background: #0073aa; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px;">AKTIV</span>
                <?php endif; ?>
            </h3>
            <p style="font-size: 32px; font-weight: bold; margin: 10px 0;">
                <?php if ($data['price'] == 0): ?>
                    Kostenlos
                <?php else: ?>
                    <?php echo esc_html($data['price']); ?> <?php echo esc_html($data['currency']); ?>
                <?php endif; ?>
                <?php if ($data['price'] > 0): ?>
                    <span style="font-size: 14px; color: #999;">einmalig</span>
                <?php endif; ?>
            </p>
            <p><?php echo esc_html($data['description']); ?></p>
            <p><strong>Max. Gerichte:</strong> <?php echo esc_html($data['max_items']); ?></p>
            <?php if (!empty($data['features'])): ?>
            <p><strong>Features:</strong></p>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($data['features'] as $feature): ?>
                <li><?php echo esc_html($feature); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="background: #fff; padding: 20px; margin: 20px 0;">
        <h2>Lizenz aktivieren</h2>
        <form method="post" action="">
            <?php wp_nonce_field('wpr_license_action', 'wpr_license_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="license_key">Lizenzschlüssel</label></th>
                    <td>
                        <input type="text" 
                               name="license_key" 
                               id="license_key" 
                               class="regular-text" 
                               placeholder="WPR-XXXXX-XXXXX-XXXXX"
                               value="<?php echo esc_attr($license_key); ?>">
                        <p class="description">Format: WPR-XXXXX-XXXXX-XXXXX</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="wpr_activate_license" class="button button-primary" value="Lizenz aktivieren">
                <?php if (!empty($license_key)): ?>
                <input type="submit" name="wpr_deactivate_license" class="button" value="Lizenz deaktivieren" 
                       onclick="return confirm('Lizenz wirklich deaktivieren?');">
                <?php endif; ?>
            </p>
        </form>
    </div>
    
    <div style="background: #fff; padding: 20px; margin: 20px 0;">
        <h2>Aktionen</h2>
        <form method="post" action="" style="display: inline-block; margin-right: 10px;">
            <?php wp_nonce_field('wpr_license_action', 'wpr_license_nonce'); ?>
            <input type="submit" name="wpr_test_server" class="button" value="🔍 Server testen">
        </form>
        <form method="post" action="" style="display: inline-block;">
            <?php wp_nonce_field('wpr_license_action', 'wpr_license_nonce'); ?>
            <input type="submit" name="wpr_refresh_pricing" class="button" value="🔄 Preise aktualisieren">
        </form>
    </div>
</div>