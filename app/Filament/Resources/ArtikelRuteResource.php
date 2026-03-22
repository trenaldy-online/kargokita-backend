<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtikelRuteResource\Pages;
use App\Models\ArtikelRute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;

class ArtikelRuteResource extends Resource
{
    protected static ?string $model = ArtikelRute::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Artikel AI (Generate)';
    protected static ?string $pluralModelLabel = 'Riwayat Artikel AI';
    
    // Memasukkan ke dalam grup menu yang sama dengan Artikel Manual
    protected static ?string $navigationGroup = 'Manajemen Artikel';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Artikel')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Hasil Tulisan AI (Bisa Diedit)')
                    ->description('Anda bisa merapikan atau menambahkan kalimat pada hasil karangan AI di bawah ini.')
                    ->schema([
                        Forms\Components\Textarea::make('paragraf_pembuka')
                            ->label('Paragraf Pembuka')
                            ->rows(4)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('teks_layanan')
                            ->label('Teks Penjelasan Layanan')
                            ->rows(6)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('teks_tips')
                            ->label('Teks Tips Aman')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Daftar FAQ (Tanya Jawab)')
                    ->description('Daftar pertanyaan otomatis yang dibuat oleh AI.')
                    ->schema([
                        // Repeater ini otomatis membaca format JSON dari database!
                        Forms\Components\Repeater::make('teks_faq')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('pertanyaan')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('jawaban')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['pertanyaan'] ?? null)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->weight('bold')
                    ->limit(40)
                    ->description(fn (ArtikelRute $record): string => "Rute: {$record->rute->kota_asal} - {$record->rute->kota_tujuan}"),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->color('gray')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status_generate')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                // Filter untuk melihat yang sukses atau gagal saja
                Tables\Filters\SelectFilter::make('status_generate')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'processing' => 'Diproses',
                        'completed' => 'Sukses Selesai',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->actions([
                // Tombol Edit untuk mengubah isi teks AI
                Tables\Actions\EditAction::make()->label('Edit Konten'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // Fitur Kelompok (Group) berdasarkan Tanggal Generate (Pengganti konsep Batch)
            ->defaultGroup('created_at')
            ->groups([
                Group::make('created_at')
                    ->label('Tanggal Batch Generate')
                    ->date(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtikelRutes::route('/'),
            'edit' => Pages\EditArtikelRute::route('/{record}/edit'),
        ];
    }
}