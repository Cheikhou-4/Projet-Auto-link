// Gestionnaire de partage social et QR codes
class ShareManager {
    constructor() {
        this.init();
    }

    // Initialiser
    init() {
        this.createShareButtons();
        this.loadQRCodeLibrary();
    }

    // Charger la bibliothèque QR Code
    loadQRCodeLibrary() {
        if (!window.QRCode) {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
            script.onload = () => {
                console.log('QR Code library loaded');
            };
            document.head.appendChild(script);
        }
    }

    // Créer les boutons de partage
    createShareButtons() {
        // Observer les changements dans le DOM pour les nouvelles cartes
        const observer = new MutationObserver(() => {
            this.addShareButtonsToCards();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Ajouter aux cartes existantes
        this.addShareButtonsToCards();
    }

    // Ajouter les boutons de partage aux cartes
    addShareButtonsToCards() {
        document.querySelectorAll('.card').forEach(card => {
            const imageContainer = card.querySelector('.image-container');
            if (imageContainer && !card.querySelector('.share-btn')) {
                const shareBtn = document.createElement('button');
                shareBtn.className = 'share-btn btn btn-light btn-sm position-absolute';
                shareBtn.style.cssText = `
                    bottom: 12px;
                    right: 134px;
                    z-index: 10;
                    background: rgba(255,255,255,0.9);
                    border: none;
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    cursor: pointer;
                `;
                shareBtn.innerHTML = '<i class="fas fa-share-alt"></i>';
                shareBtn.title = 'Partager';
                
                shareBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.showShareModal(card);
                });
                
                imageContainer.appendChild(shareBtn);
            }
        });
    }

    // Afficher la modal de partage
    showShareModal(card) {
        const vehicleData = this.getVehicleData(card);
        const shareUrl = this.generateShareUrl(vehicleData);
        const shareText = this.generateShareText(vehicleData);
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'shareModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-share-alt me-2"></i>
                            Partager ${vehicleData.marque} ${vehicleData.modele}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Partager sur les réseaux sociaux</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" onclick="shareManager.shareOnFacebook('${shareUrl}', '${shareText}')">
                                        <i class="fab fa-facebook me-2"></i>Facebook
                                    </button>
                                    <button class="btn btn-info" onclick="shareManager.shareOnTwitter('${shareUrl}', '${shareText}')">
                                        <i class="fab fa-twitter me-2"></i>Twitter
                                    </button>
                                    <button class="btn btn-success" onclick="shareManager.shareOnWhatsApp('${shareUrl}', '${shareText}')">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </button>
                                    <button class="btn btn-secondary" onclick="shareManager.shareOnTelegram('${shareUrl}', '${shareText}')">
                                        <i class="fab fa-telegram me-2"></i>Telegram
                                    </button>
                                </div>
                                
                                <hr>
                                
                                <h6>Lien direct</h6>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="${shareUrl}" readonly id="shareUrl">
                                    <button class="btn btn-outline-primary" onclick="shareManager.copyToClipboard('${shareUrl}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>QR Code</h6>
                                <div class="text-center">
                                    <div id="qrCode" style="display: inline-block; padding: 20px; background: white; border-radius: 8px;"></div>
                                    <br><br>
                                    <button class="btn btn-outline-primary" onclick="shareManager.downloadQRCode()">
                                        <i class="fas fa-download me-2"></i>Télécharger QR Code
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        // Générer le QR code
        this.generateQRCode(shareUrl);
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Récupérer les données de la voiture
    getVehicleData(card) {
        const title = card.querySelector('.card-title')?.textContent || '';
        const price = card.querySelector('.card-text')?.textContent.match(/[\d\s]+FCFA/)?.[0] || '';
        const year = card.querySelector('.card-text')?.textContent.match(/Année\s*:\s*(\d+)/)?.[1] || '';
        const ville = card.querySelector('.card-text')?.textContent.match(/📍\s*([^•]+)/)?.[1] || '';
        const vehicleId = card.querySelector('.favorite-btn')?.dataset.vehicleId || '';
        
        return {
            id: vehicleId,
            marque: title.split(' ')[0] || '',
            modele: title.split(' ').slice(1).join(' ') || '',
            prix: price,
            annee: year,
            ville: ville
        };
    }

    // Générer l'URL de partage
    generateShareUrl(vehicleData) {
        const baseUrl = window.location.origin + window.location.pathname.replace('/index.php', '');
        return `${baseUrl}/voiture.php?id=${vehicleData.id}`;
    }

    // Générer le texte de partage
    generateShareText(vehicleData) {
        return `Découvrez cette ${vehicleData.marque} ${vehicleData.modele} ${vehicleData.annee} à ${vehicleData.prix} sur AutoLink Sénégal ! 🚗`;
    }

    // Partager sur Facebook
    shareOnFacebook(url, text) {
        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(text)}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }

    // Partager sur Twitter
    shareOnTwitter(url, text) {
        const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }

    // Partager sur WhatsApp
    shareOnWhatsApp(url, text) {
        const shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
        window.open(shareUrl, '_blank');
    }

    // Partager sur Telegram
    shareOnTelegram(url, text) {
        const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }

    // Copier dans le presse-papiers
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showNotification('Lien copié dans le presse-papiers !', 'success');
        }).catch(() => {
            // Fallback pour les navigateurs plus anciens
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showNotification('Lien copié dans le presse-papiers !', 'success');
        });
    }

    // Générer le QR code
    generateQRCode(url) {
        const qrContainer = document.getElementById('qrCode');
        if (qrContainer && window.QRCode) {
            qrContainer.innerHTML = '';
            QRCode.toCanvas(qrContainer, url, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, (error) => {
                if (error) {
                    qrContainer.innerHTML = '<p class="text-muted">Erreur lors de la génération du QR code</p>';
                }
            });
        }
    }

    // Télécharger le QR code
    downloadQRCode() {
        const canvas = document.querySelector('#qrCode canvas');
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'qr-code-voiture.png';
            link.href = canvas.toDataURL();
            link.click();
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
        }, 3000);
    }
}

// Initialiser le gestionnaire de partage
let shareManager;
document.addEventListener('DOMContentLoaded', () => {
    shareManager = new ShareManager();
}); 