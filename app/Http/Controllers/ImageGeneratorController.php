<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use App\Models\Setting;
use App\Jobs\GenerateImageRuteJob;
use Illuminate\Http\Request;

class ImageGeneratorController extends Controller
{
    // Menampilkan halaman dashboard
    public function index()
    {
        $rutes = Rute::orderBy('id', 'desc')->get();
        $savedPrompt = Setting::where('key', 'image_prompt')->value('value');
        
        // Default prompt jika kosong
        if (!$savedPrompt) {
            $savedPrompt = "Foto sinematik profesional armada truk logistik KargoKita sedang melaju dari [kota_asal] menuju [kota_tujuan] di jalan tol pada pagi hari, realistis, resolusi 4k, logistik Indonesia.";
        }

        return view('admin.generate-image.index', compact('rutes', 'savedPrompt'));
    }

    // Menyimpan prompt ke database
    public function savePrompt(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);
        
        Setting::updateOrCreate(
            ['key' => 'image_prompt'],
            ['value' => $request->prompt]
        );

        return back()->with('success', 'Prompt gambar berhasil disimpan!');
    }

    // Mengeksekusi Job secara bulk
    public function generateBulk(Request $request)
    {
        $request->validate([
            'rute_ids' => 'required|array',
            'prompt' => 'required|string',
        ]);

        $forceRegenerate = $request->has('force_regenerate'); // Checkbox tumpuk gambar lama

        foreach ($request->rute_ids as $ruteId) {
            // Lempar ke background job agar loading tidak muter-muter
            GenerateImageRuteJob::dispatch($ruteId, $request->prompt, $forceRegenerate);
        }

        return back()->with('success', count($request->rute_ids) . ' Rute sedang diproses gambarnya di latar belakang!');
    }
}