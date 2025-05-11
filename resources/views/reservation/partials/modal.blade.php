<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Réservation confirmée !</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="display-1 text-success mb-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h4>Votre réservation a été enregistrée avec succès</h4>
                    <p class="text-muted">Un email et un SMS de confirmation vont vous être envoyés.</p>
                </div>
                <div class="card bg-light">
                    <div class="card-body">
                        <h5>Détails de votre réservation :</h5>
                        <p><strong>Numéro de réservation :</strong> <span id="confirmation-number">R-12345</span></p>
                        <p><strong>Jour :</strong> <span id="confirmation-day">Mardi 11 Mars 2025</span></p>
                        <p><strong>Heure :</strong> <span id="confirmation-time">09:30</span></p>
                        <p><strong>Association :</strong> <span id="confirmation-assoc">Mosquée de la Paix</span></p>
                        <!-- Ajouter dans les détails de la réservation -->
                        <p><strong>Sélection:</strong> <span id="confirmation-selection">Sélection sur place</span></p>                        <p><strong>Quantité :</strong> <span id="confirmation-quantity">1</span></p>
                        <p><strong>Acompte payé :</strong> <span class="text-success">100,00 €</span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-outline-secondary">Voir mes réservations</a>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>