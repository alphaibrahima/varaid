<div class="modal fade" id="affiliationModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Vérification d'affiliation</h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="display-1 text-primary mb-3">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h4>Vérifiez votre affiliation</h4>
                    <p class="text-muted">Pour accéder aux réservations, veuillez entrer le code d'affiliation qui vous a été envoyé par email et SMS.</p>
                </div>
                
                <div id="affiliation-alert" class="alert d-none" role="alert"></div>
                
                <form id="affiliation-form">
                    <div class="mb-3">
                        <label for="affiliation_code" class="form-label">Code d'affiliation</label>
                        <input type="text" class="form-control" id="affiliation_code" placeholder="Entrez votre code" required>
                        <div class="form-text">Le code est composé de 6 caractères alphanumériques.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="verify-code-btn">
                            Vérifier le code
                        </button>
                        {{-- <button type="button" class="btn btn-outline-secondary" id="resend-code-btn">
                            Recevoir un nouveau code
                        </button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>