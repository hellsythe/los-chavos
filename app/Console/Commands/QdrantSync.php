<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\ServiceChatbotInfo;
use App\Models\Uniform;
use App\Services\AI\QdrantService;
use Illuminate\Console\Command;

class QdrantSync extends Command
{
    protected $signature = 'app:qdrant-sync
                            {--type=all : Tipo a sincronizar (all|schools|uniforms|services)}
                            {--truncate : Vacía las colecciones antes de sincronizar}
                            {--dry-run : No escribe en Qdrant, solo muestra lo que haría}';

    protected $description = 'Sincroniza escuelas, uniformes y/o servicios hacia Qdrant.';

    public function handle(QdrantService $qdrant): int
    {
        $type = (string) $this->option('type');
        if (! in_array($type, ['all', 'schools', 'uniforms', 'services'], true)) {
            $this->error('El --type debe ser: all|schools|uniforms|services');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $truncate = (bool) $this->option('truncate');

        $this->info("Sincronizando Qdrant: tipo={$type}".($dryRun ? ' (dry-run)' : '').($truncate ? ' (truncate)' : ''));

        $stats = [
            'schools_ok' => 0,
            'schools_fail' => 0,
            'uniforms_ok' => 0,
            'uniforms_fail' => 0,
            'services_ok' => 0,
            'services_fail' => 0,
            'errors' => [],
        ];

        if (! $dryRun) {
            try {
                $qdrant->ensureCollections();
            } catch (\Throwable $e) {
                $this->error('No se pudieron crear/verificar las colecciones: '.$e->getMessage());
                return self::FAILURE;
            }

            if ($truncate) {
                if ($type === 'all' || $type === 'schools') {
                    $qdrant->truncateCollection($qdrant->schoolsCollection());
                }
                if ($type === 'all' || $type === 'uniforms') {
                    $qdrant->truncateCollection($qdrant->uniformsCollection());
                }
                if ($type === 'all' || $type === 'services') {
                    $qdrant->truncateCollection($qdrant->servicesCollection());
                }
                $this->warn('Colecciones truncadas.');
            }
        }

        if ($type === 'all' || $type === 'schools') {
            $this->syncSchools($qdrant, $dryRun, $stats);
        }

        if ($type === 'all' || $type === 'uniforms') {
            $this->syncUniforms($qdrant, $dryRun, $stats);
        }

        if ($type === 'all' || $type === 'services') {
            $this->syncServices($qdrant, $dryRun, $stats);
        }

        if (! $dryRun) {
            $this->newLine();
            $this->info('Conteo en Qdrant después de la sincronización:');
            $this->line('  schools: '.$qdrant->countSchools());
            $this->line('  uniforms: '.$qdrant->countUniforms());
            $this->line('  services: '.$qdrant->countServices());
        }

        $this->newLine();
        $this->info('=== RESUMEN ===');
        $this->table(['Clave', 'Valor'], [
            ['schools_ok', $stats['schools_ok']],
            ['schools_fail', $stats['schools_fail']],
            ['uniforms_ok', $stats['uniforms_ok']],
            ['uniforms_fail', $stats['uniforms_fail']],
            ['services_ok', $stats['services_ok']],
            ['services_fail', $stats['services_fail']],
        ]);

        if (! empty($stats['errors'])) {
            $this->newLine();
            $this->warn('Errores (mostrando primeros 10):');
            foreach (array_slice($stats['errors'], 0, 10) as $err) {
                $this->line('  - '.$err);
            }
        }

        return self::SUCCESS;
    }

    protected function syncSchools(QdrantService $qdrant, bool $dryRun, array &$stats): void
    {
        $this->info('Sincronizando escuelas...');
        $query = School::query()->where('status', School::STATUS_ACTIVE);
        $count = $query->count();
        $this->line("  Total a procesar: {$count}");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->orderBy('id')->chunk(50, function ($schools) use ($qdrant, $dryRun, $bar, &$stats) {
            foreach ($schools as $school) {
                if ($dryRun) {
                    $stats['schools_ok']++;
                    $bar->advance();
                    continue;
                }

                try {
                    $qdrant->upsertSchool($school);
                    $stats['schools_ok']++;
                } catch (\Throwable $e) {
                    $stats['schools_fail']++;
                    $stats['errors'][] = "School #{$school->id} ({$school->name}): ".$e->getMessage();
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    protected function syncUniforms(QdrantService $qdrant, bool $dryRun, array &$stats): void
    {
        $this->info('Sincronizando uniformes...');
        $query = Uniform::query()->where('status', Uniform::STATUS_ACTIVE)->with('school');
        $count = $query->count();
        $this->line("  Total a procesar: {$count}");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->orderBy('id')->chunk(50, function ($uniforms) use ($qdrant, $dryRun, $bar, &$stats) {
            foreach ($uniforms as $uniform) {
                if ($dryRun) {
                    $stats['uniforms_ok']++;
                    $bar->advance();
                    continue;
                }

                try {
                    $qdrant->upsertUniform($uniform);
                    $stats['uniforms_ok']++;
                } catch (\Throwable $e) {
                    $stats['uniforms_fail']++;
                    $stats['errors'][] = "Uniform #{$uniform->id} ({$uniform->name}): ".$e->getMessage();
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    protected function syncServices(QdrantService $qdrant, bool $dryRun, array &$stats): void
    {
        $this->info('Sincronizando info de servicios...');
        $query = ServiceChatbotInfo::query()->where('status', ServiceChatbotInfo::STATUS_ACTIVE)->with('service');
        $count = $query->count();
        $this->line("  Total a procesar: {$count}");

        if ($count === 0) {
            return;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->orderBy('id')->chunk(50, function ($infos) use ($qdrant, $dryRun, $bar, &$stats) {
            foreach ($infos as $info) {
                if ($dryRun) {
                    $stats['services_ok']++;
                    $bar->advance();
                    continue;
                }

                try {
                    $qdrant->upsertService($info);
                    $stats['services_ok']++;
                } catch (\Throwable $e) {
                    $stats['services_fail']++;
                    $stats['errors'][] = "ServiceChatbotInfo #{$info->id}: ".$e->getMessage();
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }
}
