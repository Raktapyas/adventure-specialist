<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Slide')
                    ->columns(2)
                    ->schema([
                        Select::make('image_path')
                            ->label('Image (from Media Library)')
                            ->searchable()
                            ->preload()
                            ->allowHtml()
                            ->columnSpan(2)
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
                            // Validation state is a plain string here; normalize to a host-relative path.
                            ->mutateStateForValidationUsing(fn ($state): ?string => Media::normalizePath($state))
                            ->rules([
                                'string',
                                'max:255',
                                'starts_with:/',
                                'not_regex:/\/\//',
                                'not_regex:/\.\./',
                            ]),
                        TextInput::make('eyebrow')
                            ->label('Eyebrow')
                            ->maxLength(80)
                            ->columnSpan(2),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(120)
                            ->columnSpan(2),
                        Textarea::make('lede')
                            ->label('Lede')
                            ->rows(2)
                            ->maxLength(240)
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Call-to-action buttons')
                    ->description('Primary renders as a solid button, secondary as an outline. Empty buttons are hidden.')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('primary_cta_label')
                            ->label('Primary label')
                            ->maxLength(40),
                        TextInput::make('primary_cta_url')
                            ->label('Primary URL')
                            ->maxLength(255)
                            ->rules(['nullable', 'string', 'max:255', 'starts_with:/']),
                        TextInput::make('secondary_cta_label')
                            ->label('Secondary label')
                            ->maxLength(40),
                        TextInput::make('secondary_cta_url')
                            ->label('Secondary URL')
                            ->maxLength(255)
                            ->rules(['nullable', 'string', 'max:255', 'starts_with:/']),
                    ]),

                Forms\Components\Section::make('Display')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Select::make('effect')
                            ->label('Ken Burns effect')
                            ->options([
                                'animate-hero-zoom-in' => 'Zoom in',
                                'animate-hero-zoom-out' => 'Zoom out',
                                'animate-hero-pan-right' => 'Pan right',
                                'animate-hero-pan-left' => 'Pan left',
                            ])
                            ->default('animate-hero-zoom-in'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort order'),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (HeroSlide $record): ?string => filled($record->image_path) ? url($record->image_path) : null)
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('eyebrow')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('primary_cta_label')
                    ->label('Primary CTA')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }

    private static function mediaOptionLabel(Media $media): string
    {
        $url = filled($media->url()) ? url($media->url()) : url('/images/placeholder.png');

        return '<div class="flex items-center gap-3">'
            .'<img src="'.e($url).'" alt="'.e($media->name).'" class="h-10 w-10 rounded object-cover">'
            .'<span>'.e($media->name).'</span>'
            .'</div>';
    }
}
