// Gestionnaire de filtres avancés
class AdvancedFilters {
    constructor() {
        this.initSliders();
        this.initFilterForm();
    }

    // Initialiser les sliders
    initSliders() {
        // Slider pour les années
        const yearSlider = document.getElementById('yearRange');
        if (yearSlider) {
            noUiSlider.create(yearSlider, {
                start: [2010, 2024],
                connect: true,
                range: {
                    'min': 1990,
                    'max': 2024
                },
                format: {
                    to: function (value) {
                        return Math.round(value);
                    },
                    from: function (value) {
                        return Math.round(value);
                    }
                }
            });

            // Mettre à jour les labels
            yearSlider.noUiSlider.on('update', function (values, handle) {
                document.getElementById('yearMin').textContent = values[0];
                document.getElementById('yearMax').textContent = values[1];
            });
        }

        // Slider pour les prix
        const priceSlider = document.getElementById('priceRange');
        if (priceSlider) {
            noUiSlider.create(priceSlider, {
                start: [1000000, 50000000],
                connect: true,
                range: {
                    'min': 0,
                    'max': 100000000
                },
                format: {
                    to: function (value) {
                        return Math.round(value);
                    },
                    from: function (value) {
                        return Math.round(value);
                    }
                }
            });

            // Mettre à jour les labels
            priceSlider.noUiSlider.on('update', function (values, handle) {
                document.getElementById('priceMin').textContent = this.formatPrice(values[0]);
                document.getElementById('priceMax').textContent = this.formatPrice(values[1]);
            }.bind(this));
        }
    }

    // Formater le prix
    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
    }

    // Initialiser le formulaire de filtres
    initFilterForm() {
        const form = document.getElementById('advancedFiltersForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }

        // Bouton pour réinitialiser les filtres
        const resetBtn = document.getElementById('resetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetFilters();
            });
        }
    }

    // Appliquer les filtres
    applyFilters() {
        const formData = new FormData(document.getElementById('advancedFiltersForm'));
        const params = new URLSearchParams();

        // Ajouter les paramètres de base
        ['marque', 'type', 'ville', 'search'].forEach(key => {
            const value = formData.get(key);
            if (value) params.append(key, value);
        });

        // Ajouter les plages d'années
        const yearSlider = document.getElementById('yearRange');
        if (yearSlider && yearSlider.noUiSlider) {
            const yearValues = yearSlider.noUiSlider.get();
            params.append('year_min', yearValues[0]);
            params.append('year_max', yearValues[1]);
        }

        // Ajouter les plages de prix
        const priceSlider = document.getElementById('priceRange');
        if (priceSlider && priceSlider.noUiSlider) {
            const priceValues = priceSlider.noUiSlider.get();
            params.append('price_min', priceValues[0]);
            params.append('price_max', priceValues[1]);
        }

        // Rediriger avec les nouveaux filtres
        const currentUrl = window.location.pathname;
        window.location.href = currentUrl + '?' + params.toString();
    }

    // Réinitialiser les filtres
    resetFilters() {
        // Réinitialiser les sliders
        const yearSlider = document.getElementById('yearRange');
        if (yearSlider && yearSlider.noUiSlider) {
            yearSlider.noUiSlider.set([2010, 2024]);
        }

        const priceSlider = document.getElementById('priceRange');
        if (priceSlider && priceSlider.noUiSlider) {
            priceSlider.noUiSlider.set([1000000, 50000000]);
        }

        // Réinitialiser le formulaire
        document.getElementById('advancedFiltersForm').reset();

        // Rediriger vers la page sans filtres
        const currentUrl = window.location.pathname;
        window.location.href = currentUrl;
    }

    // Mettre à jour les filtres depuis l'URL
    updateFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Mettre à jour les sliders d'années
        const yearMin = urlParams.get('year_min');
        const yearMax = urlParams.get('year_max');
        if (yearMin && yearMax) {
            const yearSlider = document.getElementById('yearRange');
            if (yearSlider && yearSlider.noUiSlider) {
                yearSlider.noUiSlider.set([parseInt(yearMin), parseInt(yearMax)]);
            }
        }

        // Mettre à jour les sliders de prix
        const priceMin = urlParams.get('price_min');
        const priceMax = urlParams.get('price_max');
        if (priceMin && priceMax) {
            const priceSlider = document.getElementById('priceRange');
            if (priceSlider && priceSlider.noUiSlider) {
                priceSlider.noUiSlider.set([parseInt(priceMin), parseInt(priceMax)]);
            }
        }
    }
}

// Initialiser les filtres avancés
document.addEventListener('DOMContentLoaded', () => {
    // Charger noUiSlider si disponible
    if (typeof noUiSlider !== 'undefined') {
        new AdvancedFilters();
    } else {
        // Charger noUiSlider dynamiquement
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.6.1/nouislider.min.js';
        script.onload = () => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.6.1/nouislider.min.css';
            document.head.appendChild(link);
            
            setTimeout(() => {
                new AdvancedFilters();
            }, 100);
        };
        document.head.appendChild(script);
    }
}); 