<?php

namespace App\Console\Commands;

use App\Jobs\SendRecordEmail;
use App\Models\Record;
use Illuminate\Console\Command;

class SendPendingRecordEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-pending-record-emails {--record_id= : ID de un acta específica para enviar} {--force : Forzar el reenvío incluso si ya se envió un email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía emails de actas pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recordId = $this->option('record_id');
        $force = $this->option('force');

        $query = Record::query()
            ->whereHas('transcription', function ($query) {
                $query->whereHas('recording', function ($query) {
                    $query->where('status', 'completed');
                });
            });

        if (!$force) {
            $query->where('email_sent', false);
        }

        if ($recordId) {
            $query->where('id', $recordId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->error('No se encontraron actas pendientes para enviar por email.');
            return 1;
        }

        $this->info("Se encontraron {$total} actas para enviar por email.");

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($query->cursor() as $record) {
            try {
                // Verificar si ya se envió un email para esta acta
                if (!$force && $record->email_sent) {
                    $this->line(" - Acta #{$record->id}: Ya se envió un email. Omitiendo...");
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Enviar email con el acta
                SendRecordEmail::dispatch($record);

                $processed++;
                $this->line(" - Acta #{$record->id}: Email enviado correctamente");
            } catch (\Exception $e) {
                $errors++;
                $this->error(" - Acta #{$record->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Proceso completado:");
        $this->line(" - Actas procesadas: {$processed}");
        $this->line(" - Actas omitidas: {$skipped}");
        $this->line(" - Errores: {$errors}");

        return 0;
    }
}
