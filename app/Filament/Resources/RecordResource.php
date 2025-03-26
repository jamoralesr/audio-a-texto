<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecordResource\Pages;
use App\Models\Record;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;

class RecordResource extends Resource
{
    protected static ?string $model = Record::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $modelLabel = 'Acta';
    
    protected static ?string $pluralModelLabel = 'Actas';
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Get the current authenticated user
        $user = Filament::auth()->user();
        
        // If the user is not a super admin (ID 1), only show records of their own transcriptions
        if ($user->id !== 1) {
            $query->whereHas('transcription.recording', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('transcription_id')
                            ->label('Transcripción')
                            ->relationship('transcription', 'id', function (Builder $query) {
                                // Get the current authenticated user
                                $user = Filament::auth()->user();
                                
                                // If the user is not a super admin (ID 1), only show their own transcriptions
                                if ($user->id !== 1) {
                                    $query->whereHas('recording', function (Builder $query) use ($user) {
                                        $query->where('user_id', $user->id);
                                    });
                                }
                                
                                return $query;
                            })
                            ->getOptionLabelFromRecordUsing(fn (Record $record) => 
                                optional($record->transcription->recording)->title . ' (ID: ' . $record->transcription->id . ')')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                                'openai_gpt4o' => 'OpenAI GPT-4o',
                                'openai_gpt4' => 'OpenAI GPT-4',
                                'claude' => 'Anthropic Claude',
                                'ollama' => 'Ollama',
                                'local' => 'Procesamiento Local',
                            ])
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Contenido del Acta')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Detalles Adicionales')
                    ->schema([
                        Forms\Components\Toggle::make('is_edited')
                            ->label('Editado Manualmente')
                            ->helperText('Indica si el acta ha sido editada manualmente')
                            ->default(false),
                        Forms\Components\Toggle::make('email_sent')
                            ->label('Email Enviado')
                            ->helperText('Indica si se ha enviado un email con el acta')
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
                Tables\Columns\TextColumn::make('transcription.recording.user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Filament::auth()->user()->id === 1),
                Tables\Columns\TextColumn::make('transcription.recording.title')
                    ->label('Grabación')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma')
                    ->badge()
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
                Tables\Columns\TextColumn::make('service_used')
                    ->label('Servicio')
                    ->badge()
                    ->colors([
                        'primary' => 'openai_gpt4o',
                        'success' => 'openai_gpt4',
                        'warning' => 'claude',
                        'danger' => 'ollama',
                        'gray' => 'local',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'openai_gpt4o' => 'GPT-4o',
                        'openai_gpt4' => 'GPT-4',
                        'claude' => 'Claude',
                        'ollama' => 'Ollama',
                        'local' => 'Local',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_edited')
                    ->label('Editado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('email_sent')
                    ->label('Email')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
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
                        'openai_gpt4o' => 'OpenAI GPT-4o',
                        'openai_gpt4' => 'OpenAI GPT-4',
                        'claude' => 'Anthropic Claude',
                        'ollama' => 'Ollama',
                        'local' => 'Procesamiento Local',
                    ]),
                Tables\Filters\TernaryFilter::make('is_edited')
                    ->label('Editado Manualmente'),
                Tables\Filters\TernaryFilter::make('email_sent')
                    ->label('Email Enviado'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'view' => Pages\ViewRecord::route('/{record}'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }    
}
