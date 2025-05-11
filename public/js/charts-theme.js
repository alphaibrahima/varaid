// Configuration des graphiques pour s'adapter au mode sombre
document.addEventListener('DOMContentLoaded', function() {
    // Détection du mode sombre
    const isDarkMode = document.documentElement.classList.contains('dark');
    
    // Définition des couleurs en fonction du mode
    const textColor = isDarkMode ? '#e5e7eb' : '#374151';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    
    // Configuration globale des graphiques Chart.js
    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;
    
    // Mettre à jour les graphiques existants
    Chart.instances.forEach(chart => {
        // Mise à jour des options d'échelle pour tous les graphiques
        if (chart.config.options.scales) {
            Object.values(chart.config.options.scales).forEach(scale => {
                if (scale.grid) {
                    scale.grid.color = gridColor;
                }
                if (scale.ticks) {
                    scale.ticks.color = textColor;
                }
            });
        }
        
        // Mise à jour de la légende
        if (chart.config.options.plugins && chart.config.options.plugins.legend) {
            chart.config.options.plugins.legend.labels.color = textColor;
        }
        
        chart.update();
    });
    
    // Écouter les changements de mode (clair/sombre)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class' && mutation.target === document.documentElement) {
                const newDarkMode = document.documentElement.classList.contains('dark');
                if (newDarkMode !== isDarkMode) {
                    location.reload(); // Rechargement de la page pour actualiser les graphiques
                }
            }
        });
    });
    
    observer.observe(document.documentElement, { attributes: true });
});