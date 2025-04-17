<div class="step" id="step-3">
    <h3 class="mb-4">Étape 3: Configurez votre commande <small class="text-muted" id="selected-time"></small></h3>
    <div class="d-flex mb-3">
        <button class="btn btn-outline-secondary me-2" onclick="goToStep(2)">
            <i class="bi bi-arrow-left"></i> Retour
        </button>
    </div>
    
    <!-- Alerte de limite de réservation -->
    @if(isset($userReservationsCount) && $userReservationsCount > 0)
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-4"></i>
            <div>
                <p class="mb-0">Vous avez déjà réservé <strong>{{ $userReservationsCount }}</strong> agneau(x).</p>
                @if($remainingReservations > 0)
                <p class="mb-0">Vous pouvez encore réserver <strong>{{ $remainingReservations }}</strong> agneau(x) sur le maximum de 4 autorisés.</p>
                @else
                <p class="mb-0 text-danger">Vous avez atteint la limite de 4 agneaux réservés.</p>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Quantité</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">−</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="{{ $remainingReservations ?? 4 }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">+</button>
                    </div>
                    <small class="form-text text-muted">Maximum {{ $remainingReservations ?? 4 }} agneau(x) restant(s) sur votre limite de 4</small>
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
                    <button class="btn btn-primary w-100" onclick="goToStep(4)" {{ $remainingReservations <= 0 ? 'disabled' : '' }}>Continuer vers le paiement</button>
                </div>
            </div>
        </div>
    </div>
</div>