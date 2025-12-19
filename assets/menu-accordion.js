/**
 * Menu Accordion Functionality
 * @package WP_Restaurant_Menu
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        const categoryHeaders = $('.wpr-category-header');
        
        if (categoryHeaders.length === 0) {
            return;
        }
        
        // Initialize - first category open, rest closed
        categoryHeaders.each(function(index) {
            const $header = $(this);
            const $items = $header.next('.wpr-category-items');
            
            if (index === 0) {
                // First category open
                $items.show();
                $header.attr('aria-expanded', 'true');
            } else {
                // Other categories closed
                $items.hide();
                $header.attr('aria-expanded', 'false');
            }
        });
        
        // Click handler
        categoryHeaders.on('click', function() {
            const $header = $(this);
            const $items = $header.next('.wpr-category-items');
            const isOpen = $items.is(':visible');
            
            if (isOpen) {
                // Close
                $items.slideUp(300);
                $header.attr('aria-expanded', 'false');
            } else {
                // Open
                $items.slideDown(300);
                $header.attr('aria-expanded', 'true');
            }
        });
        
        // Keyboard navigation
        categoryHeaders.on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });
        
        // Make headers focusable
        categoryHeaders.attr('tabindex', '0');
    });
    
})(jQuery);