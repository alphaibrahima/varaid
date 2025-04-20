document.addEventListener("DOMContentLoaded", function() {
    console.log(document.getElementById('confirmation-day'));
    
    // Initialiser les alertes en fonction de l'étape active
    const currentStep = document.querySelector('.step.active');
    if (currentStep) {
        const stepNumber = currentStep.id.split('-')[1];
        showAlertForStep(stepNumber);
    }
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
    // Vérifier si l'affiliation est confirmée avant de changer d'étape
    if (typeof affiliationVerified !== 'undefined' && !affiliationVerified) {
        // Afficher la modal d'affiliation
        const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
        affiliationModal.show();
        return;
    }
    
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
    
    // Initialiser Stripe et mettre à jour le bouton si on est à l'étape 4
    if (step === 4) {
        updateRecap(); // Met à jour le récapitulatif et génère les champs propriétaires
        updateDeposit(); // Mettre à jour l'acompte
        updatePaymentButton(); // Mettre à jour le texte du bouton
        initStripe();
    }
}

// Sélection du jour
function selectDay(day) {
    // Vérifier si l'affiliation est confirmée
    if (typeof affiliationVerified !== 'undefined' && !affiliationVerified) {
        // Afficher la modal d'affiliation
        const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
        affiliationModal.show();
        return;
    }
    
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
                    
                    if (!slot.available) {
                        // Créneau bloqué - afficher juste "Bloqué" sans la raison
                        cardClass = "card creneaux-heure bg-light text-muted";
                        clickAttr = ""; // Pas de click event
                        placesText = `<span class="badge bg-danger">Bloqué</span>`;
                        // Suppression de l'affichage de la raison du blocage
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
                                    <h5 class="card-title">${slot.start_time.substring(0, 5)}</h5>
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
    // Vérifier si l'affiliation est confirmée
    if (typeof affiliationVerified !== 'undefined' && !affiliationVerified) {
        // Afficher la modal d'affiliation
        const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
        affiliationModal.show();
        return;
    }
    
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
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const deposit = quantity * 100; // 100€ par unité
    depositElement.textContent = `${deposit},00 €`;
}

// Modifions la fonction d'incrémentation pour tenir compte de la limite
function incrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value);
    const maxValue = Math.min(parseInt(quantityInput.max), remainingReservations);
    
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
function checkReservationLimit() {
    fetch('/check-reservation-limit')
        .then(response => response.json())
        .then(data => {
            userReservationsCount = data.currentCount;
            remainingReservations = data.remainingCount;
            
            // Mettre à jour l'interface utilisateur
            updateReservationUI();
            
            // Afficher un message si la limite est atteinte
            if (data.limitReached) {
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
        // Limiter la quantité maximale au nombre restant
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
            const isFirstOwner = (i === 1);
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
                // Option: rendre les champs en lecture seule pour le premier propriétaire
                // readOnlyAttr = 'readonly';
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
                                <label for="owner-address-${i}" class="form-label">Adresse</label>
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

// Variables Stripe
let stripe;
let elements;
let paymentElement;

// Initialisation de Stripe
async function initStripe() {
    try {
        console.log('Initialisation de Stripe');
        
        // Vérifier si l'affiliation est confirmée
        if (typeof affiliationVerified !== 'undefined' && !affiliationVerified) {
            // Afficher la modal d'affiliation
            const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
            affiliationModal.show();
            return;
        }
        
        const slotId = localStorage.getItem('selectedSlotId');
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        
        // Désactiver le bouton pendant le chargement
        const submitButton = document.getElementById('submit-payment');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';
        }
        
        // Créer une intention de paiement
        const { clientSecret, paymentIntentId } = await createPaymentIntent(slotId, quantity);
        console.log('PaymentIntent créé avec succès:', { clientSecret: '***', paymentIntentId });
        
        // Initialiser Stripe avec la clé publique
        if (window.stripeConfig && window.stripeConfig.publicKey) {
            stripe = Stripe(window.stripeConfig.publicKey);
        } else {
            stripe = Stripe('pk_test_51JUagXA0Pqxe87f5NFHuEKUQO0xyy8UIUzzlUbTlnc9ixFC30N0x1DCzSFrTDaLrgBmDUmBDRnJEQnOd9vf1U5Bq00uag0krea');
        }
        
        // Vérifier si l'élément existe
        const paymentElementContainer = document.getElementById('payment-element');
        if (!paymentElementContainer) {
            throw new Error("L'élément #payment-element n'existe pas dans le DOM");
        }
        
        // Créer les éléments Stripe
        elements = stripe.elements({
            clientSecret,
            appearance: {
                theme: 'stripe',
            }
        });
        
        // Créer et monter l'élément de paiement
        paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
        
        // Réactiver le bouton
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = `Confirmer et payer l'acompte de ${quantity * 100},00 €`;
        }
        
        // Écouter la soumission du formulaire
        const form = document.getElementById('payment-form');
        if (form) {
            form.addEventListener('submit', handlePaymentSubmission);
        }
    } catch (error) {
        console.error('Error initializing Stripe:', error);
        alert('Une erreur est survenue lors de l\'initialisation du paiement. Veuillez réessayer.');
        
        // Réactiver le bouton en cas d'erreur
        const submitButton = document.getElementById('submit-payment');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer';
        }
    }
}

// Fonction pour créer une intention de paiement
async function createPaymentIntent(slotId, quantity) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    console.log('Envoi de la requête create-payment-intent avec slotId:', slotId, 'et quantity:', quantity);
    
    const response = await fetch('/create-payment-intent', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            slotId: slotId,
            quantity: quantity
        })
    });
    
    if (!response.ok) {
        const errorText = await response.text();
        console.error('Réponse HTTP non-OK:', response.status, errorText);
        throw new Error(`Erreur HTTP ${response.status}: ${errorText}`);
    }
    
    const data = await response.json();
    console.log('Réponse create-payment-intent:', data);
    return data;
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

