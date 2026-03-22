<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtikelRute extends Model
{
    use HasFactory;

    protected $guarded = []; // Buka semua proteksi

    // Memberitahu Laravel bahwa teks_faq adalah Array/JSON
    protected $casts = [
        'teks_faq' => 'array',
    ];

    // Relasi balik ke Rute
    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }
}