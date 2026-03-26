<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Rute extends Model
{
    use HasFactory;

    // Memberitahu Laravel bahwa jembatan ini terhubung ke tabel "routes"
    protected $table = 'routes';

    // Mengizinkan semua kolom diisi data
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rute) {
            // Jika slug kosong, buat otomatis dari gabungan kota asal & tujuan
            if (empty($rute->slug)) {
                $rute->slug = Str::slug($rute->kota_asal . '-' . $rute->kota_tujuan);
            }
        });
    }

    // Relasi ke ArtikelRute (1 Rute bisa punya banyak ArtikelRute)
    public function artikel()
    {
    // Menggunakan Artikel::class lebih aman daripada 'App\Models\Artikel'
    return $this->hasOne(Artikel::class); 
    }
}