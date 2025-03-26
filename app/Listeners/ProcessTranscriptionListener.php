<?php

namespace App\Listeners;

use App\Events\TranscriptionCreated;
use App\Jobs\ProcessTranscriptionToRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessTranscriptionListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TranscriptionCreated $event): void
    {
        $transcription = $event->transcription;
        
        // Registrar en el log que se ha recibido el evento
        Log::info('Evento TranscriptionCreated recibido', ['transcription_id' => $transcription->id]);
        
        // Verificar que la transcripción esté completa
        if ($transcription->recording->status === 'completed') {
            Log::info('Programando job ProcessTranscriptionToRecord', ['transcription_id' => $transcription->id]);
            
            // Programar el job para procesar la transcripción y generar el acta
            ProcessTranscriptionToRecord::dispatch($transcription);
        } else {
            Log::warning('La grabación no está completa, no se procesará la transcripción', [
                'transcription_id' => $transcription->id,
                'recording_status' => $transcription->recording->status
            ]);
        }
    }
}
