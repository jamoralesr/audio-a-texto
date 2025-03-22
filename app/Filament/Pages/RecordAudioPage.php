<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Recording;

class RecordAudioPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-s-microphone';

    protected static string $view = 'filament.pages.record-audio-page';

    protected static ?string $navigationLabel = 'Grabar Audio';

    protected static ?string $title = 'Grabación de Audio';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    // Variables para el estado de la grabación
    public $isRecording = false;
    public $isPaused = false;
    public $recordingTime = 0;
    public $audioBlob = null;
    public $audioUrl = null;
    public $audioFile = null;
    public $audioDuration = 0;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(1000),
                Toggle::make('is_public')
                    ->label('Público')
                    ->helperText('Si está activado, la grabación será visible para otros usuarios')
                    ->default(false),
            ])
            ->statePath('data');
    }

    public function updateRecordingTime($time): void
    {
        $this->recordingTime = $time;
        $this->audioDuration = $time;
    }

    public function toggleRecording(): void
    {
        $this->isRecording = !$this->isRecording;
        $this->isPaused = false;
    }

    public function togglePause(): void
    {
        if ($this->isRecording) {
            $this->isPaused = !$this->isPaused;
        }
    }

    public function stopRecording(): void
    {
        $this->isRecording = false;
        $this->isPaused = false;
    }

    public function processAudioUpload($audioData): void
    {
        // Decodificar la data URL del audio
        $audioData = str_replace('data:audio/wav;base64,', '', $audioData);
        $audioData = str_replace(' ', '+', $audioData);
        $audioDecoded = base64_decode($audioData);

        // Generar un nombre de archivo único
        $fileName = Str::uuid() . '.wav';
        $filePath = 'recordings/' . Auth::id() . '/' . $fileName;

        // Guardar el archivo en el almacenamiento
        Storage::disk('public')->put($filePath, $audioDecoded);

        // Guardar la URL del archivo para reproducción
        $this->audioFile = $filePath;
        $this->audioUrl = asset('storage/' . $filePath);

        // Notificar al usuario
        $this->dispatch('audio-processed');
    }

    public function saveRecording(): void
    {
        try {
            // Validar datos del formulario
            $data = $this->form->getState();

            // Validar que se haya grabado audio
            if (!$this->audioFile) {
                $this->addError('audioFile', 'Debe grabar audio antes de guardar');
                return;
            }

            // Crear el registro en la base de datos
            $recording = Recording::create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'file_path' => $this->audioFile,
                'file_name' => basename($this->audioFile),
                'mime_type' => 'audio/wav',
                'file_size' => Storage::disk('public')->size($this->audioFile),
                'duration' => $this->audioDuration,
                'status' => 'pending',
                'metadata' => [
                    'browser' => request()->header('User-Agent'),
                    'recorded_at' => now()->toDateTimeString(),
                ],
                'is_public' => $data['is_public'] ?? false,
            ]);

            // Enviar a la cola para procesamiento de transcripción
            \App\Jobs\ProcessAudioTranscription::dispatch($recording);

            // Mostrar notificación de éxito
            $this->dispatch('show-success-notification', [
                'title' => 'Grabación guardada',
                'message' => 'La grabación se ha guardado correctamente y se está procesando la transcripción',
            ]);

            // Reiniciar el formulario y el estado de grabación
            $this->form->fill();
            $this->resetRecording();

        } catch (Halt $exception) {
            // Falló la validación del formulario
        } catch (\Exception $e) {
            // Error al guardar la grabación
            $this->addError('general', 'Error al guardar la grabación: ' . $e->getMessage());
        }
    }

    public function resetRecording(): void
    {
        $this->isRecording = false;
        $this->isPaused = false;
        $this->recordingTime = 0;
        $this->audioBlob = null;
        $this->audioUrl = null;
        $this->audioFile = null;
        $this->audioDuration = 0;
        $this->dispatch('reset-audio-recorder');
    }
}
