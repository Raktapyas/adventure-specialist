<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavigationItemResource\Pages;
use App\Models\Destination;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Service;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Navigation';

    protected static ?string $navigationLabel = 'Navigation Menu';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'navigation item';

    protected static ?string $pluralModelLabel = 'navigation menu';

    public static function shouldRegisterNavigation(): bool
    {
        if (! Schema::hasTable('navigation_items')) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }

    public static function canViewAny(): bool
    {
        if (! Schema::hasTable('navigation_items')) {
            return false;
        }

        return parent::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! Schema::hasTable('navigation_items')) {
            // Avoid 42S02 when the migration hasn't been run yet. Use a
            // derived table so the FROM clause doesn't reference the missing
            // real table, but still returns 0 rows for the Filament table.
            return $query->fromSub(function ($q): void {
                $q->selectRaw('1 as id');
            }, 'navigation_items')->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\Section::make('Menu Item')
                    ->description('Content and placement of this navigation entry. 3 levels max: Services → Region → Trek.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'custom' => 'Link',
                                'dropdown' => 'Dropdown (no link — prevents page jump)',
                            ])
                            ->default('custom')
                            ->live()
                            ->required()
                            ->helperText('Dropdown items show a flyout but do not navigate when clicked.'),
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent item')
                            ->options(function () use ($record): array {
                                if (! Schema::hasTable('navigation_items')) {
                                    return [];
                                }

                                try {
                                    // Allow any item with depth <2 (Top or Child) to be parent → enables Grandchild (depth 2) = 3 levels
                                    $all = NavigationItem::with('parent')->get();
                                    $filtered = $all->filter(function (NavigationItem $item) use ($record) {
                                        if ($record && (int) $item->getKey() === (int) $record->getKey()) {
                                            return false;
                                        }
                                        if ($record && in_array((int) $item->getKey(), $record->descendantIds(), true)) {
                                            return false;
                                        }

                                        return $item->chainDepth() < 2;
                                    });

                                    return $filtered->sortBy(fn (NavigationItem $i) => sprintf('%05d-%s', $i->sort_order, $i->label))->pluck('label', 'id')->all();
                                } catch (\Throwable $e) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('— Top level (main bar) —')
                            ->helperText('Top → main bar. Child → dropdown. Grandchild (Region → Trek) max 3 levels total.')
                            ->columnSpanFull()
                            ->rules([
                                'nullable',
                                'integer',
                                'exists:navigation_items,id',
                                function () use ($record): Closure {
                                    return function (string $attribute, $value, Closure $fail) use ($record): void {
                                        if ($value === null) {
                                            return;
                                        }

                                        if ($record && (int) $value === (int) $record->id) {
                                            $fail('A navigation item cannot be its own parent.');
                                        }

                                        if ($record && in_array((int) $value, $record->descendantIds(), true)) {
                                            $fail('A navigation item cannot be a descendant of itself.');
                                        }

                                        $parent = NavigationItem::find($value);

                                        if ($parent && $parent->chainDepth() >= 2) {
                                            $fail('Navigation can be nested no deeper than three levels (Services → Region → Trek).');
                                        }
                                    };
                                },
                            ])
                            ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(80)
                            ->placeholder('Home')
                            ->helperText('Display text in the navbar (e.g. Home, About Us, Trekking & Activities).')
                            ->columnSpan(1),
                        Forms\Components\Select::make('quick_pick')
                            ->label('Quick pick — existing content')
                            ->placeholder('Search pages, services, destinations…')
                            ->helperText('Search an existing Page/Service/Destination to autofill URL below, or type a custom URL manually.')
                            ->visible(fn (Forms\Get $get): bool => $get('type') !== 'dropdown')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                if (! Schema::hasTable('pages') && ! Schema::hasTable('services') && ! Schema::hasTable('destinations')) {
                                    return [];
                                }

                                $results = [];

                                try {
                                    if (Schema::hasTable('pages')) {
                                        $pages = Page::query()
                                            ->where('is_published', true)
                                            ->where(function ($q) use ($search): void {
                                                $q->where('title', 'like', "%{$search}%")
                                                    ->orWhere('slug', 'like', "%{$search}%");
                                            })
                                            ->orderBy('title')
                                            ->limit(10)
                                            ->get();
                                        foreach ($pages as $page) {
                                            $path = $page->getPath();
                                            $results[$path] = 'Page: '.$page->title.' — '.$path;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                }

                                try {
                                    if (Schema::hasTable('services')) {
                                        $services = Service::query()
                                            ->where('is_published', true)
                                            ->where(function ($q) use ($search): void {
                                                $q->where('title', 'like', "%{$search}%")
                                                    ->orWhere('slug', 'like', "%{$search}%");
                                            })
                                            ->orderBy('title')
                                            ->limit(10)
                                            ->get();
                                        foreach ($services as $service) {
                                            $path = $service->getPath();
                                            $results[$path] = 'Service: '.$service->title.' — '.$path;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                }

                                try {
                                    if (Schema::hasTable('destinations')) {
                                        $destinations = Destination::query()
                                            ->where('is_published', true)
                                            ->where(function ($q) use ($search): void {
                                                $q->where('title', 'like', "%{$search}%")
                                                    ->orWhere('slug', 'like', "%{$search}%");
                                            })
                                            ->orderBy('title')
                                            ->limit(10)
                                            ->get();
                                        foreach ($destinations as $destination) {
                                            $path = $destination->getPath();
                                            $results[$path] = 'Destination: '.$destination->title.' — '.$path;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                }

                                return array_slice($results, 0, 20, true);
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => $value ? e($value) : null)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                if (filled($state)) {
                                    $set('url', $state);
                                }
                            })
                            ->dehydrated(false)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->maxLength(500)
                            ->prefixIcon('heroicon-m-link')
                            ->placeholder('/about-us/ or https://example.com')
                            ->helperText(fn (Forms\Get $get): string => $get('type') === 'dropdown' ? 'Dropdown parents have no URL — they just open the flyout.' : 'Path starting with / (e.g. /, /about-us/, /ast-services/) or full https://… for external links.')
                            ->visible(fn (Forms\Get $get): bool => $get('type') !== 'dropdown')
                            ->required(fn (Forms\Get $get): bool => $get('type') !== 'dropdown')
                            ->columnSpanFull()
                            ->rules([
                                'nullable',
                                'string',
                                'max:500',
                                function (): Closure {
                                    return function (string $attribute, $value, Closure $fail): void {
                                        if ($value === null || $value === '') {
                                            return;
                                        }

                                        if ($value === '#') {
                                            return;
                                        }

                                        if (str_starts_with($value, '/') || str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                                            return;
                                        }

                                        $fail('URL must start with /, http://, https:// or be #.');
                                    };
                                },
                            ])
                            ->dehydrated(fn ($state, Forms\Get $get): bool => $get('type') !== 'dropdown' && filled($state)),
                    ]),
                Forms\Components\Section::make('Visibility & Ordering')
                    ->description('Control display and position in the Primary navbar.')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Auto — next available')
                            ->suffix('← lower first')
                            ->helperText('Lower = first in navbar (ordered by sort_order, then label).')
                            ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->helperText('Hidden = kept but not shown.')
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('gray')
                            ->default(true)
                            ->required(),
                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('Open in new tab')
                            ->helperText('For external links.')
                            ->inline(false)
                            ->default(false)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (NavigationItem $record): ?string => $record->url)
                    ->formatStateUsing(function (string $state, NavigationItem $record): string {
                        $depth = 0;
                        try {
                            $depth = $record->chainDepth();
                        } catch (\Throwable $e) {
                        }

                        return ($depth > 0 ? str_repeat('— ', $depth) : '').$state;
                    })
                    ->tooltip(fn (NavigationItem $record): ?string => $record->url),
                Tables\Columns\TextColumn::make('parent.label')
                    ->label('Parent')
                    ->placeholder('— Top level —')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->limit(28)
                    ->copyable()
                    ->copyMessage('Copied URL')
                    ->tooltip(fn (NavigationItem $record): string => $record->url ?? ''),
                Tables\Columns\ToggleColumn::make('is_visible')
                    ->label('Visible')
                    ->onColor('success')
                    ->offColor('gray'),
                Tables\Columns\IconColumn::make('open_in_new_tab')
                    ->label('New tab')
                    ->boolean()
                    ->tooltip(fn (NavigationItem $record): string => $record->open_in_new_tab ? 'Opens in new tab' : 'Same tab'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip('Lower = first'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->emptyStateHeading('No navigation items yet')
            ->emptyStateDescription('Create your Primary navbar items — labels, URLs and visibility are edited here. Run migrations if the table is missing.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn (): bool => Schema::hasTable('navigation_items')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Visible'),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Level')
                    ->options([
                        'top' => 'Top level only',
                        'child' => 'Children only',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === 'top') {
                            return $query->whereNull('parent_id');
                        }

                        if ($value === 'child') {
                            return $query->whereNotNull('parent_id');
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (NavigationItem $record): bool => auth()->user()->can('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (NavigationItem $record): bool => auth()->user()->can('delete', $record)),
                Tables\Actions\Action::make('toggleVisible')
                    ->label(fn (NavigationItem $record): string => $record->is_visible ? 'Hide' : 'Show')
                    ->icon(fn (NavigationItem $record): string => $record->is_visible ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (NavigationItem $record): string => $record->is_visible ? 'warning' : 'success')
                    ->iconButton()
                    ->tooltip(fn (NavigationItem $record): string => $record->is_visible ? 'Hide from navbar' : 'Show in navbar')
                    ->action(function (NavigationItem $record): void {
                        $record->update(['is_visible' => ! $record->is_visible]);
                    })
                    ->visible(fn (NavigationItem $record): bool => auth()->user()->can('update', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggleVisibleBulk')
                        ->label('Toggle visibility')
                        ->icon('heroicon-m-eye')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_visible' => ! $record->is_visible]);
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
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
            'index' => Pages\ListNavigationItems::route('/'),
            'create' => Pages\CreateNavigationItem::route('/create'),
            'edit' => Pages\EditNavigationItem::route('/{record}/edit'),
        ];
    }
}
