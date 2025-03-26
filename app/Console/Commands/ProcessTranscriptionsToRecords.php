<?php

namespace App\Console\Commands;

use App\Models\Transcription;
use App\Services\RecordService;
use Illuminate\Console\Command;

class ProcessTranscriptionsToRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-transcriptions-to-records {--transcription_id= : ID de una transcripción específica para procesar} {--force : Forzar el reprocesamiento incluso si ya existe un acta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa las transcripciones existentes y genera actas para ellas';

    /**
     * Execute the console command.
     */
    public function handle(RecordService $recordService)
    {
        $transcriptionId = $this->option('transcription_id');
        $force = $this->option('force');

        $query = Transcription::query()
            ->whereHas('recording', function ($query) {
                $query->where('status', 'completed');
            });

        if ($transcriptionId) {
            $query->where('id', $transcriptionId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->error('No se encontraron transcripciones para procesar.');
            return 1;
        }

        $this->info("Se encontraron {$total} transcripciones para procesar.");

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($query->cursor() as $transcription) {
            try {
                // Verificar si ya existe un acta para esta transcripción
                if (!$force && $transcription->record) {
                    $this->line(" - Transcripción #{$transcription->id}: Ya tiene un acta (ID: {$transcription->record->id}). Omitiendo...");
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Procesar la transcripción y crear el acta
                $record = $recordService->processTranscription($transcription);

                if ($record) {
                    $processed++;
                    $this->line(" - Transcripción #{$transcription->id}: Acta creada correctamente (ID: {$record->id})");
                } else {
                    $errors++;
                    $this->error(" - Transcripción #{$transcription->id}: Error al procesar");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error(" - Transcripción #{$transcription->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Proceso completado:");
        $this->line(" - Transcripciones procesadas: {$processed}");
        $this->line(" - Transcripciones omitidas: {$skipped}");
        $this->line(" - Errores: {$errors}");

        return 0;
    }
}
