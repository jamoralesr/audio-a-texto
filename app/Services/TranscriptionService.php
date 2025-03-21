<?php

namespace App\Services;

use OpenAI\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Recording;
use App\Models\Transcription;
use Exception;

class TranscriptionService
{
    protected $client;
    protected $model;
    protected $language;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->model = config('openai.transcription_model');
        $this->language = config('openai.default_language');
    }
    
    /**
     * Transcribe an audio recording using OpenAI Whisper
     *
     * @param Recording $recording
     * @return Transcription|null
     */
    public function transcribe(Recording $recording)
    {
        try {
            // Check if the file exists
            if (!Storage::disk('public')->exists($recording->file_path)) {
                throw new Exception("Audio file not found: {$recording->file_path}");
            }
            
            // Get the file path
            $filePath = Storage::disk('public')->path($recording->file_path);
            
            // Send the file to OpenAI for transcription
            $response = $this->client->audio()->transcribe([
                'model' => $this->model,
                'file' => fopen($filePath, 'r'),
                'language' => $this->language,
                'response_format' => 'verbose_json',
            ]);
            
            // Create a new transcription record
            $transcription = new Transcription([
                'recording_id' => $recording->id,
                'content' => $response->text,
                'language' => $this->language,
                'service_used' => 'openai_whisper',
                'service_response' => [
                    'task' => $response->task ?? null,
                    'language' => $response->language ?? null,
                    'duration' => $response->duration ?? null,
                    'segments' => $response->segments ?? [],
                ],
                'confidence_score' => $this->calculateAverageConfidence($response),
                'is_edited' => false,
                'email_sent' => false,
            ]);
            
            // Save the transcription
            $transcription->save();
            
            // Update the recording status
            $recording->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
            
            return $transcription;
            
        } catch (Exception $e) {
            // Log the error
            Log::error('Transcription failed: ' . $e->getMessage(), [
                'recording_id' => $recording->id,
                'file_path' => $recording->file_path,
                'exception' => $e,
            ]);
            
            // Update the recording status
            $recording->update([
                'status' => 'failed',
                'metadata' => array_merge($recording->metadata ?? [], [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toDateTimeString(),
                ]),
            ]);
            
            return null;
        }
    }
    
    /**
     * Calculate the average confidence score from the OpenAI response
     *
     * @param object $response
     * @return float
     */
    protected function calculateAverageConfidence($response)
    {
        if (!isset($response->segments) || empty($response->segments)) {
            return 0;
        }
        
        $totalConfidence = 0;
        $segmentCount = count($response->segments);
        
        foreach ($response->segments as $segment) {
            $totalConfidence += $segment->confidence ?? 0;
        }
        
        return $segmentCount > 0 ? $totalConfidence / $segmentCount : 0;
    }
    
    /**
     * Set the language for transcription
     *
     * @param string $language
     * @return $this
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
        return $this;
    }
    
    /**
     * Set the model for transcription
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model)
    {
        $this->model = $model;
        return $this;
    }
}
