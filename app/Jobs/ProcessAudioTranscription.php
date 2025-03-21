<?php

namespace App\Jobs;

use App\Models\Recording;
use App\Services\TranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAudioTranscription implements ShouldQueue
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
     * The recording instance.
     *
     * @var \App\Models\Recording
     */
    protected $recording;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Recording  $recording
     * @return void
     */
    public function __construct(Recording $recording)
    {
        $this->recording = $recording;
    }

    /**
     * Execute the job.
     *
     * @param  \App\Services\TranscriptionService  $transcriptionService
     * @return void
     */
    public function handle(TranscriptionService $transcriptionService)
    {
        Log::info('Starting transcription job', ['recording_id' => $this->recording->id]);

        // Update recording status to processing
        $this->recording->update(['status' => 'processing']);

        // Process the transcription
        $transcription = $transcriptionService->transcribe($this->recording);

        if ($transcription) {
            Log::info('Transcription completed successfully', [
                'recording_id' => $this->recording->id,
                'transcription_id' => $transcription->id,
            ]);
        } else {
            Log::error('Transcription failed', ['recording_id' => $this->recording->id]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Transcription job failed', [
            'recording_id' => $this->recording->id,
            'exception' => $exception->getMessage(),
        ]);

        // Update recording status to failed
        $this->recording->update([
            'status' => 'failed',
            'metadata' => array_merge($this->recording->metadata ?? [], [
                'error' => $exception->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }
}
