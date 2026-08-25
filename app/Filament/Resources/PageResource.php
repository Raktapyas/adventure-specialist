<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\NormalizesCoverImage;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    use NormalizesCoverImage;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('Parent page')
                    ->options(function () use ($record): array {
                        return Page::query()
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
                    ->helperText('A page can only be nested directly under a top-level page.')
                    ->rules([
                        'nullable',
                        'integer',
                        'exists:pages,id',
                        function () use ($record): Closure {
                            return function (string $attribute, $value, Closure $fail) use ($record): void {
                                if ($value === null) {
                                    return;
                                }

                                if ($record && (int) $value === (int) $record->id) {
                                    $fail('A page cannot be its own parent.');
                                }

                                if ($record && in_array((int) $value, $record->descendantIds(), true)) {
                                    $fail('A page cannot be a descendant of itself.');
                                }

                                if (Page::find($value)?->chainDepth() >= 1) {
                                    $fail('A page can only be nested directly under a top-level page.');
                                }
                            };
                        },
                    ])
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
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
                    ->getStateUsing(fn (Page $record): string => $record->publicUrl()),
                Tables\Columns\BadgeColumn::make('is_published')
                    ->label('Status')
                    ->getStateUsing(fn (Page $record): string => $record->is_published ? 'Published' : 'Draft')
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
                    ->visible(fn (Page $record): bool => auth()->user()->can('update', $record)),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
