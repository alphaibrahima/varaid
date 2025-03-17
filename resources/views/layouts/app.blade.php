<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Eid Reservation') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        {{-- <script src="https://js.stripe.com/v3/"></script> --}}


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/reservation.js', 'resources/js/app.js'])
        
        <!-- Bootstrap CSS -->
        {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> --}}
        <!-- Bootstrap Icons -->
        {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet"> --}}
        
    </head>
    @yield('scripts')

    <body class="font-sans antialiased">
        <div class="min-vh-100 bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>

        
 
        <script src="https://js.stripe.com/v3/"></script>
        {{-- <script>
            window.STRIPE_PUBLISHABLE_KEY = '{{ config('services.stripe.key') }}';            
            window.STORE_RESERVATION_URL = '{{ route('reservations.store') }}';
        </script> --}}
    </body>
</html>