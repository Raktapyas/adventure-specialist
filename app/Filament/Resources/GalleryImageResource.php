<?php

namespace App\Filament\Resources;

use App\Filament\Components\MediaPicker;
use App\Filament\Resources\GalleryImageResource\Pages;
use App\Models\GalleryImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                MediaPicker::make('image_url', 'Choose image or video from Media Library')
                    ->required(),
                Forms\Components\Textarea::make('caption')
                    ->nullable()
                    ->maxLength(1000)
                    ->rows(3)
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->nullable()
                    ->placeholder('Auto — next available')
                    ->helperText('Leave blank to auto-assign next position.')
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ViewColumn::make('thumbnail')
                        ->view('filament.tables.columns.media-thumb'),
                    Tables\Columns\TextColumn::make('caption')
                        ->weight('bold')
                        ->description(fn (GalleryImage $record): ?string => $record->sort_order !== null ? 'Sort order: '.$record->sort_order : null),
                ]),
            ])
            ->contentGrid(['md' => 3, 'xl' => 4])
            ->modifyQueryUsing(fn ($query) => $query->with('media'))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (GalleryImage $record): bool => auth()->user()->can('update', $record)),
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
            'index' => Pages\ListGalleryImages::route('/'),
            'create' => Pages\CreateGalleryImage::route('/create'),
            'edit' => Pages\EditGalleryImage::route('/{record}/edit'),
        ];
    }
}
