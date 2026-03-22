<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('wilayah_tujuan'); // Contoh: Kalimantan, Sulawesi
            $table->string('kota_asal');      // Contoh: Surabaya
            $table->string('kota_tujuan');    // Contoh: Makassar
            $table->integer('harga_per_kg');  // Contoh: 2800
            $table->integer('min_charge_kg'); // Contoh: 50
            $table->string('estimasi_hari');  // Contoh: 3-4 Hari
            $table->string('slug')->unique(); // Untuk URL otomatis (rute/surabaya-makassar)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};