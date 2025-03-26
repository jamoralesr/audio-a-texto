<?php

namespace App\Filament\Resources\RecordingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecordRelationManager extends RelationManager
{
    protected static string $relationship = 'record';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Acta';
    
    protected static ?string $modelLabel = 'Acta';
    
    protected static ?string $pluralModelLabel = 'Actas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\MarkdownEditor::make('content')
                    ->label('Contenido')
                    ->required()
                    ->columnSpanFull(),
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
                Forms\Components\Toggle::make('is_edited')
                    ->label('Editado Manualmente')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.records.view', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
