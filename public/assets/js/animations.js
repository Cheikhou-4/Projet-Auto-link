// Gestionnaire d'animations et transitions
class AnimationManager {
    constructor() {
        this.init();
    }

    // Initialiser
    init() {
        this.addPageTransitions();
        this.addScrollAnimations();
        this.addLoadingAnimations();
        this.addHoverEffects();
        this.addSmoothScrolling();
    }

    // Ajouter les transitions de page
    addPageTransitions() {
        // Animation d'entrée pour les cartes
        this.animateCardsOnLoad();
        
        // Animation de sortie avant navigation
        // document.addEventListener('click', (e) => {
        //     // N'intercepte pas les clics à l'intérieur d'un formulaire
        //     if (e.target.closest('form')) return;
        //     // N'intercepte pas les boutons ou inputs de type submit ou leurs enfants
        //     if (
        //         (e.target.type && e.target.type === 'submit') ||
        //         (e.target.closest('button') && e.target.closest('button').type === 'submit') ||
        //         (e.target.closest('input') && e.target.closest('input').type === 'submit')
        //     ) return;
        //     const link = e.target.closest('a');
        //     if (link && link.href && !link.href.includes('#') && !link.href.includes('javascript:') && !link.target) {
        //         e.preventDefault();
        //         this.animatePageExit(() => {
        //             window.location.href = link.href;
        //         });
        //     }
        // });
    }

    // Animer les cartes au chargement
    animateCardsOnLoad() {
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    // Animation de sortie de page
    animatePageExit(callback) {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ff9800;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(overlay);
        
        setTimeout(() => {
            overlay.style.opacity = '1';
            setTimeout(callback, 300);
        }, 10);
    }

    // Ajouter les animations au scroll
    addScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observer les éléments à animer
        document.querySelectorAll('.card, .sidebar-filtres, .alert').forEach(el => {
            observer.observe(el);
        });
    }

    // Ajouter les animations de chargement
    addLoadingAnimations() {
        // Animation de chargement pour les formulaires
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (btn) {
                    const originalText = btn.innerHTML || btn.value;
                    if (btn.tagName.toLowerCase() === 'button') {
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Chargement...';
                    } else {
                        btn.value = 'Chargement...';
                    }
                    // Ne pas désactiver le bouton ici !
                    setTimeout(() => {
                        if (btn.tagName.toLowerCase() === 'button') {
                            btn.innerHTML = originalText;
                        } else {
                            btn.value = originalText;
                        }
                    }, 2000);
                }
            });
        });
    }

    // Ajouter les effets de survol simplifiés
    addHoverEffects() {
        // Effet de survol simple sur les cartes
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    }

    // Ajouter le défilement fluide
    addSmoothScrolling() {
        // Défilement fluide vers le haut
        const scrollToTopBtn = document.createElement('button');
        scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        scrollToTopBtn.className = 'scroll-to-top';
        scrollToTopBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #ff9800;
            color: white;
            border: none;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        document.body.appendChild(scrollToTopBtn);
        
        // Afficher/masquer le bouton selon le scroll
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.style.opacity = '1';
                scrollToTopBtn.style.visibility = 'visible';
            } else {
                scrollToTopBtn.style.opacity = '0';
                scrollToTopBtn.style.visibility = 'hidden';
            }
        });
        
        // Défilement vers le haut
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Défilement fluide pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Animer l'apparition d'un élément
    animateElement(element, animation = 'fadeInUp') {
        const animations = {
            fadeInUp: {
                from: { opacity: 0, transform: 'translateY(30px)' },
                to: { opacity: 1, transform: 'translateY(0)' }
            },
            fadeInLeft: {
                from: { opacity: 0, transform: 'translateX(-30px)' },
                to: { opacity: 1, transform: 'translateX(0)' }
            },
            fadeInRight: {
                from: { opacity: 0, transform: 'translateX(30px)' },
                to: { opacity: 1, transform: 'translateX(0)' }
            },
            scaleIn: {
                from: { opacity: 0, transform: 'scale(0.8)' },
                to: { opacity: 1, transform: 'scale(1)' }
            }
        };

        const anim = animations[animation];
        if (anim) {
            Object.assign(element.style, anim.from);
            element.style.transition = 'all 0.6s ease';
            
            requestAnimationFrame(() => {
                Object.assign(element.style, anim.to);
            });
        }
    }

    // Animer une notification
    animateNotification(notification) {
        notification.style.transform = 'translateX(100%)';
        notification.style.transition = 'transform 0.3s ease';
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
}

// Ajouter les styles CSS pour les animations
const animationStyles = document.createElement('style');
animationStyles.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease forwards;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .scroll-to-top:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .btn {
        transition: all 0.2s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .btn:active {
        transform: translateY(0);
    }
`;
document.head.appendChild(animationStyles);

// Initialiser le gestionnaire d'animations
let animationManager;
document.addEventListener('DOMContentLoaded', () => {
    animationManager = new AnimationManager();
}); 