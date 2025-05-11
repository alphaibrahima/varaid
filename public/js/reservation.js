document.addEventListener("DOMContentLoaded", function() {
    console.log(document.getElementById('confirmation-day'));
    
    // Initialiser les alertes en fonction de l'étape active
    const currentStep = document.querySelector('.step.active');
    if (currentStep) {
        const stepNumber = currentStep.id.split('-')[1];
        showAlertForStep(stepNumber);
    }
    
    // Initialiser les gestionnaires pour le modal des conditions
    const acceptTermsCheckbox = document.getElementById('acceptTerms');
    const confirmTermsButton = document.getElementById('confirmTerms');
    
    if (acceptTermsCheckbox && confirmTermsButton) {
        acceptTermsCheckbox.addEventListener('change', function() {
            confirmTermsButton.disabled = !this.checked;
        });
        
        confirmTermsButton.addEventListener('click', function() {
            // Fermer le modal des conditions
            const termsModal = bootstrap.Modal.getInstance(document.getElementById('termsModal'));
            if (termsModal) {
                termsModal.hide();
            }
            
            // Procéder à la réservation
            proceedWithReservation();
        });
    }
    
    // Initialiser la valeur skipSelection depuis localStorage au chargement
    const savedSkipSelection = localStorage.getItem('skipSelection');
    if (savedSkipSelection) {
        skipSelection = savedSkipSelection === 'true';
        
        // Mettre à jour la case à cocher si elle existe
        const skipSelectionCheckbox = document.getElementById('skipSelection');
        if (skipSelectionCheckbox) {
            skipSelectionCheckbox.checked = skipSelection;
        }
    }
    
    // Vérifier la limite de réservation
    checkReservationLimit();
});

window.onload = function() {
    console.log("JavaScript chargé après le chargement complet !");
};

// Variables pour stocker les sélections
let selectedDay = '';
let selectedTime = '';
let selectedSize = 'grand';
let selectedQuantity = 1;
let skipSelection = false;
let userReservationsCount = 0;
let remainingReservations = 4;
let isAssociation = false;

// Fonction pour afficher l'alerte pour l'étape courante
function showAlertForStep(step) {
    // Masquer toutes les alertes d'étape
    document.querySelectorAll('.step-alert').forEach(alert => {
        alert.style.display = 'none';
    });
    
    // Afficher l'alerte correspondant à l'étape
    const alertElement = document.getElementById(`alert-step-${step}`);
    if (alertElement) {
        alertElement.style.display = 'block';
    }
}

// Fonction pour changer d'étape
function goToStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById(`step-${step}`).classList.add('active');
    
    // Afficher l'alerte correspondante à l'étape
    showAlertForStep(step);
    
    // Mettre à jour l'indicateur de progression
    const progress = ((step - 1) / 3) * 100;
    document.getElementById('step-progress').style.width = `${progress}%`;
    
    // Mettre à jour les points d'étape
    document.querySelectorAll('.step-dot').forEach(dot => {
        const dotStep = parseInt(dot.getAttribute('data-step'));
        dot.classList.remove('active', 'completed');
        if (dotStep < step) {
            dot.classList.add('completed');
        } else if (dotStep === step) {
            dot.classList.add('active');
        }
    });
    
    // Si nous sommes à l'étape 4, mettez à jour le récapitulatif
    if (step === 4) {
        updateRecap(); // Met à jour le récapitulatif et génère les champs propriétaires
        updateDeposit(); // Mettre à jour l'acompte
    }
}

// Fonction pour soumettre la réservation
function confirmReservation() {
    // Avant d'effectuer la réservation, afficher le modal des conditions
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
    termsModal.show();
    
    // Réinitialiser l'état de la case à cocher
    document.getElementById('acceptTerms').checked = false;
    document.getElementById('confirmTerms').disabled = true;
}

