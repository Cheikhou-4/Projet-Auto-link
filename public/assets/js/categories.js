// Gestionnaire de la barre de catégories
class CategoriesManager {
    constructor() {
        this.currentCategory = 'all';
        this.init();
    }

    // Initialiser
    init() {
        this.addEventListeners();
        this.updateActiveCategory();
    }

    // Ajouter les événements
    addEventListeners() {
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const category = item.dataset.category;
                this.selectCategory(category);
            });
        });
    }

    // Sélectionner une catégorie
    selectCategory(category) {
        this.currentCategory = category;
        this.updateActiveCategory();
        this.filterVehicles(category);
        this.updateURL(category);
    }

    // Mettre à jour la catégorie active visuellement
    updateActiveCategory() {
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.category === this.currentCategory) {
                item.classList.add('active');
            }
        });
    }

    // Filtrer les véhicules
    filterVehicles(category) {
        const cards = document.querySelectorAll('.card');
        
        cards.forEach(card => {
            const vehicleType = this.getVehicleType(card);
            
            if (category === 'all' || vehicleType === category) {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.5s ease';
            } else {
                card.style.display = 'none';
            }
        });

        // Mettre à jour le compteur
        this.updateCounter();
    }

    // Obtenir le type de véhicule depuis la carte
    getVehicleType(card) {
        // Pour l'instant, on considère que tous les véhicules sont des voitures
        // Vous pouvez adapter cette logique selon votre base de données
        const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        
        if (title.includes('camion') || title.includes('truck')) return 'camion';
        if (title.includes('remorque') || title.includes('trailer')) return 'remorque';
        if (title.includes('moto') || title.includes('motorcycle')) return 'moto';
        if (title.includes('bus')) return 'bus';
        if (title.includes('utilitaire') || title.includes('van')) return 'utilitaire';
        if (title.includes('engin') || title.includes('tractor')) return 'engin';
        
        return 'voiture'; // Par défaut
    }

    // Mettre à jour le compteur
    updateCounter() {
        const visibleCards = document.querySelectorAll('.card[style*="display: block"], .card:not([style*="display: none"])');
        const totalCards = document.querySelectorAll('.card').length;
        
        // Vous pouvez ajouter un compteur visuel ici si nécessaire
        console.log(`${visibleCards.length} véhicules affichés sur ${totalCards} total`);
    }

    // Mettre à jour l'URL
    updateURL(category) {
        const url = new URL(window.location);
        if (category === 'all') {
            url.searchParams.delete('category');
        } else {
            url.searchParams.set('category', category);
        }
        window.history.pushState({}, '', url);
    }

    // Charger la catégorie depuis l'URL
    loadCategoryFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        if (category) {
            this.selectCategory(category);
        }
    }
}

// Ajouter les styles CSS pour l'animation
const categoryStyles = document.createElement('style');
categoryStyles.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(categoryStyles);

// Initialiser le gestionnaire de catégories
let categoriesManager;
document.addEventListener('DOMContentLoaded', () => {
    categoriesManager = new CategoriesManager();
    categoriesManager.loadCategoryFromURL();
}); 