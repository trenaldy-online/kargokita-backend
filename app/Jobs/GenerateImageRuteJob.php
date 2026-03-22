<?php

namespace App\Jobs;

use App\Models\Rute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateImageRuteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ruteId;
    public $promptTemplate;
    public $forceRegenerate;

    public function __construct($ruteId, $promptTemplate, $forceRegenerate = false)
    {
        $this->ruteId = $ruteId;
        $this->promptTemplate = $promptTemplate;
        $this->forceRegenerate = $forceRegenerate;
    }

    public function handle()
    {
        $rute = Rute::find($this->ruteId);
        if (!$rute) return;

        // Skip jika sudah ada gambar DAN tidak dipaksa regenerate
        if ($rute->image_path && !$this->forceRegenerate) {
            return; 
        }

        // 1. Siapkan Prompt Dinamis (Replace Variabel)
        $finalPrompt = str_replace(
            ['[kota_asal]', '[kota_tujuan]'], 
            [$rute->kota_asal, $rute->kota_tujuan], 
            $this->promptTemplate
        );

        try {
            $apiKey = env('GEMINI_API_KEY'); 
            
            // PERUBAHAN 1: Menggunakan model Gemini Flash Image (Nano Banana) dan endpoint standar generateContent
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-image-preview:generateContent?key={$apiKey}";
            
            // Trik rahasia Google: Masukkan instruksi rasio langsung ke dalam teks prompt
            $promptWithRatio = $finalPrompt . " (16:9 aspect ratio, cinematic logistics photography)";

            // PERUBAHAN 2: Format Payload sekarang persis seperti kita chat teks dengan Gemini
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $promptWithRatio]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $base64Image = null;
                $parts = $response->json('candidates.0.content.parts');

                // LOOP CERDAS: Cari bagian mana yang mengandung gambar (inlineData)
                if (is_array($parts)) {
                    foreach ($parts as $part) {
                        if (isset($part['inlineData']['data'])) {
                            $base64Image = $part['inlineData']['data'];
                            break; // Gambar ketemu, hentikan pencarian!
                        }
                    }
                }
                
                if ($base64Image) {
                    $imageContents = base64_decode($base64Image);
                    $filename = 'rute-' . Str::slug($rute->kota_asal . '-' . $rute->kota_tujuan) . '-' . time() . '.jpg';
                    
                    // PERBAIKAN: Gunakan disk('public') secara eksplisit
                    Storage::disk('public')->put('gallery/' . $filename, $imageContents);

                    // PERBAIKAN: Simpan ke DB tanpa garis miring (/) di depan
                    $rute->update([
                        'image_path' => 'storage/gallery/' . $filename
                    ]);
                    
                    Log::info("SUKSES BESAR: Gambar rute ID {$rute->id} berhasil di-generate dan diamankan!");
                } else {
                    Log::error("GAGAL: Gambar tidak ditemukan dalam parts. Response: " . $response->body());
                }

            } else {
                $statusCode = $response->status();
                $errorDetail = $response->json() ?? $response->body();
                Log::error("API Error (Status {$statusCode}) pada Rute ID {$rute->id}: " . json_encode($errorDetail));
            }

        } catch (\Exception $e) {
            Log::error("Sistem Exception pada Rute ID {$rute->id}: " . $e->getMessage());
        }
    }
}