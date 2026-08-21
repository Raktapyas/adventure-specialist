<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\NormalizesCoverImage;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    use NormalizesCoverImage;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('Parent service')
                    ->options(function () use ($record): array {
                        return Service::query()
                            ->whereDoesntHave('parent')
                            ->when($record, function ($query) use ($record) {
                                $query->whereKeyNot($record->id)
                                    ->whereNotIn('id', $record->descendantIds());
                            })
                            ->orderBy('sort_order')
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('A service can only be nested directly under a top-level service.')
                    ->rules([
                        'nullable',
                        'integer',
                        'exists:services,id',
                        function () use ($record): Closure {
                            return function (string $attribute, $value, Closure $fail) use ($record): void {
                                if ($value === null) {
                                    return;
                                }

                                if ($record && (int) $value === (int) $record->id) {
                                    $fail('A service cannot be its own parent.');
                                }

                                if ($record && in_array((int) $value, $record->descendantIds(), true)) {
                                    $fail('A service cannot be a descendant of itself.');
                                }

                                if (Service::find($value)?->chainDepth() >= 1) {
                                    $fail('Services can be nested no deeper than two levels.');
                                }
                            };
                        },
                    ]),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state): void {
                        if (($get('slug') ?? '') !== Str::slug($old ?? '')) {
                            return;
                        }

                        $set('slug', Str::slug($state ?? ''));
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->alphaDash()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique. Lowercase letters, numbers and dashes. Changing it keeps old links working via redirects.'),
                Forms\Components\Textarea::make('excerpt')
                    ->nullable()
                    ->maxLength(1000)
                    ->rows(3)
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                Forms\Components\RichEditor::make('content')
                    ->nullable()
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                Forms\Components\Select::make('icon')
                    ->label('Icon')
                    ->options(static::iconOptions())
                    ->searchable()
                    ->nullable()
                    ->helperText('Icon shown in the mini badge on the service card.'),
                static::coverImageField(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->nullable()
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                Forms\Components\Select::make('is_published')
                    ->label('Status')
                    ->options([
                        true => 'Published',
                        false => 'Draft',
                    ])
                    ->default(false)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->fontFamily('mono')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('public_url')
                    ->label('Public URL')
                    ->icon('heroicon-m-link')
                    ->getStateUsing(fn (Service $record): string => $record->publicUrl()),
                Tables\Columns\BadgeColumn::make('is_published')
                    ->label('Status')
                    ->getStateUsing(fn (Service $record): string => $record->is_published ? 'Published' : 'Draft')
                    ->colors([
                        'success' => 'Published',
                        'warning' => 'Draft',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Service $record): bool => auth()->user()->can('update', $record)),
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

    /**
     * Curated heroicon names for the service card's mini badge.
     *
     * @return array<string, string>
     */
    private static function iconOptions(): array
    {
        return [
            'heroicon-o-paper-airplane' => 'Flight',
            'heroicon-o-map' => 'Hiking',
            'heroicon-o-sun' => 'Day trip',
            'heroicon-o-fire' => 'Safari',
            'heroicon-o-rocket-launch' => 'Adventure sport',
            'heroicon-o-arrow-down-circle' => 'Bungee / drop',
            'heroicon-o-bolt' => 'Rafting / adrenaline',
            'heroicon-o-globe-asia-australia' => 'Tours',
            'heroicon-o-photo' => 'Sightseeing',
            'heroicon-o-briefcase' => 'Business',
            'heroicon-o-heart' => 'Honeymoon',
            'heroicon-o-users' => 'Groups',
            'heroicon-o-home-modern' => 'Accommodation',
            'heroicon-o-truck' => 'Transfers',
            'heroicon-o-academic-cap' => 'Culture',
            'heroicon-o-sparkles' => 'Special',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
