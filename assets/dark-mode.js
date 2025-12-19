/**
 * Dark Mode Toggle
 * @package WP_Restaurant_Menu
 */

(function() {
    'use strict';
    
    // Configuration from PHP
    const config = {
        enabled: wprDarkMode?.enabled || false,
        scope: wprDarkMode?.scope || 'menu',
        method: wprDarkMode?.method || 'manual',
        position: wprDarkMode?.position || 'bottom-right'
    };
    
    if (!config.enabled) {
        return;
    }
    
    // Get target element
    const getTarget = () => {
        if (config.scope === 'global') {
            return document.documentElement;
        } else {
            return document.querySelector('.wpr-menu-container') || document.documentElement;
        }
    };
    
    // Get current theme
    const getCurrentTheme = () => {
        const saved = localStorage.getItem('wpr-theme');
        if (saved) {
            return saved;
        }
        
        if (config.method === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        
        return 'light';
    };
    
    // Apply theme
    const applyTheme = (theme) => {
        const target = getTarget();
        target.setAttribute('data-theme', theme);
        localStorage.setItem('wpr-theme', theme);
        
        // Update button icon if exists
        const button = document.querySelector('.wpr-dark-mode-toggle');
        if (button) {
            const icon = button.querySelector('.wpr-dark-mode-icon');
            if (icon) {
                icon.textContent = theme === 'dark' ? '🌙' : '☀️';
            }
        }
    };
    
    // Toggle theme
    const toggleTheme = () => {
        const current = getCurrentTheme();
        const newTheme = current === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
    };
    
    // Create toggle button
    const createToggleButton = () => {
        if (config.method !== 'manual') {
            return;
        }
        
        const button = document.createElement('button');
        button.className = 'wpr-dark-mode-toggle position-' + (config.position === 'bottom-left' ? 'left' : 'right');
        button.setAttribute('aria-label', 'Toggle Dark Mode');
        button.innerHTML = `
            <span class="wpr-dark-mode-icon">${getCurrentTheme() === 'dark' ? '🌙' : '☀️'}</span>
            <span>Dark Mode</span>
        `;
        
        button.addEventListener('click', toggleTheme);
        document.body.appendChild(button);
    };
    
    // Initialize
    const init = () => {
        // Apply initial theme
        applyTheme(getCurrentTheme());
        
        // Create toggle button for manual mode
        createToggleButton();
        
        // Listen for system theme changes in auto mode
        if (config.method === 'auto') {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                applyTheme(e.matches ? 'dark' : 'light');
            });
        }
    };
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();