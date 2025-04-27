// assets/js/app.js
// Importer les styles
import '../styles/app.scss';

// Importer les scripts originaux
import './main.js';
import './form-validation.js';

// Initialisation de AOS
document.addEventListener('DOMContentLoaded', () => {
    // Détection de l'environnement
    const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    // Configuration des animations
    try {
        window.AOS.init({
            duration: isReducedMotion ? 300 : 800,
            once: true,
            disable: 'mobile',
            offset: isReducedMotion ? 50 : 100
        });
    } catch (e) {
        console.warn('AOS not loaded yet');
    }
    
    // Comportement du popup ebook
    initEbookPopup();
    initCookieConsent();
});

// Fonction d'initialisation du popup ebook
function initEbookPopup() {
    const popup = document.getElementById('ebook-popup');
    const openButtons = document.querySelectorAll('.open-ebook-popup');
    const closeButton = document.getElementById('close-ebook-popup');
    
    if (!popup || !openButtons.length || !closeButton) return;
    
    // Ouvrir le popup
    openButtons.forEach(button => {
        button.addEventListener('click', () => {
            popup.classList.remove('hidden');
        });
    });
    
    // Fermer le popup
    closeButton.addEventListener('click', () => {
        popup.classList.add('hidden');
    });
    
    // Fermer en cliquant à l'extérieur
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            popup.classList.add('hidden');
        }
    });
}

function initCookieConsent() {
    // Implémentation similaire pour le consentement des cookies
    // Code basé sur includes/cookie_consent.php
}