<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
            if (session()->has('error')) {
                Notification::make()
                    ->title('Login Gagal')
                    ->body(session('error'))
                    ->danger()
                    ->persistent()
                    ->send();
            }
        });
    }
}
