<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    // Tambahkan baris ini agar kita bisa menyimpan key dan value prompt
    protected $fillable = ['key', 'value'];
}