<div class="step" id="step-4">
    <h3 class="mb-4">Étape 4: Confirmation & Paiement d'acompte</h3>
    <div class="d-flex mb-3">
        <button class="btn btn-outline-secondary me-2" onclick="goToStep(3)">
            <i class="bi bi-arrow-left"></i> Retour
        </button>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Récapitulatif de votre réservation</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Jour:</strong> <span id="recap-day">Mardi 11 Mars 2025</span></p>
                    <p><strong>Heure:</strong> <span id="recap-time">09:30</span></p>
                    <p><strong>Association/Mosquée:</strong> <span id="recap-assoc">{{ $userAssociation }}</span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Taille:</strong> <span id="recap-size">Grand (~25kg)</span></p>
                    <p><strong>Quantité:</strong> <span id="recap-quantity">1</span></p>
                    <p><strong>Acompte à payer:</strong> <span class="text-primary fw-bold" id="recap-deposit">100,00 €</span></p>
                </div>
            </div>
            <hr>
            
            <!-- Nouvelle section: Informations des propriétaires -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="mb-3">Informations des propriétaires</h5>
                    <p class="text-muted mb-3">Veuillez indiquer les noms et prénoms des propriétaires pour chaque agneau.</p>
                    
                    <div id="owners-container">
                        <!-- Les champs seront générés dynamiquement ici -->
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de paiement Stripe -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="mb-3">Informations de paiement</h5>
                    <form id="payment-form" data-csrf="{{ csrf_token() }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cardholder-name" class="form-label">Nom sur la carte</label>
                                <input type="text" class="form-control" id="cardholder-name" placeholder="Mouhamad" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cardholder-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="cardholder-email" placeholder="exemple@email.com" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-12">
                                <label for="payment-element" class="form-label">Informations de paiement</label>
                                <!-- Changé de card-element à payment-element pour correspondre au JS -->
                                <div id="payment-element" class="form-control p-3" style="height: auto; min-height: 150px;"></div>
                                <!-- Changé aussi l'id des erreurs pour correspondre au JS -->
                                <div id="payment-errors" class="text-danger mt-2" role="alert"></div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                Acompte obligatoire et non remboursable. 
                                Solde à régler après avoir choisi votre (vos) agneau(x) sur le site.
                            </small>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" id="submit-payment" class="btn btn-success py-3">
                                Confirmer et payer l'acompte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>