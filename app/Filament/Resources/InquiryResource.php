<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InquiryResource extends Resource
{
    public const STATUSES = ['new', 'in_progress', 'resolved', 'archived'];

    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isAdmin() || (bool) $user->is_admin);
    }

    public static function statusOptions(): array
    {
        return [
            'new' => 'New',
            'in_progress' => 'In progress',
            'resolved' => 'Resolved',
            'archived' => 'Archived',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->nullable()
                    ->maxLength(255)
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                Forms\Components\TextInput::make('subject')
                    ->nullable()
                    ->maxLength(255)
                    ->dehydrated(fn ($state): bool => $state !== null && $state !== ''),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_read')
                    ->default(false),
                Forms\Components\Select::make('status')
                    ->options(self::statusOptions())
                    ->default('new')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('message')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),
                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn (Inquiry $record): string => $record->status)
                    ->colors([
                        'info' => 'new',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'gray' => 'archived',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Read state'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn (Inquiry $record): bool => auth()->user()->can('view', $record)),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Inquiry $record): bool => auth()->user()->can('update', $record)),
                Tables\Actions\Action::make('toggle_read')
                    ->label(fn (Inquiry $record): string => $record->is_read ? 'Mark unread' : 'Mark read')
                    ->icon(fn (Inquiry $record): string => $record->is_read ? 'heroicon-m-envelope-open' : 'heroicon-m-envelope')
                    ->visible(fn (Inquiry $record): bool => auth()->user()->can('update', $record))
                    ->action(fn (Inquiry $record) => $record->update(['is_read' => ! $record->is_read])),
                Tables\Actions\Action::make('update_status')
                    ->label('Update status')
                    ->icon('heroicon-m-arrow-path')
                    ->visible(fn (Inquiry $record): bool => auth()->user()->can('update', $record))
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(self::statusOptions())
                            ->required()
                            ->rules(['in:new,in_progress,resolved,archived']),
                    ])
                    ->action(fn (Inquiry $record, array $data) => $record->update(['status' => $data['status']])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_read')
                        ->label('Mark as read')
                        ->icon('heroicon-m-envelope-open')
                        ->action(fn (Collection $records) => $records->each->update(['is_read' => true])),
                    Tables\Actions\BulkAction::make('mark_unread')
                        ->label('Mark as unread')
                        ->icon('heroicon-m-envelope')
                        ->action(fn (Collection $records) => $records->each->update(['is_read' => false])),
                    Tables\Actions\BulkAction::make('set_status')
                        ->label('Set status')
                        ->icon('heroicon-m-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options(self::statusOptions())
                                ->required()
                                ->rules(['in:new,in_progress,resolved,archived']),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']])),
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
            'index' => Pages\ListInquiries::route('/'),
            'view' => Pages\ViewInquiry::route('/{record}'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