// Traitement du paiement
async function handlePaymentSubmission(e) {
    e.preventDefault();
    
    // Vérifier si l'affiliation est confirmée
    if (typeof affiliationVerified !== 'undefined' && !affiliationVerified) {
        // Afficher la modal d'affiliation
        const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
        affiliationModal.show();
        return;
    }
    
    // Valider les champs propriétaires
    if (!validateOwnerFields()) {
        alert('Veuillez remplir les noms et prénoms pour tous les propriétaires');
        return;
    }
    
    const submitButton = document.getElementById('submit-payment');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
    }
    
    const ownersData = collectOwnersData();
    
    try {
        // Confirmer le paiement avec Stripe
        console.log('Confirmation du paiement...');
        const result = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: `${window.location.origin}/payment-success`,
            },
            redirect: 'if_required'
        });
        
        if (result.error) {
            // Afficher l'erreur
            console.error('Erreur de paiement:', result.error);
            const errorElement = document.getElementById('payment-errors');
            if (errorElement) {
                errorElement.textContent = result.error.message;
            }
            
            // Réactiver le bouton
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Réessayer le paiement';
            }
        } else if (result.paymentIntent) {
            // Le paiement a réussi sans redirection
            console.log('Paiement réussi sans redirection:', result.paymentIntent);
            submitReservation(result.paymentIntent.id, ownersData);
        }
    } catch (error) {
        console.error('Erreur lors du paiement:', error);
        alert(`Erreur lors du paiement: ${error.message}`);
        
        // Réactiver le bouton
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer le paiement';
        }
    }
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

// Fonction pour soumettre la réservation après paiement réussi
function submitReservation(paymentIntentId, ownersData) {
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
    
    console.log('Soumission de la réservation...', {
        paymentIntentId,
        ownersData,
        skipSelection
    });
    
    const data = {
        reservation_number: 'R-' + Math.floor(100000 + Math.random() * 900000),
        cardholder_name: document.getElementById('cardholder-name').value,
        cardholder_email: document.getElementById('cardholder-email').value,
        slot_id: parseInt(slotId),                        
        quantity: quantity,
        skip_selection: skipSelection,                    
        payment_intent_id: paymentIntentId,               
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
        const submitButton = document.getElementById('submit-payment');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer';
        }
    });
}

