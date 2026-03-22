<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            // Menambah kolom image_path setelah slug (sesuaikan nama tabel jika berbeda)
            $table->string('image_path')->nullable()->after('slug'); 
        });
    }

    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};