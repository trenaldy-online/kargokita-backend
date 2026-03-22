<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;

    // Memberitahu Laravel bahwa jembatan ini terhubung ke tabel "routes"
    protected $table = 'routes';

    // Mengizinkan semua kolom diisi data
    protected $guarded = [];

    // Relasi ke ArtikelRute (1 Rute bisa punya banyak ArtikelRute)
    public function artikel()
    {
    // Menggunakan Artikel::class lebih aman daripada 'App\Models\Artikel'
    return $this->hasOne(Artikel::class); 
    }
}