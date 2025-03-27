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
        updateDeposit(); // Mettre à jour l'acompte
        updatePaymentButton(); // Mettre à jour le texte du bouton
        initStripe();
        updateRecap();
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

// Incrémentation de la quantité
function incrementQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (parseInt(quantityInput.value) < parseInt(quantityInput.max)) {
        selectedQuantity = parseInt(quantityInput.value) + 1;
        quantityInput.value = selectedQuantity;
        document.getElementById('recap-quantity').textContent = selectedQuantity;
        document.getElementById('confirmation-quantity').textContent = selectedQuantity;
        updateDeposit(); // Mettre à jour l'acompte
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

// Variables Stripe
let stripe;
let card;

// Initialisation de Stripe
function initStripe() {
    // Remplacez 'pk_test_votreClePubliqueStripe' par votre clé publique Stripe
    stripe = Stripe('pk_test_votreClePubliqueStripe');
    const elements = stripe.elements();
    
    // Style pour l'élément de carte
    const style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    
    // Si un élément de carte existe déjà, on ne le recrée pas
    if (!card) {
        // Création de l'élément de carte
        card = elements.create('card', {
            style: style,
            hidePostalCode: true  // On cache le code postal pour simplifier
        });
        
        // Montage de l'élément de carte dans le DOM
        card.mount('#card-element');
        
        // Gestion des erreurs de saisie carte
        card.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // Gestion du bouton de paiement
        document.getElementById('submit-payment').addEventListener('click', processPayment);
    }
}

// Fonction pour traiter le paiement
function processPayment(event) {
    if (event) event.preventDefault();
    
    const submitButton = document.getElementById('submit-payment');
    const cardholderName = document.getElementById('cardholder-name').value;
    const cardholderEmail = document.getElementById('cardholder-email').value;


    // const cardholderAssoci = document.getElementById('recap-assoc').value;

    // console.log("Nom sur la carte:", cardholderName);
    // console.log("Association:", cardholderAssoci);
    // console.log("Email:", cardholderEmail);
    
    // Validation de base
    if (!cardholderName || !cardholderEmail) {
        alert('Veuillez remplir tous les champs');
        return;
    }
    
    // Désactiver le bouton pendant le traitement
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
    
    // Dans un environnement de production, vous devriez faire un appel à votre serveur
    // pour créer un PaymentIntent et récupérer le client_secret
    
    // Simulation d'une requête et d'une réponse
    setTimeout(function() {
        // Simuler un appel réussi à Stripe
        submitButton.disabled = false;
        submitButton.innerHTML = 'Confirmer et payer l\'acompte de 100€';
        
        // Dans un environnement réel, vous utiliseriez ce code:
        /*
        stripe.confirmCardPayment('client_secret_de_votre_serveur', {
            payment_method: {
                card: card,
                billing_details: {
                    name: cardholderName,
                    email: cardholderEmail
                }
            }
        }).then(function(result) {
            if (result.error) {
                // Afficher l'erreur
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                submitButton.disabled = false;
                submitButton.innerHTML = 'Confirmer et payer l\'acompte de 200€';
            } else {
                // Paiement réussi
                confirmReservation();
            }
        });
        */
        
        // Pour l'exemple, nous allons directement confirmer la réservation
        confirmReservation();
    }, 2000);
}


// Confirmation de la réservation
function confirmReservation() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const slotId = localStorage.getItem('selectedSlotId');
    
    if (!csrfToken) {
        console.error('CSRF token missing');
        alert('Erreur: Token CSRF manquant');
        return;
    }

    if (!slotId) {
        alert('Veuillez sélectionner un créneau horaire');
        goToStep(2);
        return;
    }

    const data = {
        reservationNumber: 'R-' + Math.floor(100000 + Math.random() * 900000),
        cardholderName: document.getElementById('cardholder-name').value,
        cardholderEmail: document.getElementById('cardholder-email').value,
        cardholderAssoci: document.getElementById('recap-assoc').textContent,
        slotId: parseInt(slotId),
        quantity: parseInt(document.getElementById('quantity').value) || 1
    };

    // Debug log
    console.log('Sending request:', {
        url: '/reservation/confirm',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        data: data
    });

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
    .then(async response => {
        const text = await response.text();
        console.log('Raw response:', text);
        
        try {
            const data = JSON.parse(text);
            if (!response.ok) {
                throw new Error(data.message || 'Server error');
            }
            return data;
        } catch (e) {
            console.error('JSON Parse error:', e);
            throw new Error('Invalid server response');
        }
    })
    .then(data => {
        console.log('Success:', data);
        if (data.status === 'success') {
            localStorage.removeItem('selectedSlotId');
            localStorage.removeItem('selectedTime');
            alert('Réservation confirmée avec succès!');
            window.location.href = '/dashboard';
        } else {
            throw new Error(data.message || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('submit-payment').disabled = false;
        alert('Erreur lors de la confirmation: ' + error.message);
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

// Fonction pour mettre à jour le récapitulatif
function updateRecap() {
    const selectedTime = localStorage.getItem('selectedTime');
    const selectedSize = localStorage.getItem('selectedSize') || 'grand';
    const quantity = document.getElementById('quantity').value;

    document.getElementById('recap-slot').textContent = selectedTime || 'Non sélectionné';
    document.getElementById('recap-size').textContent = selectedSize.charAt(0).toUpperCase() + selectedSize.slice(1);
    document.getElementById('recap-quantity').textContent = quantity;
}
