<?php

namespace App\Filament\Resources\TranscriptionResource\Pages;

use App\Filament\Resources\TranscriptionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class ViewTranscription extends ViewRecord
{
    protected static string $resource = TranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detalles de la Transcripción')
                    ->schema([
                        TextEntry::make('recording.title')
                            ->label('Grabación'),
                        TextEntry::make('language')
                            ->label('Idioma')
                            ->badge(),
                        TextEntry::make('service_used')
                            ->label('Servicio Utilizado')
                            ->badge(),
                        TextEntry::make('confidence_score')
                            ->label('Puntuación de Confianza')
                            ->formatStateUsing(fn ($state) => number_format($state * 100, 2) . '%'),
                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime(),
                    ])
                    ->columns(2),
                
                Section::make('Contenido de la Transcripción')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Transcripción')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Información Técnica')
                    ->schema([
                        TextEntry::make('is_edited')
                            ->label('Editado')
                            ->boolean(),
                        TextEntry::make('email_sent')
                            ->label('Email Enviado')
                            ->boolean(),
                        TextEntry::make('email_sent_at')
                            ->label('Fecha de Envío de Email')
                            ->dateTime(),
                        TextEntry::make('service_response')
                            ->label('Respuesta del Servicio')
                            ->json(),
                    ])
                    ->collapsible(),
            ]);
    }
}
