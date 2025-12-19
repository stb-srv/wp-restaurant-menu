<?php
/**
 * Meta Box Template for Menu Item
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

$allergens_list = array(
    'a' => array('name' => 'A - Glutenhaltiges Getreide', 'icon' => '🌾'),
    'b' => array('name' => 'B - Krebstiere', 'icon' => '🦀'),
    'c' => array('name' => 'C - Eier', 'icon' => '🥚'),
    'd' => array('name' => 'D - Fisch', 'icon' => '🐟'),
    'e' => array('name' => 'E - Erdnüsse', 'icon' => '🥜'),
    'f' => array('name' => 'F - Soja', 'icon' => '🌱'),
    'g' => array('name' => 'G - Milch/Laktose', 'icon' => '🥛'),
    'h' => array('name' => 'H - Schalenfrüchte', 'icon' => '🌰'),
    'l' => array('name' => 'L - Sellerie', 'icon' => '🥬'),
    'm' => array('name' => 'M - Senf', 'icon' => '🍯'),
    'n' => array('name' => 'N - Sesamsamen', 'icon' => '🌾'),
    'o' => array('name' => 'O - Schwefeldioxid', 'icon' => '🧪'),
    'p' => array('name' => 'P - Lupinen', 'icon' => '🏺'),
    'r' => array('name' => 'R - Weichtiere', 'icon' => '🦐'),
);
?>

<style>
.wpr-meta-box-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 15px 0;
}
.wpr-meta-field {
    margin-bottom: 15px;
}
.wpr-meta-field label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}
.wpr-meta-field input[type="text"],
.wpr-meta-field input[type="number"] {
    width: 100%;
}
.wpr-allergens-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 10px;
}
.wpr-allergen-item label {
    display: flex;
    align-items: center;
    padding: 8px;
    background: #f5f5f5;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}
.wpr-allergen-item label:hover {
    background: #e0e0e0;
}
.wpr-allergen-item input[type="checkbox"] {
    margin-right: 8px;
}
.wpr-allergen-icon {
    margin-right: 5px;
}
</style>

<div class="wpr-meta-box-grid">
    <div>
        <div class="wpr-meta-field">
            <label for="wpr_dish_number">Gericht-Nummer</label>
            <input type="text" 
                   id="wpr_dish_number" 
                   name="wpr_dish_number" 
                   value="<?php echo esc_attr($dish_number); ?>" 
                   placeholder="z.B. 12">
        </div>
        
        <div class="wpr-meta-field">
            <label for="wpr_price">Preis</label>
            <input type="text" 
                   id="wpr_price" 
                   name="wpr_price" 
                   value="<?php echo esc_attr($price); ?>" 
                   placeholder="z.B. 12.50">
        </div>
    </div>
    
    <div>
        <div class="wpr-meta-field">
            <label>
                <input type="checkbox" 
                       name="wpr_vegan" 
                       value="1" 
                       <?php checked($vegan, 1); ?>>
                🌿 Vegan
            </label>
        </div>
        
        <div class="wpr-meta-field">
            <label>
                <input type="checkbox" 
                       name="wpr_vegetarian" 
                       value="1" 
                       <?php checked($vegetarian, 1); ?>>
                🥬 Vegetarisch
            </label>
        </div>
    </div>
</div>

<div class="wpr-meta-field">
    <label>Allergene</label>
    <div class="wpr-allergens-grid">
        <?php foreach ($allergens_list as $key => $allergen): ?>
        <div class="wpr-allergen-item">
            <label>
                <input type="checkbox" 
                       name="wpr_allergens[]" 
                       value="<?php echo esc_attr($key); ?>" 
                       <?php checked(in_array($key, $allergens)); ?>>
                <span class="wpr-allergen-icon"><?php echo $allergen['icon']; ?></span>
                <span><?php echo esc_html($allergen['name']); ?></span>
            </label>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<p class="description">
    Diese Informationen werden im Frontend angezeigt.
</p>