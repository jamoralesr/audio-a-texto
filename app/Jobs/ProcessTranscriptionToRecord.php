<?php

namespace App\Jobs;

use App\Models\Transcription;
use App\Services\RecordService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTranscriptionToRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Transcription $transcription
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RecordService $recordService): void
    {
        Log::info('Iniciando job ProcessTranscriptionToRecord', [
            'transcription_id' => $this->transcription->id,
            'job_id' => $this->job->getJobId() ?? 'unknown'
        ]);
        
        // Verificar que la transcripción exista y tenga contenido
        if (!$this->transcription || empty($this->transcription->content)) {
            Log::error('Transcripción inválida o sin contenido', [
                'transcription_id' => $this->transcription->id ?? 'null'
            ]);
            return;
        }
        
        // Procesar la transcripción y crear el acta
        $record = $recordService->processTranscription($this->transcription);
        
        // Si se creó el acta correctamente, programar el envío del email
        if ($record) {
            Log::info('Acta creada correctamente, programando envío de email', [
                'transcription_id' => $this->transcription->id,
                'record_id' => $record->id
            ]);
            
            // Programar el envío del email con el acta
            SendRecordEmail::dispatch($record);
        } else {
            Log::error('No se pudo crear el acta', [
                'transcription_id' => $this->transcription->id
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Registrar el error
        Log::error('Error al procesar transcripción para acta', [
            'transcription_id' => $this->transcription->id ?? 'unknown',
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
