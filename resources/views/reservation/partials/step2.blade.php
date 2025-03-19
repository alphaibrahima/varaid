<div class="step" id="step-2">
    <h3 class="mb-4">Étape 2: Choisissez un créneau horaire <small class="text-muted" id="selected-day"></small></h3>
    <div class="d-flex mb-3">
        <button class="btn btn-outline-secondary me-2" onclick="goToStep(1)">
            <i class="bi bi-arrow-left"></i> Retour
        </button>
    </div>
    <div class="row row-cols-2 row-cols-md-4 g-3" id="time-slots-container">
        <div class="col">
            <div class="card creneaux-heure" onclick="selectTimeSlot('09:00')">
                <div class="card-body text-center">
                    <h5 class="card-title">09:00</h5>
                    <p class="card-text"><small class="text-muted">42 places restantes</small></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card creneaux-heure" onclick="selectTimeSlot('09:30')">
                <div class="card-body text-center">
                    <h5 class="card-title">09:30</h5>
                    <p class="card-text"><small class="text-muted">38 places restantes</small></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card creneaux-heure" onclick="selectTimeSlot('10:00')">
                <div class="card-body text-center">
                    <h5 class="card-title">10:00</h5>
                    <p class="card-text"><small class="text-muted">45 places restantes</small></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card creneaux-heure" onclick="selectTimeSlot('10:30')">
                <div class="card-body text-center">
                    <h5 class="card-title">10:30</h5>
                    <p class="card-text"><small class="text-muted">29 places restantes</small></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card creneaux-heure" onclick="selectTimeSlot('11:00')">
                <div class="card-body text-center">
                    <h5 class="card-title">11:00</h5>
                    <p class="card-text"><small class="text-muted">50 places restantes</small></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card creneaux-heure" onclick="selectTimeSlot('11:30')">
                <div class="card-body text-center">
                    <h5 class="card-title">11:30</h5>
                    <p class="card-text"><small class="text-muted">47 places restantes</small></p>
                </div>
            </div>
        </div>
    </div>
</div>