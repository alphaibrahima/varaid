
<style>
.terms-content {
    max-height: 300px;
    overflow-y: auto;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

#termsModal .modal-dialog {
    max-width: 600px;
}

#termsModal .form-check {
    padding: 15px;
    background-color: rgba(0, 123, 255, 0.1);
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
</style>

<div class="modal fade" id="termsModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Conditions générales de vente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="fw-bold">Veuillez lire attentivement les conditions suivantes avant de confirmer votre réservation :</h6>
                </div>
                
                <div class="terms-content">
                    <p><strong>Dans le cadre des conditions générales de vente :</strong></p>
                    <ul class="list-unstyled">
                        <li class="mb-2">• Je m'engage à respecter les jours et horaires choisis pour la sélection et le sacrifice de mon agneau.</li>
                        
                        <li class="mb-2">• Le non-respect des jours et horaires de rendez-vous entraînera une prise en charge ultérieure de la commande.</li>
                        
                        <li class="mb-2">• Je m'engage à informer l'association Varaïd si je ne souhaite pas choisir moi-même mon agneau sur le site d'abattage à Hyères. Dans ce cas, j'accepte que l'association Varaïd sélectionne un agneau pour moi, en respectant le prix convenu parmi les offres proposées.</li>
                        
                        <li class="mb-2">• Je m'engage à verser un acompte lors de mon inscription, et je sais que celui-ci est non remboursable.</li>
                        
                        <li class="mb-2">• En me rendant sur le site, je m'engage à choisir mon agneau parmi les disponibilités restantes.</li>
                        
                        <li class="mb-2">• Je suis conscient(e) que, selon mon jour et mon créneau de rendez-vous, mon choix peut être restreint.</li>
                        
                        <li class="mb-2">• Je sais que l'absence de choix via la plateforme Varaïd, ou l'absence lors des jours de sélection sur le site d'abattage, dans les délais impartis, entraînera la perte de mon acompte et la mise en vente de ma réservation.</li>
                    </ul>
                </div>
                
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="acceptTerms">
                    <label class="form-check-label" for="acceptTerms">
                        J'ai lu et j'accepte les conditions générales de vente
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="confirmTerms" disabled>Accepter et continuer</button>
            </div>
        </div>
    </div>
</div>