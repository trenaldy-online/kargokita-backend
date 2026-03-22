<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('artikel_rutes', function (Blueprint $table) {
            $table->string('judul')->after('rute_id')->nullable();
            $table->string('slug')->after('judul')->unique()->nullable();
        });
    }
    public function down(): void {
        Schema::table('artikel_rutes', function (Blueprint $table) {
            $table->dropColumn(['judul', 'slug']);
        });
    }
};