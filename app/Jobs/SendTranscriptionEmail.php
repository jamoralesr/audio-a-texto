<?php

namespace App\Jobs;

use App\Mail\TranscriptionReady;
use App\Models\Transcription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTranscriptionEmail implements ShouldQueue
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
    public function handle(): void
    {
        $recording = $this->transcription->recording;
        $user = $recording->user;

        // Verificar que la transcripción esté completa y el email no haya sido enviado
        if (!$this->transcription->email_sent && $recording->status === 'completed') {
            // Enviar el email
            Mail::to($user->email)
                ->send(new TranscriptionReady($recording, $this->transcription));

            // Actualizar el estado de envío
            $this->transcription->update([
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
        logger()->error('Error al enviar email de transcripción', [
            'transcription_id' => $this->transcription->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
