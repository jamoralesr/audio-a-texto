<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranscriptionResource\Pages;
use App\Filament\Resources\TranscriptionResource\RelationManagers;
use App\Models\Transcription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TranscriptionResource extends Resource
{
    protected static ?string $model = Transcription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Contenido';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $modelLabel = 'Transcripción';
    
    protected static ?string $pluralModelLabel = 'Transcripciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('recording_id')
                            ->label('Grabación')
                            ->relationship('recording', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3),
                            ]),
                        Forms\Components\Select::make('language')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'Inglés',
                                'fr' => 'Francés',
                                'de' => 'Alemán',
                                'it' => 'Italiano',
                                'pt' => 'Portugués',
                            ])
                            ->default('es')
                            ->required(),
                        Forms\Components\Select::make('service_used')
                            ->label('Servicio Utilizado')
                            ->options([
                                'google' => 'Google Speech-to-Text',
                                'azure' => 'Microsoft Azure Speech',
                                'aws' => 'Amazon Transcribe',
                                'whisper' => 'OpenAI Whisper',
                                'local' => 'Procesamiento Local',
                            ])
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Contenido de la Transcripción')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
                            ->required()
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Detalles Técnicos')
                    ->schema([
                        Forms\Components\TextInput::make('confidence_score')
                            ->label('Puntuación de Confianza')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->suffix('%')
                            ->disabled(),
                        Forms\Components\Toggle::make('is_edited')
                            ->label('Editado Manualmente')
                            ->helperText('Indica si la transcripción ha sido editada manualmente')
                            ->default(false),
                        Forms\Components\Toggle::make('email_sent')
                            ->label('Email Enviado')
                            ->helperText('Indica si se ha enviado un email con la transcripción')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('email_sent_at')
                            ->label('Email Enviado el')
                            ->disabled(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Respuesta del Servicio')
                    ->schema([
                        Forms\Components\Textarea::make('service_response')
                            ->label('Respuesta JSON del Servicio')
                            ->columnSpanFull()
                            ->disabled()
                            ->rows(10),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recording.title')
                    ->label('Grabación')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('content')
                    ->label('Contenido')
                    ->html()
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('language')
                    ->label('Idioma')
                    ->colors([
                        'primary' => 'es',
                        'success' => 'en',
                        'warning' => fn ($state) => in_array($state, ['fr', 'de', 'it', 'pt']),
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'es' => 'Español',
                        'en' => 'Inglés',
                        'fr' => 'Francés',
                        'de' => 'Alemán',
                        'it' => 'Italiano',
                        'pt' => 'Portugués',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('service_used')
                    ->label('Servicio')
                    ->colors([
                        'primary' => 'google',
                        'success' => 'azure',
                        'warning' => 'aws',
                        'danger' => 'whisper',
                        'gray' => 'local',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'google' => 'Google',
                        'azure' => 'Azure',
                        'aws' => 'AWS',
                        'whisper' => 'Whisper',
                        'local' => 'Local',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Confianza')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state * 100, 1) . '%' : 'N/A')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_edited')
                    ->label('Editado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('email_sent')
                    ->label('Email')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('recording_id')
                    ->label('Grabación')
                    ->relationship('recording', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Español',
                        'en' => 'Inglés',
                        'fr' => 'Francés',
                        'de' => 'Alemán',
                        'it' => 'Italiano',
                        'pt' => 'Portugués',
                    ]),
                Tables\Filters\SelectFilter::make('service_used')
                    ->label('Servicio')
                    ->options([
                        'google' => 'Google Speech-to-Text',
                        'azure' => 'Microsoft Azure Speech',
                        'aws' => 'Amazon Transcribe',
                        'whisper' => 'OpenAI Whisper',
                        'local' => 'Procesamiento Local',
                    ]),
                Tables\Filters\TernaryFilter::make('is_edited')
                    ->label('Editado')
                    ->placeholder('Todas las transcripciones')
                    ->trueLabel('Solo editadas')
                    ->falseLabel('Solo no editadas'),
                Tables\Filters\TernaryFilter::make('email_sent')
                    ->label('Email Enviado')
                    ->placeholder('Todas las transcripciones')
                    ->trueLabel('Solo con email enviado')
                    ->falseLabel('Solo sin email enviado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            // No necesitamos relaciones adicionales por ahora
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranscriptions::route('/'),
            'create' => Pages\CreateTranscription::route('/create'),
            'edit' => Pages\EditTranscription::route('/{record}/edit'),
        ];
    }
}
