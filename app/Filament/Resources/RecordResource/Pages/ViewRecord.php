<?php

namespace App\Filament\Resources\RecordResource\Pages;

use App\Filament\Resources\RecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord as BaseViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class ViewRecord extends BaseViewRecord
{
    protected static string $resource = RecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información de la Grabación')
                    ->schema([
                        TextEntry::make('transcription.recording.title')
                            ->label('Título de la Grabación'),
                        TextEntry::make('transcription.recording.description')
                            ->label('Descripción')
                            ->markdown(),
                        TextEntry::make('transcription.recording.created_at')
                            ->label('Fecha de Grabación')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('transcription.recording.duration')
                            ->label('Duración')
                            ->formatStateUsing(fn ($state) => gmdate('H:i:s', $state)),
                    ])
                    ->columns(2),
                    
                Section::make('Información del Acta')
                    ->schema([
                        TextEntry::make('language')
                            ->label('Idioma')
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'es' => 'Español',
                                'en' => 'Inglés',
                                'fr' => 'Francés',
                                'de' => 'Alemán',
                                'it' => 'Italiano',
                                'pt' => 'Portugués',
                                default => $state,
                            }),
                        TextEntry::make('service_used')
                            ->label('Servicio Utilizado')
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'openai_gpt4o' => 'OpenAI GPT-4o',
                                'openai_gpt4' => 'OpenAI GPT-4',
                                'claude' => 'Anthropic Claude',
                                'ollama' => 'Ollama',
                                'local' => 'Procesamiento Local',
                                default => $state,
                            }),
                        TextEntry::make('is_edited')
                            ->label('Editado Manualmente')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                            ->color(fn (bool $state): string => $state ? 'warning' : 'success'),
                        TextEntry::make('email_sent')
                            ->label('Email Enviado')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('email_sent_at')
                            ->label('Email Enviado el')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('created_at')
                            ->label('Creado el')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado el')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
                    
                Section::make('Contenido del Acta')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Contenido')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
