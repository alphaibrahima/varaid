<div class="step" id="step-3">
    <h3 class="mb-4">Étape 3: Configurez votre commande <small class="text-muted" id="selected-time"></small></h3>
    <div class="d-flex mb-3">
        <button class="btn btn-outline-secondary me-2" onclick="goToStep(2)">
            <i class="bi bi-arrow-left"></i> Retour
        </button>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Quantité</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">−</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="4" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">+</button>
                    </div>
                    <small class="form-text text-muted">Maximum 4 agneaux par réservation</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Taille</label>
                    <div class="d-flex">
                        <div class="card me-2 flex-grow-1 taille-option selected" onclick="selectSize('grand')">
                            <div class="card-body text-center">
                                <h6 class="card-title">Grand</h6>
                                <p class="card-text">~25kg</p>
                            </div>
                        </div>
                        <div class="card flex-grow-1 taille-option" onclick="selectSize('moyen')">
                            <div class="card-body text-center">
                                <h6 class="card-title">Moyen</h6>
                                <p class="card-text">~18kg</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <button class="btn btn-primary w-100" onclick="goToStep(4)">Continuer vers le paiement</button>
                </div>
            </div>
        </div>
    </div>
</div>