<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Rute;
use Illuminate\Support\Facades\DB;

Route::get('/cek-ongkir', function (Request $request) {
    // Tambahkan with('artikel') di sini 👇
    $rute = Rute::with('artikel')
                ->where('kota_asal', $request->asal)
                ->where('kota_tujuan', $request->tujuan)
                ->first();

    // Jika rute ditemukan, kirimkan harganya
    if ($rute) {
        return response()->json(['status' => 'sukses', 'data' => $rute]);
    } 
    // Jika belum ada di database
    else {
        return response()->json(['status' => 'gagal', 'pesan' => 'Mohon maaf, rute ini belum tersedia.']);
    }
});

// Jalur untuk mengambil data rute berdasarkan URL (Slug)
Route::get('/rute/{slug}', function ($slug) {
    // 1. Cari ARTIKEL berdasarkan URL slug-nya
    $artikel = App\Models\ArtikelRute::where('slug', $slug)
                ->where('status_generate', 'completed')
                ->first();

    if ($artikel) {
        // 2. Ambil data harga rute parent-nya
        $rute = App\Models\Rute::find($artikel->rute_id);
        $rute->artikel = $artikel;

        // 3. JARING SEO: Cari 4 Artikel lain yang sudah 'completed' di wilayah yang sama
        $artikelTerkait = App\Models\ArtikelRute::where('id', '!=', $artikel->id)
            ->where('status_generate', 'completed')
            ->whereHas('rute', function($query) use ($rute) {
                $query->where('wilayah_tujuan', $rute->wilayah_tujuan);
            })
            ->with('rute') // Bawa serta data harga rute-nya
            ->inRandomOrder()->take(4)->get();

        $rute->artikel_terkait = $artikelTerkait;

        return response()->json(['status' => 'sukses', 'data' => $rute]);
    } else {
        return response()->json(['status' => 'gagal'], 404);
    }
});

// Rute untuk mengambil rute-rute pilihan (yang sudah ada gambarnya)
Route::get('/featured-routes', function () {
    $routes = Rute::with('artikel') // Load relasi artikel agar slug-nya terbawa
        ->whereNotNull('image_path') // Hanya ambil yang sudah di-generate gambarnya oleh AI
        ->orderBy('id', 'desc')
        ->limit(6) // Ambil 6 rute terbaru untuk ditampilkan di Home
        ->get();

    return response()->json([
        'status' => 'sukses',
        'data' => $routes
    ]);
});

Route::get('/semua-artikel', function (Request $request) {
    try {
        // 1. Ambil dari tabel artikel_blogs
        $blogs = DB::table('artikel_blogs')
            ->select(
                'id', 
                'judul', 
                'slug', 
                'konten', 
                'thumbnail as image_path', // <--- KUNCI: Samarkan thumbnail menjadi image_path agar Next.js tidak bingung
                'created_at', 
                DB::raw("'Blog' as tipe"), 
                DB::raw("NULL as wilayah_tujuan") 
            )->get();

        // 2. Ambil dari tabel artikel_rutes (dengan S)
        $rutes = DB::table('artikel_rutes')
            ->join('routes', 'artikel_rutes.rute_id', '=', 'routes.id')
            ->where('artikel_rutes.status_generate', 'completed')
            ->select(
                'artikel_rutes.id', 
                'artikel_rutes.judul', // <--- Langsung ambil dari kolom judul
                'artikel_rutes.slug', 
                'artikel_rutes.paragraf_pembuka as konten', // <--- KUNCI: Gunakan paragraf_pembuka sebagai cuplikan konten di halaman blog
                'routes.image_path as image_path', 
                'artikel_rutes.created_at',
                DB::raw("'Rute' as tipe"), 
                'routes.wilayah_tujuan'
            )->get();

        // Gabungkan kedua data dan urutkan yang terbaru di atas
        $semuaArtikel = $blogs->merge($rutes)->sortByDesc('created_at')->values();

        return response()->json([
            'status' => 'sukses',
            'data' => $semuaArtikel
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan SQL: ' . $e->getMessage()
        ], 500);
    }
});