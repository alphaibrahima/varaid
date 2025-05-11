<!-- resources/views/reservation/partials/step4.blade.php -->
<div class="step" id="step-4">
    <h3 class="mb-4">Étape 4: Confirmation finale</h3>
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
                    <p><strong>Sélection:</strong> <span id="recap-selection">Sélection sur place</span></p>
                    <p><strong>Quantité:</strong> <span id="recap-quantity">1</span></p>
                    <p><strong>Acompte déjà payé:</strong> <span class="text-primary fw-bold" id="recap-deposit">50,00 €</span></p>
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
            
            <div class="alert alert-info mt-3">
                <small>
                    <i class="bi bi-info-circle"></i>
                    Acompte obligatoire déjà payé. 
                    Solde à régler après avoir choisi votre (vos) agneau(x) sur le site.
                </small>
            </div>
            
            <div class="d-grid mt-4">
                <button type="button" id="submit-reservation" class="btn btn-success py-3" onclick="confirmReservation()">
                    Confirmer la réservation
                </button>
            </div>
        </div>
    </div>
</div>