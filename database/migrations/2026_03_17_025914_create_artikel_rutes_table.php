<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_rutes', function (Blueprint $table) {
            $table->id();
            
            // JALUR AMAN: Kita gunakan tipe data angka biasa agar tidak memicu error 'rutes'
            $table->unsignedBigInteger('rute_id');
            
            // Kolom teks yang akan diisi oleh AI
            $table->text('paragraf_pembuka')->nullable();
            $table->text('teks_layanan')->nullable();
            $table->text('teks_tips')->nullable();
            $table->json('teks_faq')->nullable(); 
            
            // Kolom pemantau proses AI (Background Job)
            $table->string('status_generate')->default('pending'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_rutes');
    }
};