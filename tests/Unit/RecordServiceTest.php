<?php

namespace Tests\Unit;

use App\Models\Record;
use App\Models\Recording;
use App\Models\Transcription;
use App\Models\User;
use App\Services\AITextProcessingService;
use App\Services\RecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class RecordServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $aiService;
    protected $recordService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock del servicio de IA
        $this->aiService = Mockery::mock(AITextProcessingService::class);
        
        // Crear el servicio con el mock
        $this->recordService = new RecordService($this->aiService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_processes_transcription_and_creates_record()
    {
        // Crear un usuario, grabación y transcripción de prueba
        $user = User::factory()->create();
        
        $recording = Recording::create([
            'user_id' => $user->id,
            'title' => 'Test Recording',
            'description' => 'Test Description',
            'file_path' => 'recordings/test.mp3',
            'file_name' => 'test.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 1000,
            'duration' => 60,
            'status' => 'completed',
            'is_public' => false,
            'processed_at' => now(),
        ]);
        
        $transcription = Transcription::create([
            'recording_id' => $recording->id,
            'content' => 'Esta es una transcripción de prueba para verificar el procesamiento de actas.',
            'language' => 'es',
            'service_used' => 'openai_whisper',
            'service_response' => ['test' => true],
            'confidence_score' => 0.95,
            'is_edited' => false,
            'email_sent' => false,
        ]);

        // Configurar el mock para que devuelva un contenido procesado
        $processedContent = "# Acta de Reunión\n\n## Participantes\n- Usuario de prueba\n\n## Contenido\nEsta es un acta procesada de prueba.";
        
        $this->aiService->shouldReceive('processWithOpenAI')
            ->once()
            ->with($transcription->content, Mockery::any(), 1500)
            ->andReturn($processedContent);

        // Ejecutar el método a probar
        $record = $this->recordService->processTranscription($transcription);

        // Verificar que se creó el acta correctamente
        $this->assertInstanceOf(Record::class, $record);
        $this->assertEquals($transcription->id, $record->transcription_id);
        $this->assertEquals($processedContent, $record->content);
        $this->assertEquals('es', $record->language);
        $this->assertEquals('openai_gpt4o', $record->service_used);
        $this->assertFalse($record->is_edited);
        $this->assertFalse($record->email_sent);
        $this->assertNull($record->email_sent_at);
    }

    /** @test */
    public function it_handles_error_when_ai_processing_fails()
    {
        // Crear un usuario, grabación y transcripción de prueba
        $user = User::factory()->create();
        
        $recording = Recording::create([
            'user_id' => $user->id,
            'title' => 'Test Recording',
            'description' => 'Test Description',
            'file_path' => 'recordings/test.mp3',
            'file_name' => 'test.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 1000,
            'duration' => 60,
            'status' => 'completed',
            'is_public' => false,
            'processed_at' => now(),
        ]);
        
        $transcription = Transcription::create([
            'recording_id' => $recording->id,
            'content' => 'Esta es una transcripción de prueba para verificar el procesamiento de actas.',
            'language' => 'es',
            'service_used' => 'openai_whisper',
            'service_response' => ['test' => true],
            'confidence_score' => 0.95,
            'is_edited' => false,
            'email_sent' => false,
        ]);

        // Configurar el mock para que simule un error
        $this->aiService->shouldReceive('processWithOpenAI')
            ->once()
            ->with($transcription->content, Mockery::any(), 1500)
            ->andReturn('Error');

        // Ejecutar el método a probar
        $record = $this->recordService->processTranscription($transcription);

        // Verificar que no se creó el acta
        $this->assertNull($record);
        $this->assertEquals(0, Record::count());
    }
}
