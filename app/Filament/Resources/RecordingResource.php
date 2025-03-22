<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecordingResource\Pages;
use App\Filament\Resources\RecordingResource\RelationManagers;
use App\Models\Recording;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;

class RecordingResource extends Resource
{
    protected static ?string $model = Recording::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $modelLabel = 'Grabación';
    
    protected static ?string $pluralModelLabel = 'Grabaciones';
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Get the current authenticated user
        $user = Filament::auth()->user();
        
        // If the user is not a super admin (ID 1), only show their own recordings
        // This is a simple approach that ensures users can only see their own content
        if ($user->id !== 1) {
            $query->where('user_id', $user->id);
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => Filament::auth()->user()->id)
                            ->disabled(fn () => Filament::auth()->user()->id !== 1)
                            ->visible(fn () => Filament::auth()->user()->id === 1),
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Archivo de Audio')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Archivo de Audio')
                            ->disk('public')
                            ->directory('recordings')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/ogg'])
                            ->maxSize(config('app.file_upload_max_size'))
                            ->downloadable()
                            ->openable()
                            ->required()
                            ->hiddenOn('edit'),
                        Forms\Components\View::make('filament.resources.recording.audio-player')
                            ->label('Reproductor de Audio')
                            ->visibleOn('edit'),
                        Forms\Components\TextInput::make('file_name')
                            ->label('Nombre del Archivo')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\TextInput::make('mime_type')
                            ->label('Tipo MIME')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\TextInput::make('file_size')
                            ->label('Tamaño del Archivo (bytes)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            //->formatStateUsing(fn (int $state): string => number_format($state / 1024 / 1024, 2) . ' MB')
                            ->visibleOn('edit'),
                        Forms\Components\TextInput::make('duration')
                            ->label('Duración (segundos)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            //->formatStateUsing(fn (int $state): string => gmdate('H:i:s', $state))
                            ->visibleOn('edit'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Estado y Configuración')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'processing' => 'Procesando',
                                'completed' => 'Completado',
                                'failed' => 'Fallido',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Público')
                            ->helperText('Si está marcado, la grabación será visible para todos los usuarios')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Procesado el')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Filament::auth()->user()->id === 1),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duración')
                    ->formatStateUsing(fn (int $state): string => gmdate('H:i:s', $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024 / 1024, 2) . ' MB')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean(),
                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Procesado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'failed' => 'Fallido',
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Visibilidad')
                    ->placeholder('Todas las grabaciones')
                    ->trueLabel('Solo públicas')
                    ->falseLabel('Solo privadas'),
                Tables\Filters\TrashedFilter::make(),
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
            // Comentado hasta crear el RelationManager
            // RelationManagers\TranscriptionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecordings::route('/'),
            'create' => Pages\CreateRecording::route('/create'),
            'edit' => Pages\EditRecording::route('/{record}/edit'),
        ];
    }
}
