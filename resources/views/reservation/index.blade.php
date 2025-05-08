<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Réservation d'Agneau pour l'Eid</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="{{asset('css/template/style.css')}}">
</head>
<body>
    <div class="d-flex flex-column flex-md-row">
        <!-- Menu latéral -->
        <div class="bg-dark text-white" style="width: 280px; min-height: 100vh;">
            <div class="d-flex flex-column h-100 py-3">
                <div class="px-3 mb-3 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                    <span class="fs-4">Réservation Varaid</span>
                </div>
                <hr class="text-white-50">
                <ul class="nav nav-pills flex-column mb-auto px-3">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-white">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reservation.index') }}" class="nav-link active">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Nouvelle réservation
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard') }}?tab=mes-reservations" class="nav-link text-white">
                            <i class="bi bi-list-check me-2"></i>
                            Mes réservations
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tutorials') }}" class="nav-link text-white">
                            <i class="bi bi-play-circle me-2"></i>
                            Tutoriels
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}" class="nav-link text-white">
                            <i class="bi bi-person-circle me-2"></i>
                            Mon profil
                        </a>
                    </li>
                </ul>
                
                <!-- Profil utilisateur -->
                <div class="px-3 py-2 mt-auto">
                    <div class="d-flex align-items-center mt-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="rounded-circle me-2" width="32" height="32">
                        <div>
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="btn btn-link text-white-50 p-0 text-decoration-none btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="flex-grow-1 bg-light" style="min-height: 100vh;">
            <!-- Barre responsive pour mobile uniquement -->
            <div class="d-md-none bg-dark text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-4">Réservation</span>
                    <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMobile">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
            
            <div class="container py-5">
                <div class="page-container">
                    <h2 class="text-center mb-5">
                        Réservez votre agneau pour l'Aïd Al Adha 2025
                    </h2>
                    
                    <!-- Indicateur de progression -->
                    <div class="step-indicator mb-5">
                        <div class="step-progress" id="step-progress"></div>
                        <div class="step-dots">
                            <div class="step-dot active" data-step="1"></div>
                            <div class="step-dot" data-step="2"></div>
                            <div class="step-dot" data-step="3"></div>
                            <div class="step-dot" data-step="4"></div>
                        </div>
                    </div>


                    <!-- Alertes spécifiques à chaque étape -->
                    <div id="alert-step-1" class="alert alert-warning mb-4 step-alert">
                        <i class="bi bi-info-circle"></i> 
                        En confirmant votre choix, vous recevrez une notification par sms et par mail détaillant vos rendez-vous.
                        <ul>
                            <li class="fw-bold fst-italic">
                                En venant choisir mon agneau le samedi 31 mai, le sacrifice se fera le 1er jour de l’Aïd.
                            </li>
                            <li class="fw-bold fst-italic">
                                En venant choisir mon agneau le dimanche 1er juin, le sacrifice se fera le 2ème jour de l’Aïd.
                            </li>
                        </ul>
                    </div>
                    
                    <div id="alert-step-2" class="alert alert-info mb-4 step-alert" style="display: none;">
                        <i class="bi bi-info-circle"></i>
                        Votre horaire d'abattage sera identique au créneau choisi. 
                    </div>
                    
                    <div id="alert-step-3" class="alert alert-danger mb-4 step-alert" style="display: none;">
                        <i class="bi bi-info-circle"></i>
                        Un acompte de 50€ par réservataire est obligatoire. Acompte non remboursable.
                    </div>
                    
                    <div id="alert-step-4" class="alert alert-info mb-4 step-alert" style="display: none;">
                        <i class="bi bi-info-circle"></i>
                        En confirmant cette réservation, vous acceptez que l'heure choisie soit la même pour le jour de votre choix et pour l'abattement de l'agneau. Une notification vous sera envoyée par mail et par SMS avec les détails.
                    </div>
                    
                    <!-- Étape 1: Sélection du jour -->
                    @include('reservation.partials.step1')
                    
                    <!-- Étape 2: Sélection de l'heure -->
                    @include('reservation.partials.step2')
                    
                    <!-- Étape 3: Configuration de la commande -->
                    @include('reservation.partials.step3')
                    
                    <!-- Étape 4: Paiement et confirmation -->
                    @include('reservation.partials.step4')
                    <!-- fin etape 4 -->
                    
                    <!-- Modal de confirmation -->
                    @include('reservation.partials.modal')

                    <!-- Modal des conditions générales -->
                    @include('reservation.partials.terms-modal')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menu mobile (offcanvas) -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="menuMobile">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body bg-dark text-white p-0">
            <div class="d-flex flex-column h-100 py-3">
                <ul class="nav nav-pills flex-column mb-auto px-3">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-white">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reservation.index') }}" class="nav-link active">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Nouvelle réservation
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard') }}?tab=mes-reservations" class="nav-link text-white">
                            <i class="bi bi-list-check me-2"></i>
                            Mes réservations
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tutorials') }}" class="nav-link text-white">
                            <i class="bi bi-play-circle me-2"></i>
                            Tutoriels
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}" class="nav-link text-white">
                            <i class="bi bi-person-circle me-2"></i>
                            Mon profil
                        </a>
                    </li>
                </ul>
                
                <!-- Profil utilisateur -->
                <div class="px-3 py-2 mt-auto">
                    <div class="d-flex align-items-center mt-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="rounded-circle me-2" width="32" height="32">
                        <div>
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="btn btn-link text-white-50 p-0 text-decoration-none btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservation.js') }}?v={{ time() }}"></script>
    <script>console.log("URL JS:", "{{ asset('js/reservation.js') }}?v={{ time() }}");</script>

    <script>
        // Make CSRF token and user info globally available
        window.csrf_token = '{{ csrf_token() }}';
        window.userInfo = {
            full_address: '{{ $user->full_address }}',
            firstName: '{{ $user->firstname}}',
            lastName: '{{ $user->name }}',
            email: '{{ $user->email }}',
            phone: '{{ $user->phone }}'
        };
    </script>
</body>
</html>