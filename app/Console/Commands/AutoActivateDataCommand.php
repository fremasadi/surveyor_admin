<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoActivateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:auto-activate {--dry-run : Run without actually updating data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mengaktifkan status data harian menjadi true setelah jam 12 siang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $now = Carbon::now();
        $today = Carbon::today();
        
        // Cek apakah sekarang sudah lewat jam 12 siang
        $cutoffTime = $today->copy()->setTime(12, 0, 0); // 12:00 PM
        
        $this->info("=== Auto Activate Data Command ===");
        $this->info("Waktu sekarang: " . $now->format('Y-m-d H:i:s'));
        $this->info("Batas waktu aktivasi: " . $cutoffTime->format('Y-m-d H:i:s'));
        
        if ($now->lt($cutoffTime)) {
            $this->warn("Belum waktunya untuk mengaktifkan data. Aktivasi akan dilakukan setelah jam 12:00 siang.");
            return 0;
        }
        
        // Ambil data hari ini yang statusnya masih false
        $dataToActivate = DataHarian::whereDate('created_at', $today)
            ->where('status', false)
            ->get();
            
        $totalData = $dataToActivate->count();
        
        if ($totalData === 0) {
            $this->info("Tidak ada data yang perlu diaktifkan untuk hari ini.");
            return 0;
        }
        
        $this->info("Ditemukan {$totalData} data yang akan diaktifkan:");
        
        // Tampilkan preview data yang akan diaktifkan
        $this->table(
            ['ID', 'Komoditas', 'Data Input', 'Created At', 'Status'],
            $dataToActivate->map(function ($data) {
                return [
                    $data->id,
                    $data->komoditas->name ?? 'N/A',
                    number_format($data->data_input, 0, ',', '.'),
                    $data->created_at->format('Y-m-d H:i:s'),
                    $data->status ? 'true' : 'false'
                ];
            })->toArray()
        );
        
        if ($isDryRun) {
            $this->warn("DRY RUN MODE: Data tidak akan diubah.");
            $this->info("Gunakan command tanpa --dry-run untuk benar-benar mengaktifkan data.");
            return 0;
        }
        
        // Konfirmasi sebelum mengaktifkan
        if (!$this->confirm("Apakah Anda yakin ingin mengaktifkan {$totalData} data ini?")) {
            $this->info("Operasi dibatalkan.");
            return 0;
        }
        
        // Progress bar untuk update
        $progressBar = $this->output->createProgressBar($totalData);
        $progressBar->start();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($dataToActivate as $data) {
            try {
                $data->update(['status' => true]);
                $successCount++;
                
                // Log aktivitas
                Log::info("Data ID {$data->id} berhasil diaktifkan", [
                    'komoditas_id' => $data->komoditas_id,
                    'data_input' => $data->data_input,
                    'activated_at' => $now->toDateTimeString()
                ]);
                
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Gagal mengaktifkan data ID {$data->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Ringkasan hasil
        $this->info("=== RINGKASAN HASIL ===");
        $this->info("✅ Berhasil diaktifkan: {$successCount} data");
        
        if ($errorCount > 0) {
            $this->error("❌ Gagal diaktifkan: {$errorCount} data");
        }
        
        $this->info("📊 Total data aktif hari ini: " . 
            DataHarian::whereDate('created_at', $today)
                ->where('status', true)
                ->count()
        );
        
        // Kirim notifikasi jika ada
        $this->sendNotification($successCount, $errorCount);
        
        return 0;
    }
    
    /**
     * Kirim notifikasi hasil aktivasi
     */
    private function sendNotification($successCount, $errorCount)
    {
        $message = "Auto Activate Data - " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $message .= "✅ Berhasil: {$successCount} data\n";
        
        if ($errorCount > 0) {
            $message .= "❌ Gagal: {$errorCount} data\n";
        }
        
        // Log notifikasi
        Log::info("Auto Activate Data Summary", [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_active_today' => DataHarian::whereDate('created_at', Carbon::today())
                ->where('status', true)
                ->count()
        ]);
        
        // TODO: Implementasi notifikasi (email, Slack, dll) jika diperlukan
        // Contoh: Mail::to('admin@example.com')->send(new DataActivatedNotification($message));
    }
}