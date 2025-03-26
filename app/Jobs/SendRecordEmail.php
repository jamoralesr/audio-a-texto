<?php

namespace App\Jobs;

use App\Mail\RecordReady;
use App\Models\Record;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRecordEmail implements ShouldQueue
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
        protected Record $record
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transcription = $this->record->transcription;
        $recording = $transcription->recording;
        $user = $recording->user;

        // Verificar que el acta esté completa y el email no haya sido enviado
        if (!$this->record->email_sent) {
            // Enviar el email
            Mail::to($user->email)
                ->send(new RecordReady($recording, $transcription, $this->record));

            // Actualizar el estado de envío
            $this->record->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Registrar el error
        logger()->error('Error al enviar email del acta', [
            'record_id' => $this->record->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
