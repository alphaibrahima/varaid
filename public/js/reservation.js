document.addEventListener("DOMContentLoaded", function() {
    console.log(document.getElementById('confirmation-day'));
});
window.onload = function() {
    console.log("JavaScript chargé après le chargement complet !");
};
// Variables pour stocker les sélections
let selectedDay = '';
let selectedTime = '';
let selectedSize = 'grand';
let selectedQuantity = 1;

// Fonction pour changer d'étape
function goToStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById(`step-${step}`).classList.add('active');
    
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
                    let cardHtml = `
                        <div class="col-md-4 mb-4"> <!-- Colonne avec espacement -->
                            <div class="card creneaux-heure" onclick="selectTimeSlot('${slot.id}', '${slot.start_time}')">
                                <div class="card-body text-center">
                                    <h5 class="card-title">${slot.start_time.substring(0, 5)}</h5>
                                    <p class="card-text"><small class="text-muted">${slot.max_reservations} places restantes</small></p>
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
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const deposit = quantity * 100; // 100€ par unité
    depositElement.textContent = `${deposit},00 €`;
}

// Incrémentation de la quantité (modifiée pour mettre à jour les champs propriétaires)
function incrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (parseInt(quantityInput.value) < parseInt(quantityInput.max)) {
        selectedQuantity = parseInt(quantityInput.value) + 1;
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
    }
}

// Décrémentation de la quantité (modifiée pour mettre à jour les champs propriétaires)
function decrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (parseInt(quantityInput.value) > parseInt(quantityInput.min)) {
        selectedQuantity = parseInt(quantityInput.value) - 1;
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
    }
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
            
            // Déterminer si c'est le premier agneau (celui du propriétaire principal)
            const isFirstOwner = (i === 1);
            const headerText = isFirstOwner ? 'Agneau #1 (Vous-même)' : `Agneau #${i}`;
            
            // Préparer les valeurs par défaut pour le premier propriétaire
            let firstNameValue = '';
            let lastNameValue = '';
            let readOnlyAttr = '';
            
            if (isFirstOwner && window.userInfo) {
                firstNameValue = window.userInfo.firstName || '';
                lastNameValue = window.userInfo.lastName || '';
                // Option: rendre les champs en lecture seule pour le premier propriétaire
                // readOnlyAttr = 'readonly';
            }
            
            ownerSection.innerHTML = `
                <div class="card-header bg-light">
                    <h6 class="mb-0">${headerText}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
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
                    ${isFirstOwner ? `
                    <div class="form-text text-muted mt-2">
                        <i class="bi bi-info-circle"></i> Ces informations sont pré-remplies avec votre profil.
                    </div>
                    ` : ''}
                </div>
            `;
            container.appendChild(ownerSection);
        }
    }
}

// Variables Stripe
// let stripe;
// let card;

// // Initialisation de Stripe
// function initStripe() {
//     // Remplacez 'pk_test_votreClePubliqueStripe' par votre clé publique Stripe
//     stripe = Stripe('pk_test_votreClePubliqueStripe');
//     const elements = stripe.elements();
    
//     // Style pour l'élément de carte
//     const style = {
//         base: {
//             color: '#32325d',
//             fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
//             fontSmoothing: 'antialiased',
//             fontSize: '16px',
//             '::placeholder': {
//                 color: '#aab7c4'
//             }
//         },
//         invalid: {
//             color: '#fa755a',
//             iconColor: '#fa755a'
//         }
//     };
    
//     // Si un élément de carte existe déjà, on ne le recrée pas
//     if (!card) {
//         // Création de l'élément de carte
//         card = elements.create('card', {
//             style: style,
//             hidePostalCode: true  // On cache le code postal pour simplifier
//         });
        
//         // Montage de l'élément de carte dans le DOM
//         card.mount('#card-element');
        
//         // Gestion des erreurs de saisie carte
//         card.addEventListener('change', function(event) {
//             const displayError = document.getElementById('card-errors');
//             if (event.error) {
//                 displayError.textContent = event.error.message;
//             } else {
//                 displayError.textContent = '';
//             }
//         });
        
//         // Gestion du bouton de paiement
//         document.getElementById('submit-payment').addEventListener('click', processPayment);
//     }
// }

// // Fonction pour traiter le paiement
// function processPayment(event) {
//     if (event) event.preventDefault();
    
//     const submitButton = document.getElementById('submit-payment');
//     const cardholderName = document.getElementById('cardholder-name').value;
//     const cardholderEmail = document.getElementById('cardholder-email').value;
    
