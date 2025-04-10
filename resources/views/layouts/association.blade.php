<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Dashboard Association</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Navigation latérale -->
        <div class="flex">
            <div class="bg-gray-800 text-white w-64 flex flex-col min-h-screen py-4 px-4">
                <div class="flex items-center justify-center mb-8">
                    <svg class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-xl font-semibold">Association</span>
                </div>
                
                <nav class="flex-1">
                    <a href="{{ route('association.dashboard') }}" class="flex items-center py-2 px-4 rounded transition-colors {{ request()->routeIs('association.dashboard') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        <span>Tableau de bord</span>
                    </a>
                    
                    <a href="{{ route('association.buyers') }}" class="flex items-center py-2 px-4 mt-2 rounded transition-colors {{ request()->routeIs('association.buyers*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                        <i class="fas fa-users mr-3"></i>
                        <span>Acheteurs</span>
                    </a>
                    
                    <a href="{{ route('association.reservations') }}" class="flex items-center py-2 px-4 mt-2 rounded transition-colors {{ request()->routeIs('association.reservations') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                        <i class="fas fa-calendar-check mr-3"></i>
                        <span>Réservations</span>
                    </a>
                    
                    <a href="{{ route('association.quotas') }}" class="flex items-center py-2 px-4 mt-2 rounded transition-colors {{ request()->routeIs('association.quotas') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                        <i class="fas fa-chart-pie mr-3"></i>
                        <span>Quotas</span>
                    </a>
                </nav>
                
                <div class="mt-auto">
                    <a href="{{ route('profile.edit') }}" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 transition-colors">
                        <i class="fas fa-user-cog mr-3"></i>
                        <span>Profil</span>
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 transition-colors">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            <span>Déconnexion</span>
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="flex-1">
                <!-- En-tête -->
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                            @yield('header')
                        </h2>
                        
                        <div class="flex items-center">
                            <span class="text-gray-600 dark:text-gray-300 mr-2">{{ Auth::user()->name }}</span>
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ Auth::user()->name }}">
                        </div>
                    </div>
                </header>

                <!-- Page content -->
                <main class="py-6">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
    </div>
    @yield('scripts')
</body>
</html>