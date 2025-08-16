// Gestionnaire de galerie photos améliorée
class GalleryManager {
    constructor() {
        this.currentImageIndex = 0;
        this.images = [];
        this.isOpen = false;
        this.init();
    }

    // Initialiser
    init() {
        this.createGalleryModal();
        this.addGalleryButtons();
    }

    // Créer la modal de galerie
    createGalleryModal() {
        const modal = document.createElement('div');
        modal.className = 'gallery-modal';
        modal.id = 'galleryModal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        `;
        
        modal.innerHTML = `
            <div class="gallery-container" style="position: relative; max-width: 90%; max-height: 90%;">
                <button class="gallery-close" style="position: absolute; top: -40px; right: 0; background: none; border: none; color: white; font-size: 2rem; cursor: pointer; z-index: 1000;">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="gallery-main" style="position: relative; text-align: center;">
                    <img id="galleryImage" src="" alt="" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px;">
                    
                    <div class="gallery-nav" style="position: absolute; top: 50%; transform: translateY(-50%); width: 100%; display: flex; justify-content: space-between; padding: 0 20px;">
                        <button class="gallery-prev" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 15px; border-radius: 50%; cursor: pointer; font-size: 1.5rem;">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="gallery-next" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 15px; border-radius: 50%; cursor: pointer; font-size: 1.5rem;">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="gallery-thumbnails" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                </div>
                
                <div class="gallery-info" style="position: absolute; bottom: -40px; left: 0; right: 0; text-align: center; color: white;">
                    <span id="galleryCounter">1 / 1</span>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Événements
        modal.querySelector('.gallery-close').addEventListener('click', () => this.closeGallery());
        modal.querySelector('.gallery-prev').addEventListener('click', () => this.prevImage());
        modal.querySelector('.gallery-next').addEventListener('click', () => this.nextImage());
        
        // Fermer avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeGallery();
            }
        });
        
        // Navigation avec flèches
        document.addEventListener('keydown', (e) => {
            if (!this.isOpen) return;
            
            if (e.key === 'ArrowLeft') {
                this.prevImage();
            } else if (e.key === 'ArrowRight') {
                this.nextImage();
            }
        });
    }

    // Ajouter les boutons de galerie sur les images
    addGalleryButtons() {
        // Observer les changements dans le DOM pour les nouvelles cartes
        const observer = new MutationObserver(() => {
            this.addGalleryButtonsToCards();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Ajouter aux cartes existantes
        this.addGalleryButtonsToCards();
    }

    // Ajouter les boutons de galerie aux cartes
    addGalleryButtonsToCards() {
        document.querySelectorAll('.card').forEach(card => {
            const imageContainer = card.querySelector('.image-container');
            if (imageContainer && !card.querySelector('.gallery-btn')) {
                const galleryBtn = document.createElement('button');
                galleryBtn.className = 'gallery-btn btn btn-light btn-sm position-absolute';
                galleryBtn.style.cssText = `
                    bottom: 12px;
                    right: 92px;
                    z-index: 10;
                    background: rgba(255,255,255,0.9);
                    border: none;
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    cursor: pointer;
                `;
                galleryBtn.innerHTML = '<i class="fas fa-images"></i>';
                galleryBtn.title = 'Voir la galerie';
                
                galleryBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.openGallery(card);
                });
                
                imageContainer.appendChild(galleryBtn);
            }
        });
    }

    // Ouvrir la galerie
    openGallery(card) {
        this.images = [];
        this.currentImageIndex = 0;
        
        // Récupérer les données de la voiture
        const vehicleId = card.querySelector('.favorite-btn')?.dataset.vehicleId;
        const vehicleData = this.getVehicleData(card);
        
        // Ajouter l'image principale
        const mainImage = card.querySelector('img');
        if (mainImage && mainImage.src) {
            this.images.push({
                src: mainImage.src,
                alt: mainImage.alt,
                title: `${vehicleData.marque} ${vehicleData.modele}`
            });
        }
        
        // Ajouter des images supplémentaires (simulation)
        this.addAdditionalImages(vehicleData);
        
        if (this.images.length > 0) {
            this.isOpen = true;
            this.showImage(0);
            this.updateThumbnails();
            this.updateCounter();
            
            const modal = document.getElementById('galleryModal');
            modal.style.display = 'flex';
            
            // Animation d'entrée
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.style.transition = 'opacity 0.3s ease';
            }, 10);
        }
    }

    // Récupérer les données de la voiture depuis la carte
    getVehicleData(card) {
        const title = card.querySelector('.card-title')?.textContent || '';
        const price = card.querySelector('.card-text')?.textContent.match(/[\d\s]+FCFA/)?.[0] || '';
        const year = card.querySelector('.card-text')?.textContent.match(/Année\s*:\s*(\d+)/)?.[1] || '';
        const ville = card.querySelector('.card-text')?.textContent.match(/📍\s*([^•]+)/)?.[1] || '';
        
        return {
            marque: title.split(' ')[0] || '',
            modele: title.split(' ').slice(1).join(' ') || '',
            prix: price,
            annee: year,
            ville: ville
        };
    }

    // Ajouter des images supplémentaires (simulation)
    addAdditionalImages(vehicleData) {
        // Dans un vrai projet, ces images viendraient de la base de données
        const additionalImages = [
            {
                src: `https://via.placeholder.com/800x600/ff9800/ffffff?text=${encodeURIComponent(vehicleData.marque + ' ' + vehicleData.modele + ' - Vue 2')}`,
                alt: `${vehicleData.marque} ${vehicleData.modele} - Vue 2`,
                title: `${vehicleData.marque} ${vehicleData.modele} - Vue arrière`
            },
            {
                src: `https://via.placeholder.com/800x600/1976d2/ffffff?text=${encodeURIComponent(vehicleData.marque + ' ' + vehicleData.modele + ' - Vue 3')}`,
                alt: `${vehicleData.marque} ${vehicleData.modele} - Vue 3`,
                title: `${vehicleData.marque} ${vehicleData.modele} - Vue intérieure`
            },
            {
                src: `https://via.placeholder.com/800x600/43a047/ffffff?text=${encodeURIComponent(vehicleData.marque + ' ' + vehicleData.modele + ' - Vue 4')}`,
                alt: `${vehicleData.marque} ${vehicleData.modele} - Vue 4`,
                title: `${vehicleData.marque} ${vehicleData.modele} - Vue moteur`
            }
        ];
        
        this.images.push(...additionalImages);
    }

    // Fermer la galerie
    closeGallery() {
        this.isOpen = false;
        const modal = document.getElementById('galleryModal');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    // Afficher une image
    showImage(index) {
        if (index < 0 || index >= this.images.length) return;
        
        this.currentImageIndex = index;
        const image = document.getElementById('galleryImage');
        const imageData = this.images[index];
        
        image.src = imageData.src;
        image.alt = imageData.alt;
        image.title = imageData.title;
        
        this.updateCounter();
        this.updateThumbnailSelection();
    }

    // Image précédente
    prevImage() {
        const newIndex = this.currentImageIndex - 1;
        if (newIndex >= 0) {
            this.showImage(newIndex);
        } else {
            this.showImage(this.images.length - 1);
        }
    }

    // Image suivante
    nextImage() {
        const newIndex = this.currentImageIndex + 1;
        if (newIndex < this.images.length) {
            this.showImage(newIndex);
        } else {
            this.showImage(0);
        }
    }

    // Mettre à jour les miniatures
    updateThumbnails() {
        const container = document.querySelector('.gallery-thumbnails');
        container.innerHTML = '';
        
        this.images.forEach((image, index) => {
            const thumb = document.createElement('img');
            thumb.src = image.src;
            thumb.alt = image.alt;
            thumb.style.cssText = `
                width: 80px;
                height: 60px;
                object-fit: cover;
                border-radius: 4px;
                cursor: pointer;
                border: 2px solid transparent;
                transition: border-color 0.2s ease;
            `;
            
            if (index === this.currentImageIndex) {
                thumb.style.borderColor = '#ff9800';
            }
            
            thumb.addEventListener('click', () => this.showImage(index));
            container.appendChild(thumb);
        });
    }

    // Mettre à jour la sélection des miniatures
    updateThumbnailSelection() {
        const thumbs = document.querySelectorAll('.gallery-thumbnails img');
        thumbs.forEach((thumb, index) => {
            if (index === this.currentImageIndex) {
                thumb.style.borderColor = '#ff9800';
            } else {
                thumb.style.borderColor = 'transparent';
            }
        });
    }

    // Mettre à jour le compteur
    updateCounter() {
        const counter = document.getElementById('galleryCounter');
        counter.textContent = `${this.currentImageIndex + 1} / ${this.images.length}`;
    }
}

// Initialiser le gestionnaire de galerie
let galleryManager;
document.addEventListener('DOMContentLoaded', () => {
    galleryManager = new GalleryManager();
}); 