<?php

namespace App\Services;

use App\Models\Record;
use App\Models\Transcription;
use Illuminate\Support\Facades\Log;

class RecordService
{
    protected $aiService;
    
    public function __construct(AITextProcessingService $aiService)
    {
        $this->aiService = $aiService;
    }
    
    /**
     * Process a transcription and create a record using AI
     *
     * @param Transcription $transcription
     * @return Record|null
     */
    public function processTranscription(Transcription $transcription)
    {
        try {
            Log::info('Iniciando procesamiento de transcripción a acta', [
                'transcription_id' => $transcription->id,
                'recording_id' => $transcription->recording_id
            ]);
            
            // Verificar que la transcripción tenga contenido
            if (empty($transcription->content)) {
                Log::error('La transcripción no tiene contenido', ['transcription_id' => $transcription->id]);
                throw new \Exception('La transcripción no tiene contenido');
            }
            
            // Obtener el prompt para procesar la transcripción
            $prompt = $this->getPrompt($transcription->content);
            Log::debug('Prompt generado para procesar transcripción', [
                'transcription_id' => $transcription->id,
                'prompt_length' => strlen($prompt)
            ]);
            
            // Procesar con el servicio de IA (usando OpenAI por defecto)
            Log::info('Enviando solicitud a OpenAI', ['transcription_id' => $transcription->id]);
            $processedContent = $this->aiService->processWithOpenAI(
                $prompt, // El prompt del sistema ahora es el que contiene las instrucciones
                1500 // Tokens máximos para la respuesta
            );
            
            // Si hubo un error en el procesamiento
            if ($processedContent === 'Error' || empty($processedContent)) {
                Log::error('Error o respuesta vacía de OpenAI', ['transcription_id' => $transcription->id]);
                throw new \Exception('Error al procesar la transcripción con IA');
            }
            
            Log::info('Respuesta recibida de OpenAI', [
                'transcription_id' => $transcription->id,
                'response_length' => strlen($processedContent)
            ]);
            
            // Crear un nuevo registro de acta
            $record = new Record([
                'transcription_id' => $transcription->id,
                'content' => $processedContent,
                'language' => $transcription->language,
                'service_used' => 'openai_gpt4o', // O el servicio que se haya usado
                'service_response' => [
                    'processed_at' => now()->toDateTimeString(),
                    'model' => 'gpt-4o',
                ],
                'is_edited' => false,
                'email_sent' => false,
            ]);
            
            // Guardar el acta
            $record->save();
            
            Log::info('Acta creada correctamente', [
                'transcription_id' => $transcription->id,
                'record_id' => $record->id
            ]);
            
            return $record;
            
        } catch (\Exception $e) {
            // Registrar el error
            Log::error('Error al procesar transcripción para acta: ' . $e->getMessage(), [
                'transcription_id' => $transcription->id,
                'exception' => $e,
            ]);
            
            return null;
        }
    }
    
    /**
     * Get the system prompt for AI processing
     * 
     * @return string
     */
    protected function getPrompt(string $transcriptionContent): string
    {
        return "Eres un asistente experto en la redacción de actas médicas y en la extracción de información clínica estructurada. A partir de la transcripción de una consulta ginecológica que te proporcionaré, tu tarea es:

1.⁠ ⁠Redactar un acta formal que recoja de manera clara y concisa los aspectos más relevantes de la consulta, tales como datos del paciente, motivo de consulta, antecedentes, hallazgos clínicos, diagnósticos, tratamientos y recomendaciones.

2.⁠ ⁠Extraer y organizar la información en un formato estructurado (por ejemplo, en JSON) que contenga los siguientes campos:
   - datosPaciente: { nombre, edad, otros datos relevantes }
   - fechaConsulta
   - motivoConsulta
   - antecedentes
   - hallazgosClinicos
   - diagnostico
   - planTratamiento
   - recomendaciones
   - citasSeguimiento

Asegúrate de mantener una redacción formal y precisa. Transcripción: {$transcriptionContent}";
    }
}
