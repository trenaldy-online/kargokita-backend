<?php

namespace App\Filament\Imports;

use App\Models\Rute;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class RuteImporter extends Importer
{
    protected static ?string $model = Rute::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('kota_asal')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
                
            ImportColumn::make('kota_tujuan')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
                
            ImportColumn::make('wilayah_tujuan')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
                
            ImportColumn::make('harga_per_kg')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:0']),
                
            ImportColumn::make('min_charge_kg')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:1']),
                
            ImportColumn::make('estimasi_hari')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:1']),
        ];
    }

    // FUNGSI KRUSIAL: LOGIKA UPSERT (Update or Insert)
    public function resolveRecord(): ?Rute
    {
        // 1. Cari atau buat rute baru berdasarkan asal dan tujuan
        $rute = Rute::firstOrNew([
            'kota_asal' => $this->data['kota_asal'],
            'kota_tujuan' => $this->data['kota_tujuan'],
        ]);

        // 2. Jika rute ini adalah rute baru (belum ada di database), buatkan slug otomatis!
        if (! $rute->exists) {
            $rute->slug = Str::slug($this->data['kota_asal'] . '-' . $this->data['kota_tujuan']);
        }

        return $rute;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Proses import rute telah selesai. ' . number_format($import->successful_rows) . ' baris berhasil diproses.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Namun, ada ' . number_format($failedRowsCount) . ' baris yang gagal (silakan unduh laporan error).';
        }

        return $body;
    }
}