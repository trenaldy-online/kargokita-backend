<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtikelBlogResource\Pages;
use App\Models\ArtikelBlog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArtikelBlogResource extends Resource
{
    protected static ?string $model = ArtikelBlog::class;

    // Mengganti icon menu di samping kiri
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Artikel Blog (Manual)';
    protected static ?string $pluralModelLabel = 'Artikel Blog';
    protected static ?string $navigationGroup = 'Manajemen Artikel';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Artikel')
                    ->description('Tulis artikel manual Anda di sini.')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            // Otomatis membuat URL (slug) saat judul diketik
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ArtikelBlog::class, 'slug', ignoreRecord: true),
                        
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('blog-thumbnails')
                            ->columnSpanFull(),
                        
                        // Text Editor lengkap ala WordPress
                        Forms\Components\RichEditor::make('konten')
                            ->required()
                            ->fileAttachmentsDirectory('blog-images')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan SEO & Publikasi')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(255)
                            ->helperText('Judul yang akan dibaca oleh Google (Maks 60 karakter)'),
                        
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(255)
                            ->helperText('Deskripsi singkat yang muncul di hasil pencarian Google (Maks 160 karakter)'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft (Disimpan)',
                                'published' => 'Published (Terbit)',
                            ])
                            ->default('draft')
                            ->required(),
                            
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Terbit'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail'),
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'draft',
                        'success' => 'published',
                    ]),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('d M Y')
                    ->sortable(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtikelBlogs::route('/'),
            'create' => Pages\CreateArtikelBlog::route('/create'),
            'edit' => Pages\EditArtikelBlog::route('/{record}/edit'),
        ];
    }
}