<?php
/**
 * Import/Export Page
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Handle export
if (isset($_POST['wpr_export']) && check_admin_referer('wpr_import_export_action', 'wpr_import_export_nonce')) {
    WPR_Import_Export::download_export();
}

// Handle import
if (isset($_POST['wpr_import']) && check_admin_referer('wpr_import_export_action', 'wpr_import_export_nonce')) {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $json_data = file_get_contents($_FILES['import_file']['tmp_name']);
        $overwrite = isset($_POST['overwrite']) && $_POST['overwrite'] === '1';
        
        $result = WPR_Import_Export::import_menu($json_data, $overwrite);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . nl2br(esc_html($result['message'])) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>Bitte wählen Sie eine Datei aus.</p></div>';
    }
}

$item_count = wp_count_posts('wpr_menu_item');
$published_count = (int) $item_count->publish;
?>

<div class="wrap">
    <h1>📊 Restaurant Menü - Import / Export</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- Export -->
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2>📤 Export</h2>
            <p>Exportieren Sie alle Menüpunkte als JSON-Datei.</p>
            
            <div style="background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 4px;">
                <strong>Statistik:</strong><br>
                <?php echo esc_html($published_count); ?> veröffentlichte Gerichte
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('wpr_import_export_action', 'wpr_import_export_nonce'); ?>
                <input type="submit" name="wpr_export" class="button button-primary" value="Alle Gerichte exportieren">
            </form>
            
            <h3 style="margin-top: 30px;">Was wird exportiert?</h3>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Titel und Beschreibung</li>
                <li>Preis und Gericht-Nummer</li>
                <li>Vegan/Vegetarisch Kennzeichnung</li>
                <li>Allergene</li>
                <li>Kategorien und Menükarten</li>
                <li>Bild-URLs</li>
                <li>Reihenfolge</li>
            </ul>
        </div>
        
        <!-- Import -->
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2>📥 Import</h2>
            <p>Importieren Sie Menüpunkte aus einer JSON-Datei.</p>
            
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('wpr_import_export_action', 'wpr_import_export_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="import_file">JSON-Datei</label></th>
                        <td>
                            <input type="file" name="import_file" id="import_file" accept=".json" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="overwrite">Bestehende überschreiben</label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="overwrite" id="overwrite" value="1">
                                Gerichte mit gleichem Titel überschreiben
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="wpr_import" class="button button-primary" value="Import starten">
                </p>
            </form>
            
            <div style="background: #fff3cd; padding: 15px; margin: 15px 0; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong>⚠️ Hinweis:</strong><br>
                Der Import kann nicht rückgängig gemacht werden. Erstellen Sie vorher ein Backup!
            </div>
            
            <h3>Format-Anforderungen</h3>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Gültiges JSON-Format</li>
                <li>"items" Array erforderlich</li>
                <li>Kompatibel mit Export-Format</li>
            </ul>
        </div>
    </div>
    
    <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 8px;">
        <h2>📋 Beispiel JSON-Struktur</h2>
        <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto; border-radius: 4px;"><code>{
  "version": "1.7.2",
  "exported_at": "2024-12-19T10:00:00Z",
  "items": [
    {
      "title": "Pizza Margherita",
      "content": "Tomaten, Mozzarella, Basilikum",
      "dish_number": "12",
      "price": "8.50",
      "vegan": false,
      "vegetarian": true,
      "allergens": ["a", "g"],
      "categories": ["hauptgerichte"],
      "menu_lists": ["mittag"],
      "image_url": "https://example.com/pizza.jpg",
      "menu_order": 1
    }
  ]
}</code></pre>
    </div>
</div>