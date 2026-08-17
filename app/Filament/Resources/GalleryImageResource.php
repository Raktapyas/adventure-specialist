<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryImageResource\Pages;
use App\Models\GalleryImage;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('image_url')
                    ->label('Choose from Media Library')
                    ->searchable()
                    ->preload()
                    ->allowHtml()
                    ->getSearchResultsUsing(function (string $search): array {
                        return Media::query()
                            ->where('name', 'like', "%{$search}%")
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn (Media $media): array => [
                                $media->path => self::mediaOptionLabel($media),
                            ])
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $media = Media::where('path', $value)->first();

                        return $media ? self::mediaOptionLabel($media) : e($value);
                    })
                    ->required()
                    ->mutateStateForValidationUsing(fn ($state): ?string => Media::normalizePath($state))
                    ->rules([
                        'string',
                        'max:255',
                        'starts_with:/',
                        'not_regex:/\/\//',
                        'not_regex:/\.\./',
                    ]),
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
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
            ]);
    }

    /**
     * HTML option label for the media picker: a thumbnail preview next to the
     * file name. Values are escaped so allowHtml() cannot be abused via stored
     * names or paths.
     */
    private static function mediaOptionLabel(Media $media): string
    {
        $url = filled($media->url()) ? url($media->url()) : url('/images/placeholder.png');

        return '<div class="flex items-center gap-3">'
            .'<img src="'.e($url).'" alt="'.e($media->name).'" class="h-10 w-10 rounded object-cover">'
            .'<span>'.e($media->name).'</span>'
            .'</div>';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('image_url')
                        ->size('100%')
                        ->square()
                        ->getStateUsing(fn (GalleryImage $record): ?string => filled($record->image_url) ? url($record->image_url) : null)
                        ->defaultImageUrl(url('/images/placeholder.png')),
                    Tables\Columns\TextColumn::make('caption')
                        ->weight('bold')
                        ->description(fn (GalleryImage $record): ?string => $record->sort_order !== null ? 'Sort order: '.$record->sort_order : null),
                ]),
            ])
            ->contentGrid(['md' => 3, 'xl' => 4])
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
