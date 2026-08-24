<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\NormalizesCoverImage;
use App\Filament\Resources\PackageResource\Pages;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PackageResource extends Resource
{
    use NormalizesCoverImage;

    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Forms\Components\TextInput::make('duration_days')
                    ->label('Duration (days)')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(365)
                    ->nullable()
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
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
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('public_url')
                    ->label('Public URL')
                    ->icon('heroicon-m-link')
                    ->getStateUsing(fn (Package $record): string => $record->publicUrl()),
                Tables\Columns\BadgeColumn::make('is_published')
                    ->label('Status')
                    ->getStateUsing(fn (Package $record): string => $record->is_published ? 'Published' : 'Draft')
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
                    ->visible(fn (Package $record): bool => auth()->user()->can('update', $record)),
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
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
