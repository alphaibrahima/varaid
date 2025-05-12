<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AcheteurResource;
use App\Filament\Resources\AssociationResource;
use App\Filament\Resources\QuotaResource;
use App\Filament\Resources\SlotResource;
use App\Filament\Resources\ReservationResource;
use App\Filament\Resources\AdminReservationResource;
use App\Filament\Resources\SmsMessageResource;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\SendSms;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Gestion des Réservations')
            ->favicon(asset('images/favicon.ico'))
            ->colors([
                'primary' => Color::Sky,
                'secondary' => Color::Emerald,
            ])
            ->navigationGroups([
                'Utilisateurs',
                'Gestion des Créneaux',
                'Réservations', 
                'Paramètres',
                'Communication',
            ])
            ->resources([
                AcheteurResource::class,
                AssociationResource::class,
                QuotaResource::class,
                SlotResource::class,
                ReservationResource::class, 
                SmsMessageResource::class,
                // AdminReservationResource::class,
                \App\Filament\Resources\AdminReservationResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                Dashboard::class,
                SendSms::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->widgets([
                 \App\Filament\Widgets\ReservationsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseTransactions()
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k']);
    }
}