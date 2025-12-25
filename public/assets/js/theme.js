/**
 * Theme & Utility Script
 * Handles Dark/Light mode toggle and Copy to Clipboard
 */

const ThemeManager = {
    init: function () {
        this.themeToggleBtn = document.getElementById('theme-toggle');
        this.body = document.body;
        this.loadTheme();

        if (this.themeToggleBtn) {
            this.themeToggleBtn.addEventListener('click', () => this.toggleTheme());
        }
    },

    loadTheme: function () {
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            this.body.classList.add('dark-mode');
            this.updateIcon(true);
        } else {
            this.body.classList.remove('dark-mode');
            this.updateIcon(false);
        }
    },

    toggleTheme: function () {
        const isDark = this.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        this.updateIcon(isDark);
    },

    updateIcon: function (isDark) {
        if (!this.themeToggleBtn) return;
        // Simple text/icon swap
        this.themeToggleBtn.innerHTML = isDark ? '☀️' : '🌙';
        this.themeToggleBtn.setAttribute('aria-label', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
    }
};

const Utils = {
    copyToClipboard: async function (text, btnElement) {
        try {
            await navigator.clipboard.writeText(text);
            this.showTooltip(btnElement, 'Copied!');
        } catch (err) {
            console.error('Failed to copy: ', err);
            this.showTooltip(btnElement, 'Failed');
        }
    },

    showTooltip: function (element, message) {
        const originalText = element.innerHTML;
        const width = element.offsetWidth;

        element.innerHTML = message;
        element.style.width = `${width}px`; // Maintain width handling

        setTimeout(() => {
            element.innerHTML = originalText;
            element.style.width = '';
        }, 2000);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
});
