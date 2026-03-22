<?php

namespace App\Jobs;

use App\Models\ArtikelRute;
use App\Models\Rute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProsesArtikelAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $rute;
    public $artikelRuteId;

    // Pekerja ini butuh data Rute dan ID Artikel untuk bekerja
    public function __construct(Rute $rute, $artikelRuteId)
    {
        $this->rute = $rute;
        $this->artikelRuteId = $artikelRuteId;
    }

    public function handle(): void
    {
        // 1. Ubah status menjadi 'processing'
        $artikel = ArtikelRute::find($this->artikelRuteId);
        if (!$artikel) return;
        $artikel->update(['status_generate' => 'processing']);

        // Masukkan Judul ke dalam prompt agar AI menyesuaikan konteks H2/H3
        $prompt = "Anda adalah pakar SEO dan Copywriter perusahaan ekspedisi kargo. 
        Tulis artikel SEO dengan topik/judul utama: '{$artikel->judul}'. 
        Rute ekspedisi ini dari {$this->rute->kota_asal} ke {$this->rute->kota_tujuan}.
        
        WAJIB kembalikan jawaban HANYA dalam format JSON murni. Struktur key:
        {
            \"paragraf_pembuka\": \"Tulis 1 paragraf pembuka yang mengalir natural berdasarkan judul '{$artikel->judul}'. Sebutkan harga Rp" . number_format($this->rute->harga_per_kg, 0, ',', '.') . "/kg dan estimasi " . $this->rute->estimasi_hari . " hari.\",
            \"teks_layanan\": \"Tulis 2-3 paragraf layanan kargo yang kata-katanya disesuaikan dengan intensi dari judul artikel.\",
            \"teks_tips\": \"Tulis 3-4 tips unik.\",
            \"teks_faq\": [
                {\"pertanyaan\": \"Berapa tarif ekspedisi {$this->rute->kota_asal} ke {$this->rute->kota_tujuan}?\", \"jawaban\": \"...\"}
            ]
        }";

        try {
            // 3. Menghubungi API Gemini Google (Menggunakan mesin Gemini 2.5 Pro)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            // 4. Menerima dan Memproses Jawaban AI
            if ($response->successful()) {
                $hasilTeks = $response->json('candidates.0.content.parts.0.text');
                
                // Membersihkan markdown ```json jika Gemini membandel
                $hasilTeks = str_replace(['```json', '```'], '', $hasilTeks);
                $dataJson = json_decode(trim($hasilTeks), true);

                if ($dataJson) {
                    // 5. Simpan ke Brankas Database!
                    $artikel->update([
                        'paragraf_pembuka' => $dataJson['paragraf_pembuka'] ?? null,
                        'teks_layanan' => $dataJson['teks_layanan'] ?? null,
                        'teks_tips' => $dataJson['teks_tips'] ?? null,
                        'teks_faq' => $dataJson['teks_faq'] ?? null,
                        'status_generate' => 'completed'
                    ]);
                } else {
                    $artikel->update(['status_generate' => 'failed']);
                }
            } else {
                $artikel->update(['status_generate' => 'failed']);
                Log::error("Gemini API Error: " . $response->body());
            }

        } catch (\Exception $e) {
            $artikel->update(['status_generate' => 'failed']);
            Log::error("Job AI Error: " . $e->getMessage());
        }
    }
}