<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Espace Acheteur</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Navigation latérale fixe -->
        <div class="bg-gray-800 text-white w-64 flex-shrink-0 flex flex-col h-screen">
            <div class="flex items-center justify-center py-6 border-b border-gray-700">
                <svg class="h-8 w-8 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xl font-semibold">Espace Acheteur</span>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <!-- Tableau de bord -->
                <a href="{{ route('dashboard') }}" class="flex items-center py-2 px-4 rounded transition-colors mb-3 {{ request()->routeIs('dashboard') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    <span>Tableau de bord</span>
                </a>
                
                <!-- Réservations -->
                <div class="mb-3">
                    <div class="flex items-center py-2 px-4 text-gray-300 text-xs uppercase font-semibold">
                        <span>Réservations</span>
                    </div>
                    
                    <a href="{{ route('reservation.index') }}" class="flex items-center py-2 px-4 rounded transition-colors mb-1 ml-2 {{ request()->routeIs('reservation.index') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                        <i class="fas fa-plus-circle mr-3"></i>
                        <span>Nouvelle réservation</span>
                    </a>
                    
                    <a href="{{ route('dashboard') }}?tab=mes-reservations" class="flex items-center py-2 px-4 rounded transition-colors mb-1 ml-2 {{ request()->has('tab') && request()->get('tab') == 'mes-reservations' ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                        <i class="fas fa-list mr-3"></i>
                        <span>Mes réservations</span>
                    </a>
                </div>
                
                <!-- Aide/Informations -->
                <div class="mb-3">
                    <div class="flex items-center py-2 px-4 text-gray-300 text-xs uppercase font-semibold">
                        <span>Informations</span>
                    </div>
                    
                    <a href="{{ route('help.index') }}" class="flex items-center py-2 px-4 rounded transition-colors mb-1 ml-2 hover:bg-gray-700">
                        <i class="fas fa-info-circle mr-3"></i>
                        <span>Aide & FAQ</span>
                    </a>
                    
                    <a href="{{ route('contacts.index') }}" class="flex items-center py-2 px-4 rounded transition-colors mb-1 ml-2 hover:bg-gray-700">
                        <i class="fas fa-phone mr-3"></i>
                        <span>Contacts</span>
                    </a>
                </div>

            </nav>
            
            <div class="border-t border-gray-700 p-3">
                <a href="{{ route('profile.edit') }}" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 transition-colors mb-1 {{ request()->routeIs('profile.edit') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-user-cog mr-3"></i>
                    <span>Mon profil</span>
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); this.closest('form').submit();" 
                       class="flex items-center py-2 px-4 rounded hover:bg-gray-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        <span>Déconnexion</span>
                    </a>
                </form>
            </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- En-tête fixe -->
            <header class="bg-white dark:bg-gray-800 shadow z-10">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        @yield('header', 'Tableau de bord')
                    </h2>
                    
                    <div class="flex items-center">
                        <span class="text-gray-600 dark:text-gray-300 mr-2">{{ Auth::user()->name }}</span>
                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ Auth::user()->name }}">
                    </div>
                </div>
            </header>

            <!-- Contenu avec défilement -->
            <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-200 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 px-4 py-2 bg-red-100 border border-red-200 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @yield('scripts')
</body>
</html>