// Fonction pour mettre à jour le bouton de paiement
function updatePaymentButton() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const deposit = quantity * 100; // 100€ par unité
    const paymentButton = document.getElementById('submit-payment');
    paymentButton.textContent = `Confirmer et payer l'acompte de ${deposit},00 €`;
}

// Fonction pour afficher les créneaux horaires
function displaySlots(slots) {
    const container = document.getElementById('slots-container');
    container.innerHTML = '';

    slots.forEach(slot => {
        const slotElement = document.createElement('div');
        slotElement.className = 'col-md-4 mb-3';
        slotElement.innerHTML = `
            <div class="time-slot card" 
                 data-slot-id="${slot.id}" 
                 onclick="selectTimeSlot(${slot.id}, '${slot.start_time}')">
                <div class="card-body text-center">
                    <h5 class="card-title">${slot.start_time}</h5>
                    <p class="card-text">Places disponibles: ${slot.capacity}</p>
                </div>
            </div>
        `;
        container.appendChild(slotElement);
    });
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

// Gestion de la modal de vérification d'affiliation
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales pour l'affiliation
    const affiliationAlert = document.getElementById('affiliation-alert');
    const resendCodeBtn = document.getElementById('resend-code-btn');
    
    // Afficher automatiquement la modal si l'affiliation n'est pas vérifiée
    if (typeof affiliationVerified !== 'undefined' && !affiliationVerified) {
        const affiliationModal = new bootstrap.Modal(document.getElementById('affiliationModal'));
        affiliationModal.show();
    }
    
    // Écouter la soumission du formulaire d'affiliation
    const affiliationForm = document.getElementById('affiliation-form');
    if (affiliationForm) {
        affiliationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = document.getElementById('affiliation_code').value;
            const verifyBtn = document.getElementById('verify-code-btn');
            
            // Désactiver le bouton pendant la vérification
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Vérification...';
            
            // Envoyer la requête AJAX
            fetch('/verify-affiliation-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ affiliation_code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher un message de succès
                    if (affiliationAlert) {
                        affiliationAlert.classList.remove('d-none', 'alert-danger');
                        affiliationAlert.classList.add('alert-success');
                        affiliationAlert.textContent = data.message;
                    }
                    
                    // Mettre à jour la variable d'état de l'affiliation
                    window.affiliationVerified = true;
                    
                    // Fermer la modal après 2 secondes
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('affiliationModal'));
                        if (modal) modal.hide();
                        // Rafraîchir la page pour mettre à jour l'interface
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                // Afficher un message d'erreur
                if (affiliationAlert) {
                    affiliationAlert.classList.remove('d-none', 'alert-success');
                    affiliationAlert.classList.add('alert-danger');
                    affiliationAlert.textContent = error.message || 'Une erreur est survenue. Veuillez réessayer.';
                }
                
                // Réactiver le bouton
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Vérifier le code';
            });
        });
    }
    
    // Gérer la demande d'un nouveau code
    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi en cours...';
            
            fetch('/resend-affiliation-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher un message de succès
                    if (affiliationAlert) {
                        affiliationAlert.classList.remove('d-none', 'alert-danger');
                        affiliationAlert.classList.add('alert-success');
                        affiliationAlert.textContent = data.message;
                    }
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                // Afficher un message d'erreur
                if (affiliationAlert) {
                    affiliationAlert.classList.remove('d-none', 'alert-success');
                    affiliationAlert.classList.add('alert-danger');
                    affiliationAlert.textContent = error.message || 'Une erreur est survenue. Veuillez réessayer.';
                }
            })
            .finally(() => {
                // Réactiver le bouton
                this.disabled = false;
                this.textContent = 'Recevoir un nouveau code';
            });
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
});