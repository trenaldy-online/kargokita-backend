<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    // ARAHKAN KE TABEL YANG BENAR:
    protected $table = 'artikel_rutes'; 

    protected $guarded = [];

    // Relasi balik ke Rute
    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }
}