<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaUploader;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload')
                ->label('Upload images')
                ->icon('heroicon-m-arrow-up-tray')
                ->form([
                    FileUpload::make('media')
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->maxSize(5120)
                        ->required()
                        ->saveUploadedFileUsing(function (FileUpload $component, UploadedFile $file, string|array|null $state): string {
                            $media = app(MediaUploader::class)->store($file, auth()->id());

                            return $media->path;
                        }),
                    Forms\Components\TextInput::make('alt_text')
                        ->nullable()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    if (blank($data['media'] ?? null)) {
                        return;
                    }

                    if (filled($data['alt_text'] ?? null)) {
                        Media::whereIn('path', $data['media'])->update(['alt_text' => $data['alt_text']]);
                    }
                }),
        ];
    }
}