// Fonction pour procéder à la réservation après acceptation des conditions
function proceedWithReservation() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const slotId = localStorage.getItem('selectedSlotId');
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    // Récupérer la valeur skipSelection depuis localStorage ou depuis le checkbox
    const skipSelectionCheckbox = document.getElementById('skipSelection');
    const skipSelection = skipSelectionCheckbox ? skipSelectionCheckbox.checked : (localStorage.getItem('skipSelection') === 'true');
    
    console.log('Skip Selection avant envoi:', skipSelection);
    
    if (!csrfToken) {
        console.error('CSRF token missing');
        alert('Erreur: Token CSRF manquant');
        return;
    }
    
    // Valider les champs propriétaires
    if (!validateOwnerFields()) {
        alert('Veuillez remplir correctement les informations pour tous les propriétaires');
        return;
    }
    
    const ownersData = collectOwnersData();
    
    // Désactiver le bouton pendant le traitement
    const submitButton = document.getElementById('submit-reservation');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
    }
    
    const data = {
        reservation_number: 'R-' + Math.floor(100000 + Math.random() * 900000),
        slot_id: parseInt(slotId),                        
        quantity: quantity,
        skip_selection: skipSelection,                    
        owners: ownersData
    };
    
    // Ajouter pour déboguer
    console.log('Données JSON avant envoi:', JSON.stringify(data, null, 2));
    
    fetch('/reservation/confirm', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                console.error('Error response:', errorData);
                throw new Error(errorData.message || 'Erreur lors de la confirmation');
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Réponse de confirmation:', data);
        if (data.status === 'success') {
            // Vider les données locales
            localStorage.removeItem('selectedSlotId');
            localStorage.removeItem('selectedTime');
            localStorage.removeItem('skipSelection');
            
            // Fermer le modal des conditions si toujours ouvert
            const termsModal = bootstrap.Modal.getInstance(document.getElementById('termsModal'));
            if (termsModal) {
                termsModal.hide();
            }
            
            // Rediriger vers la page de reçu
            if (data.redirectUrl) {
                window.location.href = data.redirectUrl;
            } else {
                console.error('URL de redirection non fournie');
                alert('Réservation confirmée mais impossible de rediriger vers le reçu.');
            }
        } else {
            throw new Error(data.message || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        console.error('Erreur lors de la confirmation:', error);
        alert('Erreur lors de la confirmation: ' + error.message);
        
        // Réactiver le bouton
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Confirmer la réservation';
        }
    });
}

// Sélection du jour
function selectDay(day) {
    selectedDay = day;
    const formattedDate = new Date(day).toLocaleDateString('fr-FR', {
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric'
    });

    // Vérifier l'existence des éléments avant de mettre à jour leur contenu
    const selectedDayElement = document.getElementById('selected-day');
    const recapDayElement = document.getElementById('recap-day');
    const confirmationDayElement = document.getElementById('confirmation-day');

    if (selectedDayElement) {
        selectedDayElement.textContent = `- ${formattedDate}`;
    }
    
    if (recapDayElement) {
        recapDayElement.textContent = formattedDate;
    }
    
    if (confirmationDayElement) {
        confirmationDayElement.textContent = formattedDate;
    }

    // Stocker la date dans localStorage
    localStorage.setItem('selectedDay', formattedDate);

    // Passer la date dans l'URL en l'encodant pour éviter les erreurs
    let url = `/get-slots/${encodeURIComponent(selectedDay)}`;

    // Requête AJAX pour récupérer les créneaux horaires
    $.get(url)
        .done(function(response) {
            console.log(response); // Debugging : voir la réponse

            let slotsContainer = $('#time-slots-container');
            slotsContainer.empty();

            if (Array.isArray(response) && response.length > 0) {
                response.forEach(slot => {
                    let cardClass = "";
                    let clickAttr = "";
                    let placesText = "";
                    
                    // Formater l'affichage de l'heure pour inclure début et fin
                    const startTime = slot.start_time.substring(0, 5).replace(':', 'h');
                    const endTime = slot.end_time.substring(0, 5).replace(':', 'h');
                    const timeRange = `${startTime} - ${endTime}`;
                    
                    if (!slot.available) {
                        // Créneau bloqué
                        cardClass = "card creneaux-heure bg-light text-muted";
                        clickAttr = ""; // Pas de click event
                        placesText = `<span class="badge bg-danger">Bloqué</span>`;
                    } else if (slot.places_restantes <= 0) {
                        // Créneau complet
                        cardClass = "card creneaux-heure bg-light text-muted";
                        clickAttr = ""; // Pas de click event
                        placesText = "Complet";
                    } else {
                        // Créneau disponible
                        cardClass = "card creneaux-heure";
                        clickAttr = `onclick="selectTimeSlot('${slot.id}', '${slot.start_time}')"`;
                        placesText = `${slot.places_restantes} places restantes`;
                    }
                    
                    let cardHtml = `
                        <div class="col-md-4 mb-4">
                            <div class="${cardClass}" ${clickAttr}>
                                <div class="card-body text-center">
                                    <h5 class="card-title">${timeRange}</h5>
                                    <p class="card-text"><small class="text-muted">${placesText}</small></p>
                                </div>
                            </div>
                        </div>`;
                    slotsContainer.append(cardHtml);
                });
            } else {
                slotsContainer.append('<p>Aucun créneau disponible pour cette date.</p>');
            }
        })
        .fail(function() {
            alert('Erreur lors de la récupération des créneaux.');
        });

    // Passer à l'étape suivante
    goToStep(2);
}

