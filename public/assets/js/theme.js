// Gestionnaire de thème sombre/clair
class ThemeManager {
    constructor() {
        this.theme = this.loadTheme();
        this.init();
    }

    // Charger le thème depuis localStorage
    loadTheme() {
        const saved = localStorage.getItem('autolink_theme');
        if (saved) {
            return saved;
        }
        // Détecter la préférence système
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    // Sauvegarder le thème
    saveTheme() {
        localStorage.setItem('autolink_theme', this.theme);
    }

    // Appliquer le thème
    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
        document.body.classList.toggle('dark-theme', this.theme === 'dark');
        this.updateThemeButton();
        this.saveTheme();
    }

    // Basculer le thème
    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        this.applyTheme();
        this.showNotification(`Mode ${this.theme === 'dark' ? 'sombre' : 'clair'} activé`, 'info');
    }

    // Initialiser
    init() {
        this.applyTheme();
        this.createThemeButton();
        this.listenForSystemPreference();
    }

    // Créer le bouton de thème dans la navbar
    createThemeButton() {
        const navbar = document.querySelector('.navbar-nav');
        if (navbar && !document.querySelector('.theme-nav-btn')) {
            const themeBtn = document.createElement('li');
            themeBtn.className = 'nav-item';
            themeBtn.innerHTML = `
                <button class="nav-link theme-nav-btn" type="button" style="color:#fff;font-weight:500;background:none;border:none;">
                    <i class="fas fa-sun theme-icon"></i>
                </button>
            `;
            
            themeBtn.addEventListener('click', () => {
                this.toggleTheme();
            });
            
            navbar.appendChild(themeBtn);
        }
    }

    // Mettre à jour l'icône du bouton
    updateThemeButton() {
        const icon = document.querySelector('.theme-icon');
        if (icon) {
            icon.className = this.theme === 'dark' ? 'fas fa-moon theme-icon' : 'fas fa-sun theme-icon';
        }
    }

    // Écouter les changements de préférence système
    listenForSystemPreference() {
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('autolink_theme')) {
                    this.theme = e.matches ? 'dark' : 'light';
                    this.applyTheme();
                }
            });
        }
    }

    // Afficher une notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }
}

// Initialiser le gestionnaire de thème
let themeManager;
document.addEventListener('DOMContentLoaded', () => {
    themeManager = new ThemeManager();
}); 