//     // Validation de base
//     if (!cardholderName || !cardholderEmail) {
//         alert('Veuillez remplir tous les champs de paiement');
//         return;
//     }
    
//     // Valider les champs des propriétaires
//     const ownerInputs = document.querySelectorAll('.owner-input');
//     let allOwnersValid = true;
//     let ownersData = [];
    
//     // Regrouper les données des propriétaires
//     const quantity = parseInt(document.getElementById('quantity').value) || 1;
//     for (let i = 1; i <= quantity; i++) {
//         const firstname = document.getElementById(`owner-firstname-${i}`).value;
//         const lastname = document.getElementById(`owner-lastname-${i}`).value;
        
//         if (!firstname || !lastname) {
//             allOwnersValid = false;
//             break;
//         }
        
//         ownersData.push({
//             firstname: firstname,
//             lastname: lastname
//         });
//     }
    
//     if (!allOwnersValid) {
//         alert('Veuillez remplir les noms et prénoms pour tous les propriétaires');
//         return;
//     }
    
//     // Désactiver le bouton pendant le traitement
//     submitButton.disabled = true;
//     submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
    
//     // Récupérer les données pour la requête
//     const slotId = localStorage.getItem('selectedSlotId');
    
//     if (!slotId) {
//         alert('Veuillez sélectionner un créneau horaire');
//         goToStep(2);
//         return;
//     }

//     const data = {
//         reservationNumber: 'R-' + Math.floor(100000 + Math.random() * 900000),
//         cardholderName: cardholderName,
//         cardholderEmail: cardholderEmail,
//         slotId: parseInt(slotId),
//         quantity: quantity,
//         owners: ownersData
//     };
    
//     // Dans un environnement de production, vous devriez faire un appel à votre serveur
//     // pour créer un PaymentIntent et récupérer le client_secret
    
//     // Simuler un appel réussi (pour test)
//     setTimeout(function() {
//         // Réactiver le bouton
//         submitButton.disabled = false;
//         submitButton.innerHTML = 'Confirmer et payer l\'acompte';
        
//         // Pour l'exemple, nous allons directement confirmer la réservation
//         confirmReservation(data);
//     }, 2000);
// }

// Variables Stripe
let stripe;
let elements;
let paymentElement;
let paymentIntentId;

// Initialisation de Stripe
function initStripe() {
    stripe = Stripe('pk_test_51JUagXA0Pqxe87f5oHIjCKz43aDvkqkxbxmI7i8pLnr4ynfnvo9Caf9qARxP5SZ8MhOwyABy1KOowNuZAI6NpIN5006xdGeAd9');
    
    // Récupérer les données nécessaires pour créer une intention de paiement
    const slotId = localStorage.getItem('selectedSlotId');
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    // Désactiver le bouton pendant le chargement
    const submitButton = document.getElementById('submit-payment');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';
    }
    
    // Créer une intention de paiement côté serveur
    createPaymentIntent(slotId, quantity).then(({clientSecret, paymentIntentId: intentId}) => {
        paymentIntentId = intentId;
        
        // Créer les éléments Stripe avec le client secret reçu
        elements = stripe.elements({
            clientSecret: clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#0066cc',
                }
            },
        });

        // Créer et monter l'élément de paiement
        paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
        
        // Écouter les événements de changement
        paymentElement.on('change', (event) => {
            const displayError = document.getElementById('payment-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // Réactiver le bouton
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = `Confirmer et payer l'acompte de ${quantity * 100},00 €`;
        }
        
        // Ajouter un gestionnaire d'événements pour la soumission du formulaire
        const form = document.getElementById('payment-form');
        if (form) {
            form.addEventListener('submit', handlePaymentSubmission);
        }
    }).catch(error => {
        console.error('Error initializing Stripe:', error);
        alert('Une erreur est survenue lors de l\'initialisation du paiement. Veuillez réessayer.');
        
        // Réactiver le bouton en cas d'erreur
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer';
        }
    });

    // Dans initStripe()
    debugStripe('Initialisation de Stripe avec la clé publique', 'pk_test_...');
    // Après avoir reçu le clientSecret
    debugStripe('ClientSecret obtenu', { id: paymentIntentId, hasSecret: !!clientSecret });
}

