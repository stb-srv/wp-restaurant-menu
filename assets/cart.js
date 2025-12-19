/**
 * Shopping Cart Functionality
 * @package WP_Restaurant_Menu
 */

(function($) {
    'use strict';
    
    // Cart object
    const Cart = {
        items: [],
        
        init: function() {
            this.loadFromStorage();
            this.renderCartSidebar();
            this.bindEvents();
            this.updateCartCount();
        },
        
        loadFromStorage: function() {
            const saved = localStorage.getItem('wpr-cart');
            if (saved) {
                try {
                    this.items = JSON.parse(saved);
                } catch (e) {
                    this.items = [];
                }
            }
        },
        
        saveToStorage: function() {
            localStorage.setItem('wpr-cart', JSON.stringify(this.items));
        },
        
        addItem: function(id, title, price) {
            const existing = this.items.find(item => item.id === id);
            
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({
                    id: id,
                    title: title,
                    price: parseFloat(price),
                    quantity: 1
                });
            }
            
            this.saveToStorage();
            this.renderCart();
            this.updateCartCount();
            this.showNotification('Zum Warenkorb hinzugefügt');
        },
        
        removeItem: function(id) {
            this.items = this.items.filter(item => item.id !== id);
            this.saveToStorage();
            this.renderCart();
            this.updateCartCount();
        },
        
        updateQuantity: function(id, quantity) {
            const item = this.items.find(item => item.id === id);
            if (item) {
                item.quantity = parseInt(quantity);
                if (item.quantity <= 0) {
                    this.removeItem(id);
                } else {
                    this.saveToStorage();
                    this.renderCart();
                    this.updateCartCount();
                }
            }
        },
        
        clearCart: function() {
            if (confirm('Warenkorb wirklich leeren?')) {
                this.items = [];
                this.saveToStorage();
                this.renderCart();
                this.updateCartCount();
            }
        },
        
        getTotal: function() {
            return this.items.reduce((total, item) => {
                return total + (item.price * item.quantity);
            }, 0);
        },
        
        renderCartSidebar: function() {
            if ($('.wpr-cart-sidebar').length > 0) {
                return;
            }
            
            const sidebar = $(`
                <div class="wpr-cart-sidebar">
                    <div class="wpr-cart-header">
                        <h3>Warenkorb</h3>
                        <button class="wpr-cart-close">&times;</button>
                    </div>
                    <div class="wpr-cart-items"></div>
                    <div class="wpr-cart-footer">
                        <div class="wpr-cart-total">
                            <strong>Gesamt:</strong>
                            <span class="wpr-cart-total-amount">0,00 €</span>
                        </div>
                        <button class="wpr-cart-checkout button">Bestellung senden</button>
                        <button class="wpr-cart-clear button">Warenkorb leeren</button>
                    </div>
                </div>
                <div class="wpr-cart-overlay"></div>
            `);
            
            $('body').append(sidebar);
            this.renderCart();
        },
        
        renderCart: function() {
            const container = $('.wpr-cart-items');
            
            if (this.items.length === 0) {
                container.html('<p class="wpr-cart-empty">Warenkorb ist leer</p>');
            } else {
                let html = '';
                this.items.forEach(item => {
                    html += `
                        <div class="wpr-cart-item" data-id="${item.id}">
                            <div class="wpr-cart-item-info">
                                <strong>${item.title}</strong>
                                <span class="wpr-cart-item-price">${item.price.toFixed(2)} €</span>
                            </div>
                            <div class="wpr-cart-item-controls">
                                <input type="number" 
                                       class="wpr-cart-item-quantity" 
                                       value="${item.quantity}" 
                                       min="1" 
                                       max="99">
                                <button class="wpr-cart-item-remove">&times;</button>
                            </div>
                        </div>
                    `;
                });
                container.html(html);
            }
            
            $('.wpr-cart-total-amount').text(this.getTotal().toFixed(2) + ' €');
        },
        
        updateCartCount: function() {
            const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
            let badge = $('.wpr-cart-badge');
            
            if (badge.length === 0) {
                badge = $('<span class="wpr-cart-badge"></span>');
                $('.wpr-cart-toggle').append(badge);
            }
            
            badge.text(count);
            badge.toggle(count > 0);
        },
        
        showNotification: function(message) {
            const notification = $(`<div class="wpr-notification">${message}</div>`);
            $('body').append(notification);
            
            setTimeout(() => {
                notification.addClass('show');
            }, 10);
            
            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        },
        
        bindEvents: function() {
            const self = this;
            
            // Add to cart buttons
            $(document).on('click', '.wpr-add-to-cart', function(e) {
                e.preventDefault();
                const $button = $(this);
                const id = $button.data('id');
                const title = $button.data('title');
                const price = $button.data('price');
                
                self.addItem(id, title, price);
            });
            
            // Open cart
            $(document).on('click', '.wpr-cart-toggle', function() {
                $('.wpr-cart-sidebar').addClass('open');
                $('.wpr-cart-overlay').addClass('show');
            });
            
            // Close cart
            $(document).on('click', '.wpr-cart-close, .wpr-cart-overlay', function() {
                $('.wpr-cart-sidebar').removeClass('open');
                $('.wpr-cart-overlay').removeClass('show');
            });
            
            // Update quantity
            $(document).on('change', '.wpr-cart-item-quantity', function() {
                const id = $(this).closest('.wpr-cart-item').data('id');
                const quantity = $(this).val();
                self.updateQuantity(id, quantity);
            });
            
            // Remove item
            $(document).on('click', '.wpr-cart-item-remove', function() {
                const id = $(this).closest('.wpr-cart-item').data('id');
                self.removeItem(id);
            });
            
            // Clear cart
            $(document).on('click', '.wpr-cart-clear', function() {
                self.clearCart();
            });
            
            // Checkout
            $(document).on('click', '.wpr-cart-checkout', function() {
                if (self.items.length === 0) {
                    alert('Warenkorb ist leer');
                    return;
                }
                
                // Create order summary
                let summary = 'Bestellung:\n\n';
                self.items.forEach(item => {
                    summary += `${item.quantity}x ${item.title} - ${(item.price * item.quantity).toFixed(2)} €\n`;
                });
                summary += `\nGesamt: ${self.getTotal().toFixed(2)} €`;
                
                alert(summary + '\n\nDiese Funktion ist noch in Entwicklung.');
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        Cart.init();
    });
    
})(jQuery);