// Sélection de l'heure
function selectTimeSlot(slotId, time) {
    // Stocker le slot_id dans localStorage
    localStorage.setItem('selectedSlotId', slotId);
    localStorage.setItem('selectedTime', time);

    // Mettre à jour l'UI
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    const selectedSlot = document.querySelector(`[data-slot-id="${slotId}"]`);
    if (selectedSlot) {
        selectedSlot.classList.add('selected');
        
        // Mettre à jour le récapitulatif
        const recapSlot = document.getElementById('recap-slot');
        if (recapSlot) {
            recapSlot.textContent = time;
        }
    }
    goToStep(3);
}

// Fonction pour mettre à jour l'acompte
function updateDeposit() {
    const depositElement = document.getElementById('recap-deposit');
    if (!depositElement) return;
    
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    // Le prix par unité est identique pour tous
    const deposit = quantity * 50; // 50€ par unité
    depositElement.textContent = `${deposit},00 €`;
}

// Modifions la fonction d'incrémentation pour tenir compte de la limite
function incrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    
    // Pour les associations, pas de limite max
    if (isAssociation) {
        selectedQuantity = currentValue + 1;
        quantityInput.value = selectedQuantity;
        
        // Mettre à jour le récapitulatif
        if (document.getElementById('recap-quantity')) {
            document.getElementById('recap-quantity').textContent = selectedQuantity;
        }
        if (document.getElementById('confirmation-quantity')) {
            document.getElementById('confirmation-quantity').textContent = selectedQuantity;
        }
        
        updateDeposit(); // Mettre à jour l'acompte
        generateOwnerFields(); // Mettre à jour les champs propriétaires
        return;
    }
    
    // Pour les acheteurs, respecter la limite
    const maxValue = Math.min(parseInt(quantityInput.max || 4), remainingReservations);
    
    if (currentValue < maxValue) {
        selectedQuantity = currentValue + 1;
        quantityInput.value = selectedQuantity;
        
        // Mettre à jour le récapitulatif
        if (document.getElementById('recap-quantity')) {
            document.getElementById('recap-quantity').textContent = selectedQuantity;
        }
        if (document.getElementById('confirmation-quantity')) {
            document.getElementById('confirmation-quantity').textContent = selectedQuantity;
        }
        
        updateDeposit(); // Mettre à jour l'acompte
        generateOwnerFields(); // Mettre à jour les champs propriétaires
    } else if (currentValue >= maxValue) {
        showLimitReachedAlert();
    }
}



// Décrémentation de la quantité
function decrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (parseInt(quantityInput.value) > parseInt(quantityInput.min)) {
        selectedQuantity = parseInt(quantityInput.value) - 1;
        quantityInput.value = selectedQuantity;
        document.getElementById('recap-quantity').textContent = selectedQuantity;
        document.getElementById('confirmation-quantity').textContent = selectedQuantity;
        updateDeposit(); // Mettre à jour l'acompte
        
        // Si une alerte est visible, la masquer car nous diminuons la quantité
        const quantityAlert = document.getElementById('quantityAlert');
        if (quantityAlert) {
            quantityAlert.style.display = 'none';
        }
    }
}