// Fonction pour créer une intention de paiement côté serveur
async function createPaymentIntent(slotId, quantity) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    try {
        console.log('Envoi de la requête create-payment-intent avec slotId:', slotId, 'et quantity:', quantity);
        
        const response = await fetch('/create-payment-intent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                slotId: slotId,
                quantity: quantity
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Erreur API:', errorData);
            throw new Error(errorData.error || 'Erreur lors de la création de l\'intention de paiement');
        }
        
        const data = await response.json();
        console.log('Réponse create-payment-intent:', data);
        return data;
    } catch (error) {
        console.error('Erreur complète:', error);
        throw error;
    }
}

// Gérer la soumission du formulaire de paiement
async function handlePaymentSubmission(e) {
    e.preventDefault();
    
    // Valider les champs des propriétaires
    if (!validateOwnerFields()) {
        alert('Veuillez remplir les noms et prénoms pour tous les propriétaires');
        return;
    }
    
    const submitButton = document.getElementById('submit-payment');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
    }
    
    // Collecter les données des propriétaires
    const ownersData = collectOwnersData();
    
    // Confirmer le paiement
    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: `${window.location.origin}/payment-success?owners=${encodeURIComponent(JSON.stringify(ownersData))}`,
        },
        redirect: 'if_required',
    });
    
    if (error) {
        // Afficher le message d'erreur à l'utilisateur
        const errorElement = document.getElementById('payment-errors');
        errorElement.textContent = error.message;
        
        // Réactiver le bouton
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer le paiement';
        }
    } else {
        // Le paiement a réussi sans redirection
        confirmReservation(paymentIntentId, ownersData);
    }
}

// Collecter les données des propriétaires
function collectOwnersData() {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    let ownersData = [];
    
    for (let i = 1; i <= quantity; i++) {
        const firstname = document.getElementById(`owner-firstname-${i}`).value;
        const lastname = document.getElementById(`owner-lastname-${i}`).value;
        
        ownersData.push({
            firstname: firstname,
            lastname: lastname
        });
    }
    
    return ownersData;
}

// Confirmer la réservation
function confirmReservation(paymentIntentId, ownersData) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const slotId = localStorage.getItem('selectedSlotId');
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    if (!csrfToken) {
        console.error('CSRF token missing');
        alert('Erreur: Token CSRF manquant');
        return;
    }
    
    const data = {
        reservationNumber: 'R-' + Math.floor(100000 + Math.random() * 900000),
        cardholderName: document.getElementById('cardholder-name').value,
        cardholderEmail: document.getElementById('cardholder-email').value,
        slotId: parseInt(slotId),
        quantity: quantity,
        paymentIntentId: paymentIntentId,
        owners: ownersData
    };
    
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
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        if (data.status === 'success') {
            // Clear localStorage
            localStorage.removeItem('selectedSlotId');
            localStorage.removeItem('selectedTime');
            
            // Redirect to receipt page using the URL from response
            if (data.redirectUrl) {
                window.location.href = data.redirectUrl;
            } else {
                console.error('No redirect URL provided');
            }
        } else {
            throw new Error(data.message || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la confirmation: ' + error.message);
        
        // Réactiver le bouton
        const submitButton = document.getElementById('submit-payment');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réessayer';
        }
    });
}


// Modifier la fonction confirmReservation pour inclure les données des propriétaires
// function confirmReservation(paymentData) {
//     const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
//     if (!csrfToken) {
//         console.error('CSRF token missing');
//         alert('Erreur: Token CSRF manquant');
//         return;
//     }

//     fetch('/reservation/confirm', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/json',
//             'X-CSRF-TOKEN': csrfToken,
//             'Accept': 'application/json',
//             'X-Requested-With': 'XMLHttpRequest'
//         },
//         credentials: 'same-origin',
//         body: JSON.stringify(paymentData)
//     })
//     .then(response => response.json())
//     .then(data => {
//         console.log('Response:', data);
//         if (data.status === 'success') {
//             // Clear localStorage
//             localStorage.removeItem('selectedSlotId');
//             localStorage.removeItem('selectedTime');
            
//             // Show success message
//             alert('Réservation confirmée avec succès!');
            
//             // Redirect to receipt page using the URL from response
//             if (data.redirectUrl) {
//                 window.location.href = data.redirectUrl;
//             } else {
//                 console.error('No redirect URL provided');
//             }
//         } else {
//             throw new Error(data.message || 'Une erreur est survenue');
//         }
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         alert('Erreur lors de la confirmation: ' + error.message);
//     });
// }


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

// Fonction pour mettre à jour le récapitulatif
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
    
    // Générer les champs de propriétaires
    generateOwnerFields();
    
    // Mettre à jour l'acompte
    updateDeposit();
}
