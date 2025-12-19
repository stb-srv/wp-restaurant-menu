/**
 * Menu Search Functionality
 * @package WP_Restaurant_Menu
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        const searchInput = $('.wpr-menu-search');
        
        if (searchInput.length === 0) {
            return;
        }
        
        // Add data attributes for search
        $('.wpr-menu-item').each(function() {
            const $item = $(this);
            const title = $item.find('.wpr-menu-item-title').text().toLowerCase();
            const description = $item.find('.wpr-menu-item-description').text().toLowerCase();
            const number = $item.find('.wpr-menu-item-number').text().toLowerCase();
            
            $item.attr('data-search-title', title);
            $item.attr('data-search-content', description + ' ' + number);
        });
        
        // Search input handler
        searchInput.on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            
            if (query === '') {
                // Show all items
                $('.wpr-menu-item').show();
                $('.wpr-category-section').show();
                return;
            }
            
            // Filter items
            $('.wpr-menu-item').each(function() {
                const $item = $(this);
                const title = $item.attr('data-search-title') || '';
                const content = $item.attr('data-search-content') || '';
                
                if (title.includes(query) || content.includes(query)) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
            
            // Hide empty categories
            $('.wpr-category-section').each(function() {
                const $section = $(this);
                const visibleItems = $section.find('.wpr-menu-item:visible').length;
                
                if (visibleItems === 0) {
                    $section.hide();
                } else {
                    $section.show();
                }
            });
        });
        
        // Clear search on ESC
        searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                $(this).trigger('input');
            }
        });
    });
    
})(jQuery);