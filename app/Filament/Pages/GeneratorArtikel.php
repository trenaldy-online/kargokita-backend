<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use App\Models\Rute;
use App\Models\ArtikelRute;
use App\Jobs\ProsesArtikelAi;

class GeneratorArtikel extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Generator Artikel AI';
    protected static ?string $title = 'Mesin Pabrik Artikel AI';
    protected static ?string $navigationGroup = 'AI Tools';
    
    protected static string $view = 'filament.pages.generator-artikel';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'jumlah' => 1,
            'format_judul' => 'Ekspedisi [asal] ke [tujuan] Termurah 2026',
            'format_slug' => 'ekspedisi-[asal]-ke-[tujuan]-termurah-2026',
        ]);
    }

    // Fungsi bantuan untuk menghitung ketersediaan rute secara real-time
    protected function getJumlahRuteTersedia(Get $get): int
    {
        $query = Rute::query();

        if ($get('kota_asal')) $query->where('kota_asal', $get('kota_asal'));
        if ($get('wilayah_tujuan')) $query->where('wilayah_tujuan', $get('wilayah_tujuan'));
        if ($get('kota_tujuan')) $query->where('kota_tujuan', $get('kota_tujuan'));

        return $query->count();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // BLOK FILTER (BARU)
                Section::make('Filter Target Rute (Opsional)')
                    ->description('Pilih secara spesifik area mana yang ingin dibuatkan artikelnya. Kosongkan jika ingin acak ke seluruh Indonesia.')
                    ->schema([
                        Select::make('kota_asal')
                            ->label('Kota Asal')
                            ->options(fn () => Rute::distinct()->pluck('kota_asal', 'kota_asal'))
                            ->searchable()
                            ->live() // Membuat form reaktif
                            ->afterStateUpdated(fn (Set $set) => $set('jumlah', 1)),

                        Select::make('wilayah_tujuan')
                            ->label('Pulau / Wilayah Tujuan')
                            ->options(fn () => Rute::distinct()->pluck('wilayah_tujuan', 'wilayah_tujuan'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('jumlah', 1)),

                        Select::make('kota_tujuan')
                            ->label('Kota Tujuan')
                            ->options(function (Get $get) {
                                // Jika wilayah dipilih, kota tujuan yang muncul hanya yang ada di wilayah itu
                                $query = Rute::query();
                                if ($get('wilayah_tujuan')) {
                                    $query->where('wilayah_tujuan', $get('wilayah_tujuan'));
                                }
                                return $query->distinct()->pluck('kota_tujuan', 'kota_tujuan');
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('jumlah', 1)),
                    ])->columns(3),

                // BLOK BATCH & SEO
                Section::make('Pengaturan Batch & SEO')
                    ->schema([
                        TextInput::make('jumlah')
                            ->label('Jumlah Artikel yang Diproses (Batch)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            // OTOMATIS MENGUNCI BATAS MAKSIMAL BERDASARKAN FILTER
                            ->maxValue(fn (Get $get) => $this->getJumlahRuteTersedia($get) > 0 ? $this->getJumlahRuteTersedia($get) : 1)
                            // MENGUBAH TEKS BANTUAN SECARA REAL-TIME
                            ->helperText(function (Get $get) {
                                $tersedia = $this->getJumlahRuteTersedia($get);
                                if ($tersedia === 0) return '⚠️ Tidak ada rute yang cocok dengan filter di atas.';
                                return "Maksimal rute tersedia: {$tersedia} rute.";
                            }),
                        
                        TextInput::make('format_judul')
                            ->label('Format Judul Utama (H1)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if ($state) {
                                    $slug = strtolower($state);
                                    $slug = preg_replace('/[^a-z0-9\[\]\-]+/', '-', $slug);
                                    $slug = preg_replace('/-+/', '-', $slug);
                                    $slug = trim($slug, '-');
                                    $set('format_slug', $slug);
                                }
                            })
                            ->helperText('Gunakan tag [asal] dan [tujuan].'),

                        TextInput::make('format_slug')
                            ->label('Format URL (Slug)')
                            ->required()
                            ->helperText('Gunakan tag [asal] dan [tujuan].'),
                    ])
            ])
            ->statePath('data');
    }

    public function generateAi()
    {
        $data = $this->form->getState();
        $jumlahBatas = $data['jumlah'];

        // 1. BANGUN PENCARIAN RUTE BERDASARKAN FILTER
        $queryRute = Rute::query();
        if (!empty($data['kota_asal'])) $queryRute->where('kota_asal', $data['kota_asal']);
        if (!empty($data['wilayah_tujuan'])) $queryRute->where('wilayah_tujuan', $data['wilayah_tujuan']);
        if (!empty($data['kota_tujuan'])) $queryRute->where('kota_tujuan', $data['kota_tujuan']);

        // Ambil rute secara acak sesuai filter dan jumlah
        $ruteDipilih = $queryRute->inRandomOrder()->take($jumlahBatas)->get();
        $jumlahBerhasilDiproses = 0;

        if ($ruteDipilih->count() === 0) {
            Notification::make()->title('Gagal')->body('Tidak ada rute yang sesuai dengan filter.')->danger()->send();
            return;
        }

        foreach ($ruteDipilih as $rute) {
            $judulAsli = str_replace(['[asal]', '[tujuan]'], [$rute->kota_asal, $rute->kota_tujuan], $data['format_judul']);
            
            $slugMentah = str_replace(['[asal]', '[tujuan]'], [$rute->kota_asal, $rute->kota_tujuan], $data['format_slug']);
            $slugAsli = Str::slug($slugMentah);

            if (ArtikelRute::where('slug', $slugAsli)->exists()) {
                $slugAsli = $slugAsli . '-' . rand(100, 9999);
            }

            $artikelBaru = ArtikelRute::create([
                'rute_id' => $rute->id,
                'judul' => $judulAsli,
                'slug' => $slugAsli,
                'status_generate' => 'pending'
            ]);

            ProsesArtikelAi::dispatch($rute, $artikelBaru->id);
            $jumlahBerhasilDiproses++;
        }

        Notification::make()
            ->title('Mesin AI Sedang Bekerja!')
            ->body("Berhasil mengirim {$jumlahBerhasilDiproses} antrean artikel ke latar belakang.")
            ->success()
            ->send();
    }

    // FUNGSI BARU: Mengambil data statistik antrean khusus hari ini
    public function getQueueStats(): array
    {
        $hariIni = \Carbon\Carbon::today();

        // Menghitung status dari database
        $pending = \App\Models\ArtikelRute::where('status_generate', 'pending')->count();
        $processing = \App\Models\ArtikelRute::where('status_generate', 'processing')->count();
        $completed = \App\Models\ArtikelRute::where('status_generate', 'completed')->whereDate('created_at', $hariIni)->count();
        $failed = \App\Models\ArtikelRute::where('status_generate', 'failed')->whereDate('created_at', $hariIni)->count();

        // Rumus Progress Bar
        $totalSisa = $pending + $processing;
        $totalSelesai = $completed + $failed;
        $totalBatchHariIni = $totalSisa + $totalSelesai;

        $persentase = $totalBatchHariIni > 0 ? round(($totalSelesai / $totalBatchHariIni) * 100) : 0;

        return [
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'persentase' => $persentase,
        ];
    }
}

