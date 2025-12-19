/**
 * Admin Scripts
 * @package WP_Restaurant_Menu
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // License key formatter
        $('input[name="license_key"]').on('input', function() {
            let value = $(this).val().toUpperCase().replace(/[^A-Z0-9-]/g, '');
            
            // Add dashes automatically
            if (value.length > 3 && value.charAt(3) !== '-') {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
            
            $(this).val(value);
        });
        
        // Confirm deactivation
        $('input[name="wpr_deactivate_license"]').on('click', function(e) {
            if (!confirm('Lizenz wirklich deaktivieren?')) {
                e.preventDefault();
            }
        });
        
        // Import file validation
        $('input[name="import_file"]').on('change', function() {
            const file = this.files[0];
            
            if (file && !file.name.endsWith('.json')) {
                alert('Bitte wählen Sie eine JSON-Datei aus.');
                $(this).val('');
            }
        });
        
        // Price input formatting
        $('input[name="wpr_price"]').on('blur', function() {
            let value = $(this).val().replace(',', '.');
            if (value && !isNaN(value)) {
                $(this).val(parseFloat(value).toFixed(2));
            }
        });
        
    });
    
})(jQuery);