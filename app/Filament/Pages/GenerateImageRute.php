<?php

namespace App\Filament\Pages;

use App\Models\Rute;
use App\Models\Setting;
use App\Jobs\GenerateImageRuteJob;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class GenerateImageRute extends Page
{
    // Icon di menu samping (Sidebar)
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    
    // Masukkan ke grup menu yang sama dengan Generator Artikel
    protected static ?string $navigationGroup = 'AI Tools'; 
    
    // Nama menu di Sidebar
    protected static ?string $navigationLabel = 'Generator Gambar AI';
    
    // Judul Halaman
    protected static ?string $title = 'Generate Thumbnail Rute';

    protected static string $view = 'filament.pages.generate-image-rute';

    // Variabel Livewire
    public $prompt = '';
    public $selectedRutes = [];
    public $forceRegenerate = false;

    // Berjalan pertama kali saat halaman dibuka
    public function mount()
    {
        $this->prompt = Setting::where('key', 'image_prompt')->value('value')
            ?? "Foto sinematik profesional armada truk logistik KargoKita sedang melaju dari [kota_asal] menuju [kota_tujuan] di jalan tol pada pagi hari, realistis, resolusi 4k, logistik Indonesia.";
    }

    // Mengambil data rute dari database
    public function getRutesProperty()
    {
        return Rute::orderBy('id', 'desc')->get();
    }

    // Fungsi menyimpan prompt
    public function savePrompt()
    {
        Setting::updateOrCreate(
            ['key' => 'image_prompt'],
            ['value' => $this->prompt]
        );

        Notification::make()
            ->title('Prompt Permanen Berhasil Disimpan!')
            ->success()
            ->send();
    }

    // Fungsi execute background job
    public function generateBulk()
    {
        if (empty($this->selectedRutes)) {
            Notification::make()->title('Silakan centang minimal 1 rute!')->danger()->send();
            return;
        }

        foreach ($this->selectedRutes as $ruteId) {
            GenerateImageRuteJob::dispatch($ruteId, $this->prompt, $this->forceRegenerate);
        }

        Notification::make()
            ->title(count($this->selectedRutes) . ' Rute sedang digenerate gambarnya di latar belakang!')
            ->success()
            ->send();

        // Kosongkan centangan setelah di-klik
        $this->selectedRutes = [];
    }
}