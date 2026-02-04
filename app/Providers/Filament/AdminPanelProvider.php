<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            ->brandName('Indikarya Total Solution')
            ->darkMode(false)
->renderHook(
    'panels::head.end',
    fn (): string => '
        <style>
            /* Add logo to login page */
            .fi-simple-main::before {
                content: "";
                display: block;
                width: 100%;
                height: 80px;
                background-image: url("/logo-indikarya.png");
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                margin-bottom: 2rem;
            }
            
            /* Ensure brand name is visible */
            .fi-sidebar-header .fi-sidebar-brand,
            .fi-topbar .fi-topbar-brand {
                display: flex !important;
                align-items: center;
                font-size: 1.125rem;
                font-weight: 600;
                color: inherit;
            }
        </style>
    '
)
->renderHook(
    'panels::head.end',
    fn (): string => '
        <link href="' . asset('css/custom.css') . '" rel="stylesheet">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Inject text "Indikarya Total Solution" ke header
                const brandElements = document.querySelectorAll(".filament-brand, .filament-topbar .filament-brand");
                
                brandElements.forEach(function(element) {
                    // Cek apakah sudah ada text
                    if (!element.querySelector(".indikarya-brand-text")) {
                        // Buat element text baru
                        const textElement = document.createElement("span");
                        textElement.className = "indikarya-brand-text";
                        textElement.textContent = "Indikarya Total Solution";
                        
                        // Insert text setelah logo disembunyikan
                        element.appendChild(textElement);
                    }
                });
            });
        </script>
    ',
)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('Laporan Presensi')
                    ->url('/admin/attendances/report')
                    ->icon('heroicon-o-document-chart-bar')
                    ->group('Laporan')
                    ->sort(10),
                NavigationItem::make('Laporan Task List')
                    ->url('/admin/task-lists/report')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->group('Laporan')
                    ->sort(11),
                NavigationItem::make('Laporan Patroli')
                    ->url('/admin/patrols/report')
                    ->icon('heroicon-o-document-text')
                    ->group('Laporan')
                    ->sort(12),
                NavigationItem::make('Laporan Shift')
                    ->url('/admin/shift-reports/report')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->group('Laporan')
                    ->sort(13),
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

