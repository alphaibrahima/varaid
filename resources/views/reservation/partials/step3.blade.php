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
                    <!-- Ajouter cette div d'alerte -->
                    <div id="quantity-alert" style="display: none;"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Options</label>
                    <div class="card p-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="skipSelection" onchange="toggleSkipSelection()">
                            <label class="form-check-label" for="skipSelection">
                                Ne pas venir choisir mon agneau sur le site
                            </label>
                            <p class="text-muted small mt-2">Si vous cochez cette case, un agneau vous sera attribué aléatoirement.</p>
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