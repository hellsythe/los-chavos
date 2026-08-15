<?php

namespace App\Console\Commands;

use App\Services\BusinessInfoService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ChatbotDiagnostic extends Command
{
    protected $signature = 'app:chatbot-diagnostic
                            {--date= : Fecha a simular (Y-m-d)}
                            {--show-context : Imprime el contexto completo del bot}
                            {--show-prompt : Imprime el system prompt que se envía a OpenAI}';

    protected $description = 'Diagnostica qué información del negocio verá el bot';

    public function handle(BusinessInfoService $service): int
    {
        $service->flushCache();

        $date = $this->option('date')
            ? CarbonImmutable::parse($this->option('date'), 'America/Mexico_city')
            : CarbonImmutable::now('America/Mexico_city');

        $this->info("=== Fecha de simulación: {$date->toIso8601String()} ({$date->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')}) ===");
        $this->newLine();

        $this->info('=== 1. Datos crudos en DB (settings.business_hours) ===');
        $raw = \App\Models\Setting::query()->where('name', 'business_hours')->first();
        $this->line($raw ? $raw->value : '(NO EXISTE)');
        $this->newLine();

        $this->info('=== 2. Datos normalizados (getHours) ===');
        $hours = $service->getHours();
        if ($hours === null) {
            $this->warn('  No hay horarios configurados');
        } else {
            foreach ($hours as $day => $cfg) {
                $status = $cfg['closed'] ? 'CERRADO' : "ABIERTO {$cfg['open']}-{$cfg['close']}";
                $this->line("  $day: $status");
            }
        }
        $this->newLine();

        $this->info('=== 3. todayHours() para la fecha seleccionada ===');
        $today = $service->todayHours($date);
        if ($today === null) {
            $this->warn('  No hay configuración para hoy');
        } else {
            $status = $today['closed'] ? 'CERRADO' : "ABIERTO {$today['open']}-{$today['close']}";
            $this->line("  Estado: $status");
        }
        $this->newLine();

        $this->info('=== 4. isOpenAt() a esta hora ===');
        $open = $service->isOpenAt($date);
        $this->line('  ' . ($open ? 'ABIERTO' : 'CERRADO'));
        $this->newLine();

        $this->info('=== 5. Feriado hoy? ===');
        $holiday = $service->todayHoliday($date);
        $this->line($holiday ? 'Sí: ' . $holiday['reason'] : 'No');
        $this->newLine();

        if ($this->option('show-context') || true) {
            $this->info('=== 6. CONTEXTO QUE RECIBE EL BOT ===');
            $context = $service->getBusinessContext($date);
            $this->line($context);
            $this->newLine();

            $this->warn('⚠️  Si el contexto dice "cerrado" o "no configurado" y tú guardaste un horario, ejecuta:');
            $this->line('  php artisan cache:clear');
            $this->line('  sudo supervisorctl restart los-chavos-worker:*');
        }

        if ($this->option('show-prompt')) {
            $this->info('=== 7. SYSTEM PROMPT ACTIVO EN CONFIG ===');
            $prompt = (string) config('openai_llm.chat_bot.system_prompt');
            $this->line($prompt);
            $this->newLine();

            $this->info('=== 8. SYSTEM PROMPT EN BD (si fue configurado vía .env) ===');
            $envPrompt = env('CHAT_BOT_SYSTEM_PROMPT');
            $this->line($envPrompt ?: '(no está en .env, usa el default)');
        }

        return self::SUCCESS;
    }
}
