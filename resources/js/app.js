import './bootstrap';
import '../css/app.css'; // Assurez-vous que ce fichier existe


import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import Font Awesome
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { 
    faTachometerAlt, 
    faUsers, 
    faUserCheck, 
    faChartPie, 
    faBoxOpen, 
    faCalendarCheck, 
    faEnvelope, 
    faPhone, 
    faMapMarkerAlt, 
    faArrowLeft, 
    faSearch, 
    faBan, 
    faCheckCircle, 
    faEye, 
    faSignOutAlt, 
    faUserCog
} from '@fortawesome/free-solid-svg-icons';

// Ajouter les icônes à la bibliothèque
library.add(
    faTachometerAlt, 
    faUsers, 
    faUserCheck, 
    faChartPie, 
    faBoxOpen, 
    faCalendarCheck, 
    faEnvelope, 
    faPhone, 
    faMapMarkerAlt, 
    faArrowLeft, 
    faSearch, 
    faBan, 
    faCheckCircle, 
    faEye, 
    faSignOutAlt, 
    faUserCog
);

// Remplacer les éléments i par des SVG Font Awesome
dom.watch();




