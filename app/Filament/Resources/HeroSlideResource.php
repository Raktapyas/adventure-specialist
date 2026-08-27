<?php

namespace App\Filament\Resources;

use App\Filament\Components\MediaPicker;
use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
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
                        MediaPicker::make('image_path', 'Image or video (from Media Library)')
                            ->required()
                            ->columnSpan(2),
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
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Auto — next available')
                            ->helperText('Leave blank to auto-assign next position.')
                            ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
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
                Tables\Columns\ViewColumn::make('thumbnail')
                    ->label('Media')
                    ->view('filament.tables.columns.media-thumb', ['sizeClass' => 'h-11 w-11']),
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
            ->modifyQueryUsing(fn ($query) => $query->with('media'))
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
}
