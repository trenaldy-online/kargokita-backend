<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RuteResource\Pages;
use App\Models\Rute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class RuteResource extends Resource
{
    protected static ?string $model = Rute::class;

    // Ini untuk mengubah ikon menu di sebelah kiri
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    // Ini untuk mengubah nama menu
    protected static ?string $navigationLabel = 'Kelola Rute & Tarif';
    protected static ?string $pluralModelLabel = 'Data Rute';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('wilayah_tujuan')
                    ->options([
                        'kalimantan' => 'Kalimantan',
                        'sulawesi' => 'Sulawesi',
                        'papua' => 'Papua',
                        'maluku' => 'Maluku',
                        'nusa-tenggara' => 'Nusa Tenggara',
                    ])
                    ->required()
                    ->label('Wilayah Tujuan'),
                TextInput::make('kota_asal')
                    ->required()
                    ->placeholder('Contoh: Surabaya'),
                TextInput::make('kota_tujuan')
                    ->required()
                    ->placeholder('Contoh: Makassar')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug('ekspedisi surabaya '.$state)) : null),
                TextInput::make('harga_per_kg')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'),
                TextInput::make('min_charge_kg')
                    ->numeric()
                    ->required()
                    ->suffix('Kg'),
                TextInput::make('estimasi_hari')
                    ->required()
                    ->placeholder('Contoh: 3-4 Hari'),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->readOnly()
                    ->helperText('URL ini dibuat otomatis oleh sistem untuk SEO Anda.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wilayah_tujuan')->searchable()->sortable(),
                TextColumn::make('kota_asal')->searchable(),
                TextColumn::make('kota_tujuan')->searchable(),
                TextColumn::make('harga_per_kg')->money('IDR')->sortable(),
                TextColumn::make('estimasi_hari'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRutes::route('/'),
            'create' => Pages\CreateRute::route('/create'),
            'edit' => Pages\EditRute::route('/{record}/edit'),
        ];
    }
}