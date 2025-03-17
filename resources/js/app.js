import './bootstrap';
import '../css/app.css'; // Assurez-vous que ce fichier existe

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// Ton code JavaScript personnalisé
console.log('app.js chargé avec succès !');

window.SLOTS_URL = '/slots/__date__';
window.STORE_RESERVATION_URL = '/reservations';
window.STRIPE_KEY = 'votre_clé_stripe_publique';