// Option de ne pas venir choisir l'agneau
function toggleSkipSelection() {
    skipSelection = document.getElementById('skipSelection').checked;
    
    // Stocker la valeur dans localStorage pour la conserver entre les étapes
    localStorage.setItem('skipSelection', skipSelection);
    
    // Mettre à jour le récapitulatif
    const selectionInfo = skipSelection ? 'Ne viendra pas choisir' : 'Sélection sur place';
    
    // Mettre à jour le récapitulatif si l'élément existe
    if (document.getElementById('recap-selection')) {
        document.getElementById('recap-selection').textContent = selectionInfo;
    }
    
    // Mettre à jour le modal de confirmation si l'élément existe
    if (document.getElementById('confirmation-selection')) {
        document.getElementById('confirmation-selection').textContent = selectionInfo;
    }
    
    console.log("Option 'Ne pas venir choisir':", skipSelection);
}

// Fonction pour vérifier la limite de réservation de l'utilisateur
// Fonction pour vérifier la limite de réservation de l'utilisateur
function checkReservationLimit() {
    fetch('/check-reservation-limit')
        .then(response => response.json())
        .then(data => {
            userReservationsCount = data.currentCount;
            remainingReservations = data.remainingCount;
            isAssociation = data.isAssociation || false; // Récupérer l'info si c'est une association
            
            console.log('Données de limite:', data);
            
            // Mettre à jour l'interface utilisateur
            updateReservationUI();
            
            // Afficher un message si la limite est atteinte (seulement pour les acheteurs)
            if (!isAssociation && data.limitReached) {
                showLimitReachedAlert();
            }
        })
        .catch(error => {
            console.error('Erreur lors de la vérification de la limite de réservation:', error);
        });
}

// Fonction pour mettre à jour l'interface utilisateur en fonction de la limite
function updateReservationUI() {
    const quantityInput = document.getElementById('quantity');
    
    if (quantityInput) {
        if (isAssociation) {
            // Pour les associations, pas de limite
            quantityInput.removeAttribute('max');
            
            // Mettre à jour le texte d'aide
            const helpText = quantityInput.parentElement.nextElementSibling;
            if (helpText) {
                helpText.textContent = "En tant qu'association, vous n'avez pas de limite de réservation";
            }
            
            // Activer tous les boutons de jour si désactivés
            document.querySelectorAll('.creneaux-jour.disabled').forEach(btn => {
                btn.classList.remove('disabled');
                btn.style.opacity = '';
                btn.style.cursor = '';
                // Restaurer le gestionnaire d'événements original
                // (cela nécessiterait de stocker et restaurer la fonction originale)
            });
        } else {
            // Pour les acheteurs, limiter à 4
            quantityInput.max = remainingReservations;
            quantityInput.value = Math.min(parseInt(quantityInput.value), remainingReservations);
            
            // Si aucune réservation n'est possible, désactiver les boutons
            if (remainingReservations <= 0) {
                document.querySelectorAll('.creneaux-jour').forEach(btn => {
                    btn.classList.add('disabled');
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                    btn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        showLimitReachedAlert();
                    };
                });
            }
        }
    }
}

// Fonction pour afficher une alerte stylisée lorsque la limite est atteinte
function showLimitReachedAlert() {
    // Créer l'élément d'alerte s'il n'existe pas déjà
    let alertElement = document.getElementById('limit-reached-alert');
    if (!alertElement) {
        alertElement = document.createElement('div');
        alertElement.id = 'limit-reached-alert';
        alertElement.className = 'alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3 shadow';
        alertElement.style.zIndex = '9999';
        alertElement.style.animationName = 'slideDown';
        alertElement.style.animationDuration = '0.3s';
        
        // Ajouter les styles d'animation au document s'ils n'existent pas
        if (!document.getElementById('limit-alert-styles')) {
            const styleElement = document.createElement('style');
            styleElement.id = 'limit-alert-styles';
            styleElement.textContent = `
                @keyframes slideDown {
                    from { transform: translate(-50%, -100%); }
                    to { transform: translate(-50%, 0); }
                }
                
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(styleElement);
        }
        
        document.body.appendChild(alertElement);
    }
    
    // Mettre à jour le contenu de l'alerte
    alertElement.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill text-danger me-2 fs-4"></i>
            <div>
                <strong>Limite atteinte!</strong> Vous avez déjà réservé ${userReservationsCount} agneau(x) sur le maximum de 4 autorisés.
            </div>
            <button type="button" class="btn-close ms-auto" onclick="document.getElementById('limit-reached-alert').remove();"></button>
        </div>
    `;
    
    // Faire disparaître l'alerte après 5 secondes
    setTimeout(() => {
        alertElement.style.animationName = 'fadeOut';
        alertElement.style.animationDuration = '0.5s';
        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.parentNode.removeChild(alertElement);
            }
        }, 500);
    }, 5000);
}

