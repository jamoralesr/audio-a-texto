<?php

namespace App\Console\Commands;

use App\Models\Transcription;
use App\Services\RecordService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestProcessTranscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-process-transcription {transcription_id : ID de la transcripción a procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el procesamiento de una transcripción a acta';

    /**
     * Execute the console command.
     */
    public function handle(RecordService $recordService)
    {
        $transcriptionId = $this->argument('transcription_id');
        
        $this->info("Buscando transcripción con ID: {$transcriptionId}");
        
        $transcription = Transcription::find($transcriptionId);
        
        if (!$transcription) {
            $this->error("No se encontró la transcripción con ID: {$transcriptionId}");
            return 1;
        }
        
        $this->info("Transcripción encontrada: {$transcription->id}");
        $this->line("Contenido de la transcripción (primeros 100 caracteres): " . substr($transcription->content, 0, 100) . "...");
        
        $this->info("Procesando transcripción a acta...");
        
        try {
            // Configurar el nivel de log para ver más detalles
            Log::withContext(['command' => 'test-process-transcription', 'transcription_id' => $transcription->id]);
            
            // Procesar la transcripción
            $record = $recordService->processTranscription($transcription);
            
            if ($record) {
                $this->info("Acta creada correctamente con ID: {$record->id}");
                $this->line("Contenido del acta (primeros 100 caracteres): " . substr($record->content, 0, 100) . "...");
                return 0;
            } else {
                $this->error("No se pudo crear el acta. Revisa los logs para más detalles.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error al procesar la transcripción: " . $e->getMessage());
            $this->line("Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")");
            $this->line("Traza: " . $e->getTraceAsString());
            return 1;
        }
    }
}
