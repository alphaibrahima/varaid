// Dashboard Association JavaScript

// Fonction pour filtrer les tableaux
function setupTableFilter(inputId, tableId, columns) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('keyup', function() {
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            let showRow = false;
            
            if (columns) {
                // Recherche dans des colonnes spécifiques
                for (let colIndex of columns) {
                    const cell = rows[i].getElementsByTagName('td')[colIndex];
                    if (cell) {
                        const cellText = cell.textContent || cell.innerText;
                        if (cellText.toLowerCase().indexOf(filter) > -1) {
                            showRow = true;
                            break;
                        }
                    }
                }
            } else {
                // Recherche dans toutes les colonnes
                const cells = rows[i].getElementsByTagName('td');
                for (let cell of cells) {
                    const cellText = cell.textContent || cell.innerText;
                    if (cellText.toLowerCase().indexOf(filter) > -1) {
                        showRow = true;
                        break;
                    }
                }
            }
            
            rows[i].style.display = showRow ? '' : 'none';
        }
    });
}

// Fonction pour afficher un message d'alerte
function showAlert(message, type = 'success', duration = 3000) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `fixed top-4 right-4 p-4 rounded-lg shadow-md z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    } text-white`;
    
    alertContainer.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertContainer);
    
    setTimeout(() => {
        alertContainer.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => {
            document.body.removeChild(alertContainer);
        }, 300);
    }, duration);
}

// Initialisation lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les filtres de recherche
    setupTableFilter('search', 'buyers-table');
    setupTableFilter('search', 'reservations-table');
    
    // Ajouter des écouteurs d'événements pour les actions (comme les boutons d'activation/désactivation)
    document.querySelectorAll('[data-action="toggle-status"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('form');
            const userName = this.getAttribute('data-user-name');
            const action = this.getAttribute('data-action-type') === 'activate' ? 'activer' : 'désactiver';
            
            if (confirm(`Êtes-vous sûr de vouloir ${action} le compte de ${userName} ?`)) {
                form.submit();
            }
        });
    });
});