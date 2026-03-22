<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\ImageGeneratorController;

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/generate-image', [ImageGeneratorController::class, 'index'])->name('admin.generate-image.index');
    Route::post('/generate-image/save-prompt', [ImageGeneratorController::class, 'savePrompt'])->name('admin.generate-image.save-prompt');
    Route::post('/generate-image/generate-bulk', [ImageGeneratorController::class, 'generateBulk'])->name('admin.generate-image.generate-bulk');
});