// Sélection de la taille
function selectSize(size) {
    selectedSize = size;
    document.querySelectorAll('.taille-option').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    
    let sizeText = size === 'grand' ? 'Grand (~25kg)' : 'Moyen (~18kg)';
    document.getElementById('recap-size').textContent = sizeText;
    document.getElementById('confirmation-size').textContent = sizeText;
}

// Générer les champs de propriétaires en fonction de la quantité
function generateOwnerFields() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const container = document.getElementById('owners-container');
    
    // Vider le conteneur avant de générer les nouveaux champs
    if (container) {
        container.innerHTML = '';
        
        // Générer les champs pour chaque agneau
        for (let i = 1; i <= quantity; i++) {
            const ownerSection = document.createElement('div');
            ownerSection.className = 'card mb-3';
            
            // Générer un ID unique pour le collapsible
            const collapseId = `owner-collapse-${i}`;
            
            // Déterminer si c'est le premier agneau (celui du propriétaire principal)
            // Si c'est une association, ne pas pré-remplir avec les données de l'utilisateur
            const isFirstOwner = (i === 1) && !isAssociation;
            const headerText = isFirstOwner ? 'Agneau #1 (Vous-même)' : `Agneau #${i}`;
            
            // Le premier propriétaire est ouvert par défaut, les autres sont fermés
            const showClass = isFirstOwner ? 'show' : '';
            
            // Préparer les valeurs par défaut pour le premier propriétaire
            let firstNameValue = '';
            let lastNameValue = '';
            let emailValue = '';
            let phoneValue = '';
            let addressValue = '';
            let readOnlyAttr = '';
            
            if (isFirstOwner && window.userInfo) {
                firstNameValue = window.userInfo.firstName || '';
                lastNameValue = window.userInfo.lastName || '';
                emailValue = window.userInfo.email || '';
                phoneValue = window.userInfo.phone || '';
                addressValue = window.userInfo.full_address || '';
            }
            
            ownerSection.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">${headerText}</h6>
                    <button class="btn btn-link btn-sm p-0" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#${collapseId}" 
                            aria-expanded="${isFirstOwner ? 'true' : 'false'}" 
                            aria-controls="${collapseId}">
                        <i class="bi ${isFirstOwner ? 'bi-chevron-up' : 'bi-chevron-down'}"></i>
                    </button>
                </div>
                <div id="${collapseId}" class="collapse ${showClass}">
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="owner-firstname-${i}" class="form-label">Prénom</label>
                                <input type="text" class="form-control owner-input" id="owner-firstname-${i}" 
                                    name="owners[${i}][firstname]" value="${firstNameValue}" ${readOnlyAttr} required>
                            </div>
                            <div class="col-md-6">
                                <label for="owner-lastname-${i}" class="form-label">Nom</label>
                                <input type="text" class="form-control owner-input" id="owner-lastname-${i}" 
                                    name="owners[${i}][lastname]" value="${lastNameValue}" ${readOnlyAttr} required>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="owner-email-${i}" class="form-label">Email</label>
                                <input type="email" class="form-control owner-input" id="owner-email-${i}" 
                                    name="owners[${i}][email]" value="${emailValue}" ${readOnlyAttr} required>
                            </div>
                            <div class="col-md-6">
                                <label for="owner-phone-${i}" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control owner-input" id="owner-phone-${i}" 
                                    name="owners[${i}][phone]" value="${phoneValue}" ${readOnlyAttr} required>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="owner-address-${i}" class="form-label">Ville, Code postal</label>
                                <textarea class="form-control owner-input" id="owner-address-${i}" 
                                    name="owners[${i}][address]" rows="2" ${readOnlyAttr} required>${addressValue}</textarea>
                            </div>
                        </div>
                        
                        ${isFirstOwner ? `
                        <div class="form-text text-muted mt-3">
                            <i class="bi bi-info-circle"></i> Ces informations sont pré-remplies avec votre profil.
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            container.appendChild(ownerSection);
            
            // Ajouter un gestionnaire d'événements pour changer l'icône lors de l'ouverture/fermeture
            const collapseElement = ownerSection.querySelector(`#${collapseId}`);
            const toggleButton = ownerSection.querySelector(`[data-bs-target="#${collapseId}"]`);
            const toggleIcon = toggleButton.querySelector('i');
            
            collapseElement.addEventListener('show.bs.collapse', function () {
                toggleIcon.classList.remove('bi-chevron-down');
                toggleIcon.classList.add('bi-chevron-up');
            });
            
            collapseElement.addEventListener('hide.bs.collapse', function () {
                toggleIcon.classList.remove('bi-chevron-up');
                toggleIcon.classList.add('bi-chevron-down');
            });
        }
    }
}

