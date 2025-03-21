<?php

namespace App\Filament\Resources\TranscriptionResource\Pages;

use App\Filament\Resources\TranscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTranscriptions extends ListRecords
{
    protected static string $resource = TranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
