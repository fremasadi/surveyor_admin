<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // Jalankan command auto-activate setiap hari pada jam 12:00 siang
        $schedule->command('data:auto-activate')
            ->dailyAt('12:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Auto activate data command berhasil dijalankan pada ' . now());
            })
            ->onFailure(function () {
                \Log::error('Auto activate data command gagal dijalankan pada ' . now());
            });
            
        // Alternatif: Jalankan setiap jam setelah jam 12 siang (jika diperlukan)
        // $schedule->command('data:auto-activate')
        //     ->hourly()
        //     ->between('12:00', '23:59')
        //     ->timezone('Asia/Jakarta')
        //     ->withoutOverlapping();
            
        // Jalankan dry-run setiap hari jam 11:30 untuk preview
        $schedule->command('data:auto-activate --dry-run')
            ->dailyAt('11:30')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Auto activate data dry-run berhasil dijalankan pada ' . now());
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
