/**
 * Theme Management System
 * Supports: light, dark, auto (system preference)
 */

class ThemeManager {
    constructor() {
        this.storageKey = 'theme_preference';
        this.currentTheme = this.getStoredTheme() || 'auto';
        this.init();
    }

    init() {
        // Apply theme on page load
        this.applyTheme(this.currentTheme);

        // Listen for system theme changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.currentTheme === 'auto') {
                this.applyTheme('auto');
            }
        });
    }

    getStoredTheme() {
        return localStorage.getItem(this.storageKey);
    }

    setStoredTheme(theme) {
        localStorage.setItem(this.storageKey, theme);
    }

    getPreferredTheme() {
        if (this.currentTheme !== 'auto') {
            return this.currentTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    applyTheme(theme) {
        this.currentTheme = theme;
        const activeTheme = this.getPreferredTheme();
        
        // Set Bootstrap 5 theme attribute
        document.documentElement.setAttribute('data-bs-theme', activeTheme);
        
        // Dispatch custom event for other components to listen
        window.dispatchEvent(new CustomEvent('theme-changed', { 
            detail: { theme: activeTheme, preference: theme } 
        }));
    }

    setTheme(theme) {
        this.setStoredTheme(theme);
        this.applyTheme(theme);
    }

    cycleTheme() {
        const themes = ['light', 'dark', 'auto'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextTheme = themes[(currentIndex + 1) % themes.length];
        this.setTheme(nextTheme);
        return nextTheme;
    }

    getCurrentTheme() {
        return this.currentTheme;
    }

    getActiveTheme() {
        return this.getPreferredTheme();
    }
}

// Initialize theme manager
window.themeManager = new ThemeManager();

// Theme toggle button handler
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        // Update button icon on init
        updateThemeToggleIcon(toggleBtn);

        toggleBtn.addEventListener('click', () => {
            window.themeManager.cycleTheme();
            updateThemeToggleIcon(toggleBtn);
        });

        // Update icon when theme changes from other sources
        window.addEventListener('theme-changed', () => {
            updateThemeToggleIcon(toggleBtn);
        });
    }
});

function updateThemeToggleIcon(btn) {
    const theme = window.themeManager.getCurrentTheme();
    const icons = {
        light: '☀️',
        dark: '🌙',
        auto: '🔄'
    };
    btn.innerHTML = icons[theme] || icons.auto;
    btn.setAttribute('title', `Current: ${theme}`);
}
