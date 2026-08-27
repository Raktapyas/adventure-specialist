<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Media';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ViewColumn::make('thumbnail')
                        ->view('filament.tables.columns.media-thumb'),
                    Tables\Columns\TextColumn::make('name')
                        ->searchable(['name', 'path'])
                        ->weight('bold')
                        ->description(fn (Media $record): string => $record->humanSize()),
                ]),
            ])
            ->contentGrid(['md' => 3, 'xl' => 4])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['image' => 'Images', 'video' => 'Videos']),
                Tables\Filters\SelectFilter::make('extension')
                    ->options(collect(config('media.allowed_extensions'))->mapWithKeys(fn ($e) => [$e => strtoupper($e)])->all()),
                Tables\Filters\TernaryFilter::make('is_legacy')
                    ->label('Source')
                    ->trueLabel('Legacy')
                    ->falseLabel('Uploaded'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->visible(fn (Media $record): bool => auth()->user()->can('view', $record))
                    ->modalHeading(fn (Media $record): string => $record->name)
                    ->modalContent(fn (Media $record) => view('filament.tables.media-preview', ['media' => $record]))
                    ->modalSubmitAction(false)
                    ->modalWidth('5xl'),
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->visible(fn (Media $record): bool => auth()->user()->can('delete', $record))
                    ->authorize(fn (Media $record): bool => auth()->user()->can('delete', $record))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Checkbox::make('force')
                            ->label('Force delete (bypass usage check)'),
                    ])
                    ->action(function (Media $record, array $data): void {
                        if (! ($data['force'] ?? false)) {
                            $record->load('usages');

                            if ($record->usages->isNotEmpty()) {
                                $labels = $record->usageLabels();

                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete')
                                    ->body('This file is in use by '.$record->usages->count().' item(s)'.($labels !== [] ? ': '.implode(', ', $labels) : '').'. Reassign or remove those references first, or force-delete.')
                                    ->send();

                                return;
                            }
                        }

                        DB::transaction(function () use ($record): void {
                            if (! $record->is_legacy && $record->storage_path !== null) {
                                Storage::disk($record->disk)->delete($record->storage_path);
                            }

                            $record->delete();
                        });
                    }),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withoutGlobalScopes());
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
            'index' => Pages\ListMedia::route('/'),
        ];
    }
}