// Validation des champs propriétaires
function validateOwnerFields() {
    const ownerInputs = document.querySelectorAll('.owner-input');
    let allValid = true;
    
    ownerInputs.forEach(input => {
        // Vérification générale pour les champs vides
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            allValid = false;
        } 
        // Validation spécifique pour les emails
        else if (input.type === 'email' && !validateEmail(input.value.trim())) {
            input.classList.add('is-invalid');
            allValid = false;
        }
        // Validation spécifique pour les téléphones
        else if (input.type === 'tel' && !validatePhone(input.value.trim())) {
            input.classList.add('is-invalid');
            allValid = false;
        }
        else {
            input.classList.remove('is-invalid');
        }
    });
    
    return allValid;
}

// Fonction de validation d'email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Fonction de validation de téléphone
function validatePhone(phone) {
    // Accepte un format international avec ou sans +
    // Exemple: +33612345678 ou 33612345678 ou 0612345678
    const re = /^(?:\+?\d{1,3})?[- ]?\d{9,10}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Collecter les données des propriétaires
function collectOwnersData() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    let ownersData = [];
    
    for (let i = 1; i <= quantity; i++) {
        const firstname = document.getElementById(`owner-firstname-${i}`).value;
        const lastname = document.getElementById(`owner-lastname-${i}`).value;
        // Assurez-vous que ces valeurs ne sont pas vides
        
        ownersData.push({
            firstname: firstname,
            lastname: lastname,
            email: document.getElementById(`owner-email-${i}`).value,
            phone: document.getElementById(`owner-phone-${i}`).value,
            address: document.getElementById(`owner-address-${i}`).value
        });
        
        console.log(`Propriétaire ${i}:`, {
            firstname, lastname, 
            email: document.getElementById(`owner-email-${i}`).value
        });
    }
    
    return ownersData;
}

// Mise à jour du récapitulatif et des champs de propriétaires
function updateRecap() {
    const selectedTime = localStorage.getItem('selectedTime');
    const selectedSize = localStorage.getItem('selectedSize') || 'grand';
    const quantity = document.getElementById('quantity').value;

    // Mettre à jour les informations de récapitulation
    if (document.getElementById('recap-time')) {
        document.getElementById('recap-time').textContent = selectedTime || 'Non sélectionné';
    }
    if (document.getElementById('recap-size')) {
        document.getElementById('recap-size').textContent = selectedSize === 'grand' ? 'Grand (~25kg)' : 'Moyen (~18kg)';
    }
    if (document.getElementById('recap-quantity')) {
        document.getElementById('recap-quantity').textContent = quantity;
    }
    
    // Mettre à jour l'option "Ne pas venir choisir l'agneau"
    if (document.getElementById('skipSelection')) {
        // Restaurer l'état depuis le localStorage si disponible
        const savedSkipSelection = localStorage.getItem('skipSelection');
        if (savedSkipSelection) {
            skipSelection = savedSkipSelection === 'true';
            document.getElementById('skipSelection').checked = skipSelection;
        }
        
        // Mettre à jour le récapitulatif
        const selectionInfo = skipSelection ? 'Ne viendra pas choisir' : 'Sélection sur place';
        if (document.getElementById('recap-selection')) {
            document.getElementById('recap-selection').textContent = selectionInfo;
        }
        
        // Mettre à jour le modal de confirmation si l'élément existe
        if (document.getElementById('confirmation-selection')) {
            document.getElementById('confirmation-selection').textContent = selectionInfo;
        }
    }

    // Générer les champs de propriétaires
    generateOwnerFields();

    // Mettre à jour l'acompte
    updateDeposit();
}