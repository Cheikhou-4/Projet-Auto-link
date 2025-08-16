// Gestionnaire principal style CoinAfrique
class CoinAfriqueManager {
    constructor() {
        this.currentCategory = 'all';
        this.currentView = 'grid';
        this.filters = {
            ville: '',
            marque: '',
            type: '',
            priceMin: 0,
            priceMax: 100000000,
            yearMin: 1990,
            yearMax: 2024
        };
        this.init();
    }

    init() {
        this.initCategories();
        this.initFilters();
        this.initSearch();
        this.initViewControls();
    }

    // Initialiser les catégories
    initCategories() {
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                const category = card.dataset.category;
                this.selectCategory(category);
            });
        });
    }

    // Sélectionner une catégorie
    selectCategory(category) {
        this.currentCategory = category;
        
        // Mettre à jour l'état actif
        document.querySelectorAll('.category-card').forEach(card => {
            card.classList.remove('active');
        });
        const selected = document.querySelector(`[data-category="${category}"]`);
        if (selected) {
            selected.classList.add('active');
        }
        // Filtrer les véhicules
        this.filterVehicles();
    }

    // Initialiser les filtres
    initFilters() {
        // Filtres de sélection
        const villeFilter = document.getElementById('villeFilter');
        if (villeFilter) {
            villeFilter.addEventListener('change', (e) => {
                this.filters.ville = e.target.value;
                this.filterVehicles();
            });
        }
        const marqueFilter = document.getElementById('marqueFilter');
        if (marqueFilter) {
            marqueFilter.addEventListener('change', (e) => {
                this.filters.marque = e.target.value;
                this.filterVehicles();
            });
        }
        // Filtres radio
        document.querySelectorAll('input[name="typeFilter"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.filters.type = e.target.value;
                this.filterVehicles();
            });
        });
        // Filtres de prix
        const priceMin = document.getElementById('priceMin');
        const priceMax = document.getElementById('priceMax');
        const priceMinLabel = document.getElementById('priceMinLabel');
        const priceMaxLabel = document.getElementById('priceMaxLabel');
        if (priceMin && priceMinLabel) {
            priceMin.addEventListener('input', (e) => {
                this.filters.priceMin = parseInt(e.target.value);
                priceMinLabel.textContent = this.formatPrice(this.filters.priceMin) + ' FCFA';
                this.filterVehicles();
            });
        }
        if (priceMax && priceMaxLabel) {
            priceMax.addEventListener('input', (e) => {
                this.filters.priceMax = parseInt(e.target.value);
                priceMaxLabel.textContent = this.formatPrice(this.filters.priceMax) + ' FCFA';
                this.filterVehicles();
            });
        }
        // Filtres d'année
        const yearMin = document.getElementById('yearMin');
        const yearMax = document.getElementById('yearMax');
        const yearMinLabel = document.getElementById('yearMinLabel');
        const yearMaxLabel = document.getElementById('yearMaxLabel');
        if (yearMin && yearMinLabel) {
            yearMin.addEventListener('input', (e) => {
                this.filters.yearMin = parseInt(e.target.value);
                yearMinLabel.textContent = this.filters.yearMin;
                this.filterVehicles();
            });
        }
        if (yearMax && yearMaxLabel) {
            yearMax.addEventListener('input', (e) => {
                this.filters.yearMax = parseInt(e.target.value);
                yearMaxLabel.textContent = this.filters.yearMax;
                this.filterVehicles();
            });
        }
        // Bouton réinitialiser
        const clearFilters = document.getElementById('clearFilters');
        if (clearFilters) {
            clearFilters.addEventListener('click', () => {
                this.resetFilters();
            });
        }
    }

    // Initialiser la recherche
    initSearch() {
        const searchInput = document.getElementById('mainSearch');
        const searchBtn = document.getElementById('searchBtn');

        if (searchInput) {
            // On écoute Enter, blur et change
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = searchInput.value.trim();
                    if (query) {
                        window.location.href = '/Projet%20Automobile/public/index.php?search=' + encodeURIComponent(query);
                    }
                }
            });
            searchInput.addEventListener('change', () => {
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = '/Projet%20Automobile/public/index.php?search=' + encodeURIComponent(query);
                }
            });
            searchInput.addEventListener('blur', () => {
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = '/Projet%20Automobile/public/index.php?search=' + encodeURIComponent(query);
                }
            });
        }
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = '/Projet%20Automobile/public/index.php?search=' + encodeURIComponent(query);
                }
            });
        }
    }

    // Effectuer une recherche
    performSearch(query) {
        // Sinon, fallback sur la grille principale
        const cards = document.querySelectorAll('.vehicle-card');
        if (!query.trim()) {
            cards.forEach(card => card.style.display = 'block');
            return;
        }
        const q = query.toLowerCase();
        cards.forEach(card => {
            const title = card.querySelector('.vehicle-title')?.textContent.toLowerCase() || '';
            const ville = card.querySelector('.detail-item span')?.textContent.toLowerCase() || '';
            const marque = card.dataset.marque?.toLowerCase() || '';
            const description = card.querySelector('.card-text, .vehicle-description')?.textContent.toLowerCase() || '';
            const categorie = card.dataset.category?.toLowerCase() || '';
            const matches = title.includes(q) || ville.includes(q) || marque.includes(q) || description.includes(q) || categorie.includes(q);
            card.style.display = matches ? 'block' : 'none';
        });
    }

    // Afficher/masquer le message "Aucun résultat" dans le hero
    toggleHeroNoResult(show) {
        let msg = document.getElementById('heroNoResult');
        if (show) {
            if (!msg) {
                msg = document.createElement('div');
                msg.id = 'heroNoResult';
                msg.className = 'alert alert-warning text-center mt-3';
                msg.innerHTML = '<i class="fas fa-info-circle me-2"></i>Aucun résultat trouvé dans les vedettes.';
                const container = document.querySelector('.featured-carousel-wrapper');
                if (container) container.appendChild(msg);
            }
        } else {
            if (msg) msg.remove();
        }
    }

    // Initialiser les contrôles de vue
    initViewControls() {
        const sortSelect = document.getElementById('sortSelect');
        const viewButtons = document.querySelectorAll('[data-view]');

        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortVehicles(e.target.value);
            });
        }

        // Filtrer explicitement les boutons qui ont data-view
        if (viewButtons.length > 0) {
            Array.from(viewButtons).forEach(btn => {
                if (!btn || !btn.dataset || !btn.dataset.view) return;
                btn.addEventListener('click', (e) => {
                    const view = btn.dataset.view;
                    this.changeView(view);
                });
            });
        }
    }

    // Changer la vue
    changeView(view) {
        this.currentView = view;
        const grid = document.getElementById('vehiclesGrid');
        
        // Mettre à jour les boutons
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-view="${view}"]`).classList.add('active');
        
        // Changer la classe CSS
        grid.className = `vehicles-grid view-${view}`;
    }

    // Trier les véhicules
    sortVehicles(sortType) {
        const grid = document.getElementById('vehiclesGrid');
        const cards = Array.from(grid.querySelectorAll('.vehicle-card'));
        
        cards.sort((a, b) => {
            switch (sortType) {
                case 'price_asc':
                    return parseInt(a.dataset.prix) - parseInt(b.dataset.prix);
                case 'price_desc':
                    return parseInt(b.dataset.prix) - parseInt(a.dataset.prix);
                case 'year_desc':
                    return parseInt(b.dataset.annee) - parseInt(a.dataset.annee);
                default: // recent
                    return 0; // Garder l'ordre original
            }
        });
        
        // Réorganiser les cartes
        cards.forEach(card => grid.appendChild(card));
    }

    // Filtrer les véhicules
    filterVehicles() {
        const cards = document.querySelectorAll('.vehicle-card');
        cards.forEach(card => {
            const matchesCategory = this.currentCategory === 'all' || 
                (card.dataset.category && card.dataset.category === this.currentCategory);
            const matchesVille = !this.filters.ville || 
                (card.dataset.ville && card.dataset.ville.toLowerCase().includes(this.filters.ville.toLowerCase()));
            const matchesMarque = !this.filters.marque || 
                (card.dataset.marque && card.dataset.marque.toLowerCase().includes(this.filters.marque.toLowerCase()));
            const matchesType = !this.filters.type || 
                (card.dataset.type && card.dataset.type === this.filters.type);
            const prix = card.dataset.prix ? parseInt(card.dataset.prix) : 0;
            const matchesPrice = prix >= this.filters.priceMin && prix <= this.filters.priceMax;
            const annee = card.dataset.annee ? parseInt(card.dataset.annee) : 0;
            const matchesYear = annee >= this.filters.yearMin && annee <= this.filters.yearMax;
            const isVisible = matchesCategory && matchesVille && matchesMarque && 
                matchesType && matchesPrice && matchesYear;
            card.style.display = isVisible ? 'block' : 'none';
        });
    }

    // Réinitialiser les filtres
    resetFilters() {
        // Réinitialiser les valeurs
        document.getElementById('villeFilter').value = '';
        document.getElementById('marqueFilter').value = '';
        document.getElementById('typeAll').checked = true;
        document.getElementById('priceMin').value = 0;
        document.getElementById('priceMax').value = 100000000;
        document.getElementById('yearMin').value = 1990;
        document.getElementById('yearMax').value = 2024;
        
        // Mettre à jour les labels
        document.getElementById('priceMinLabel').textContent = '0 FCFA';
        document.getElementById('priceMaxLabel').textContent = '100 000 000 FCFA';
        document.getElementById('yearMinLabel').textContent = '1990';
        document.getElementById('yearMaxLabel').textContent = '2024';
        
        // Réinitialiser les filtres
        this.filters = {
            ville: '',
            marque: '',
            type: '',
            priceMin: 0,
            priceMax: 100000000,
            yearMin: 1990,
            yearMax: 2024
        };
        
        // Afficher tous les véhicules
        document.querySelectorAll('.vehicle-card').forEach(card => {
            card.style.display = 'block';
        });
    }

    // SUPPRIMÉ : la méthode initFavorites car il n'y a plus de favoris

    // Formater le prix
    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR').format(price);
    }}

let coinafriqueManager;
document.addEventListener('DOMContentLoaded', () => {
    coinafriqueManager = new CoinAfriqueManager();
});

// === SUPPRESSION DE TOUTE LA PARTIE FAVORIS ===
// (window.favoritesManager, updateFavoriteButtons, toggleFavorite, updateHeaderCounter, openFavoritesModal, et gestion des clics sur .btn-favorite)

// Défilement automatique du carrousel vedette horizontal (version finale sans bouton test)
function autoScrollFeaturedCarousel() {
    const wrapper = document.querySelector('.featured-carousel-wrapper');
    const carousel = document.querySelector('.featured-carousel');
    if (!carousel || !wrapper) { console.log('[Carrousel] Pas de wrapper ou carousel'); return; }

    // Forcer le style du wrapper pour le scroll horizontal
    wrapper.style.overflowX = 'auto';
    wrapper.style.whiteSpace = 'nowrap';
    wrapper.style.position = 'relative';
    wrapper.style.scrollBehavior = 'auto';

    let scrollStep = 0.5; // pixels per frame (plus lent)
    let interval = null;
    let isHovered = false;
    let isUserScrolling = false;

    // Attendre que toutes les images soient chargées, sinon démarrer direct
    const images = carousel.querySelectorAll('img');
    let loaded = 0;
    function reallyStart() {
        if (interval) return;
        interval = setInterval(() => {
            if (isHovered || isUserScrolling) return;
            // Si on est presque à la fin, revenir au début (effet infini)
            if (wrapper.scrollLeft + wrapper.offsetWidth >= wrapper.scrollWidth - 2) {
                wrapper.scrollTo({ left: 0, behavior: 'auto' });
            } else {
                wrapper.scrollLeft += scrollStep;
            }
        }, 16);
    }
    if (images.length === 0) reallyStart();
    images.forEach(img => {
        if (img.complete) {
            loaded++;
            if (loaded === images.length) reallyStart();
        } else {
            img.addEventListener('load', () => {
                loaded++;
                if (loaded === images.length) reallyStart();
            });
        }
    });

    wrapper.addEventListener('mouseenter', () => { isHovered = true; });
    wrapper.addEventListener('mouseleave', () => { isHovered = false; });
    wrapper.addEventListener('mousedown', () => { isUserScrolling = true; });
    wrapper.addEventListener('mouseup', () => { isUserScrolling = false; });
    wrapper.addEventListener('touchstart', () => { isUserScrolling = true; });
    wrapper.addEventListener('touchend', () => { isUserScrolling = false; });

    // Relancer le scroll sur resize
    window.addEventListener('resize', () => {
        if (interval) clearInterval(interval);
        interval = null;
        setTimeout(reallyStart, 500);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.featured-carousel')) {
        autoScrollFeaturedCarousel();
    }
}); 