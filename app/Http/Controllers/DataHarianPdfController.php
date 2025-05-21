<?php

namespace App\Http\Controllers;

use App\Models\KomoditasData;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataHarianPdfController extends Controller
{
    public function cetakPdf(Request $request)
    {
        try {
            $tanggal = $request->tanggal;

            // Validasi format tanggal
            if (!$tanggal) {
                return response()->json(['error' => 'Tanggal tidak boleh kosong'], 400);
            }

            // Log format tanggal yang diterima
            Log::info("Format tanggal asli dari request: " . $tanggal);

            // Pastikan format tanggal konsisten dengan Carbon
            try {
                $parsedTanggal = Carbon::parse($tanggal)->format('Y-m-d');
                Log::info("Tanggal setelah diparse: " . $parsedTanggal);
            } catch (Exception $e) {
                Log::error("Gagal parsing tanggal: " . $e->getMessage());
                return response()->json(['error' => 'Format tanggal tidak valid'], 400);
            }

            // Mengambil data dengan eager loading
            $data = KomoditasData::with(['user', 'responden', 'komoditas'])
                ->whereDate('tanggal', $parsedTanggal)
                ->get();

            // Log jumlah data yang diambil untuk debugging
            Log::info("Mencetak PDF untuk tanggal: $parsedTanggal, jumlah data: " . $data->count());
            
            // Log raw query untuk debugging
            $query = KomoditasData::with(['user', 'responden', 'komoditas'])
                ->whereDate('tanggal', $parsedTanggal)
                ->toSql();
            Log::info("Raw SQL query: " . $query);

            // Menambahkan debug info ke view
            $debug = [
                'tanggal' => $parsedTanggal,
                'tanggal_asli' => $tanggal,
                'jumlah_data' => $data->count(),
                'query' => $query
            ];

            // Konfigurasi tambahan untuk DomPDF
            $options = [
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'dpi' => 150,
                'debugCss' => true, // Tambahkan debug CSS
                'debugLayout' => true, // Tambahkan debug layout
            ];

            $pdf = Pdf::loadView('pdf.data-harian', compact('data', 'tanggal', 'debug'))
                ->setOptions($options);

            // Tambahkan header untuk menghindari caching
            return $pdf->stream("data-harian-{$parsedTanggal}.pdf", [
                'Attachment' => false,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (Exception $e) {
            // Log error untuk debugging
            Log::error('Error saat generate PDF: ' . $e->getMessage());
            Log::error('Error stack trace: ' . $e->getTraceAsString());

            // Return error page yang informatif
            return response()->view('errors.pdf-error', [
                'message' => $e->getMessage(),
                'tanggal' => $request->tanggal ?? 'tidak ada',
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}