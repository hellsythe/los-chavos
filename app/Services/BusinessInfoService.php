<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class BusinessInfoService
{
    public const HOURS_KEY = 'business_hours';
    public const HOLIDAYS_KEY = 'business_holidays';
    public const INFO_KEY = 'business_info';

    protected const CACHE_TTL_SECONDS = 3600;

    /**
     * Build a text block describing the business context for the current moment.
     * Includes: current date/dow, today's hours, today's holiday status, upcoming holidays.
     */
    public function getBusinessContext(?CarbonImmutable $now = null): string
    {
        $now ??= CarbonImmutable::now('America/Mexico_city');

        $lines = [];

        $nowEs = $now->locale('es');
        $dateText = $nowEs->isoFormat('dddd D [de] MMMM [de] YYYY, HH:mm');

        $lines[] = 'Fecha y hora actual: '.$dateText.' ('.$now->getTimezone()->getName().').';

        $hours = $this->todayHours($now);
        if ($hours === null) {
            $lines[] = 'Horario de hoy: no configurado.';
        } elseif (! empty($hours['closed'])) {
            $lines[] = 'Horario de hoy: cerrado.';
        } else {
            $lines[] = sprintf(
                'Horario de hoy (%s): %s - %s.',
                $nowEs->isoFormat('dddd'),
                $hours['open'] ?? '?',
                $hours['close'] ?? '?'
            );
        }

        $holiday = $this->todayHoliday($now);
        if ($holiday !== null) {
            $lines[] = 'Hoy es día feriado: '.$holiday['reason'].'. El negocio está cerrado.';
        }

        $upcoming = $this->upcomingHolidays(7, $now);
        if (! empty($upcoming)) {
            $list = [];
            foreach ($upcoming as $h) {
                $date = CarbonImmutable::parse($h['date'])->locale('es')->isoFormat('D [de] MMMM');
                $list[] = $date.' ('.$h['reason'].')';
            }
            $lines[] = 'Próximos días feriados: '.implode(', ', $list).'.';
        }

        $info = $this->getBusinessInfo();
        if (! empty($info)) {
            $parts = [];
            foreach (['business_name', 'address', 'phone', 'email'] as $k) {
                if (! empty($info[$k])) {
                    $labels = [
                        'business_name' => 'Nombre',
                        'address' => 'Dirección',
                        'phone' => 'Teléfono',
                        'email' => 'Email',
                    ];
                    $parts[] = $labels[$k].': '.$info[$k];
                }
            }
            if ($parts) {
                $lines[] = 'Datos del negocio: '.implode(' | ', $parts).'.';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Get the configured hours for today.
     *
     * @return array{open: ?string, close: ?string, closed: bool}|null
     */
    public function todayHours(?CarbonImmutable $now = null): ?array
    {
        $now ??= CarbonImmutable::now('America/Mexico_city');
        $hours = $this->getHours();
        if ($hours === null) {
            return null;
        }

        $dayKey = $this->dayKey($now->dayOfWeekIso);
        $day = $hours[$dayKey] ?? null;
        if ($day === null) {
            return null;
        }

        return $this->normalizeDay($day);
    }

    /**
     * Get the holiday record for today, if any.
     *
     * @return array{date: string, reason: string, closed: bool}|null
     */
    public function todayHoliday(?CarbonImmutable $now = null): ?array
    {
        $now ??= CarbonImmutable::now('America/Mexico_city');
        $holidays = $this->getHolidays();
        $today = $now->toDateString();

        foreach ($holidays as $h) {
            if (($h['date'] ?? null) === $today && ($h['closed'] ?? true)) {
                return $h;
            }
        }

        return null;
    }

    /**
     * Get holidays within the next N days.
     *
     * @return array<int, array{date: string, reason: string, closed: bool}>
     */
    public function upcomingHolidays(int $days = 7, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now('America/Mexico_city');
        $end = $now->addDays($days);
        $holidays = $this->getHolidays();

        $upcoming = [];
        foreach ($holidays as $h) {
            if (empty($h['date'])) {
                continue;
            }
            $date = CarbonImmutable::parse($h['date'])->startOfDay();
            if ($date->gte($now->startOfDay()) && $date->lte($end)) {
                $upcoming[] = $h;
            }
        }

        usort($upcoming, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $upcoming;
    }

    /**
     * Get the full weekly schedule.
     *
     * @return array<string, array{open: ?string, close: ?string, closed: bool}>|null
     */
    public function getHours(): ?array
    {
        $raw = $this->getSettingValue(self::HOURS_KEY);
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return null;
        }

        $out = [];
        foreach ($decoded as $day => $cfg) {
            $out[$day] = $this->normalizeDay($cfg);
        }
        return $out;
    }

    /**
     * Normalize a day config to always have `closed` (bool) and optionally `open`/`close`.
     * A day is considered closed if `closed === true` OR if `open`/`close` are missing.
     */
    protected function normalizeDay(mixed $cfg): array
    {
        if (! is_array($cfg)) {
            return ['closed' => true];
        }

        $hasOpen = ! empty($cfg['open']);
        $hasClose = ! empty($cfg['close']);
        $isClosed = (bool) ($cfg['closed'] ?? false) || ! $hasOpen || ! $hasClose;

        $out = ['closed' => $isClosed];
        if (! $isClosed) {
            $out['open'] = (string) $cfg['open'];
            $out['close'] = (string) $cfg['close'];
        }
        return $out;
    }

    /**
     * Get all configured holidays.
     *
     * @return array<int, array{date: string, reason: string, closed: bool}>
     */
    public function getHolidays(): array
    {
        $raw = $this->getSettingValue(self::HOLIDAYS_KEY);
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getBusinessInfo(): ?array
    {
        $raw = $this->getSettingValue(self::INFO_KEY);
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    public function isOpenAt(?CarbonImmutable $at = null): bool
    {
        $at ??= CarbonImmutable::now('America/Mexico_city');

        if ($this->todayHoliday($at) !== null) {
            return false;
        }

        $hours = $this->todayHours($at);
        if ($hours === null || ! empty($hours['closed'])) {
            return false;
        }

        if (empty($hours['open']) || empty($hours['close'])) {
            return false;
        }

        $open = $at->copy()->setTimeFromTimeString($hours['open']);
        $close = $at->copy()->setTimeFromTimeString($hours['close']);

        return $at->between($open, $close);
    }

    protected function getSettingValue(string $name): ?string
    {
        return Cache::remember('business_setting:'.$name, self::CACHE_TTL_SECONDS, function () use ($name) {
            $row = Setting::query()->where('name', $name)->first();
            return $row?->value;
        });
    }

    protected function dayKey(int $dayOfWeekIso): string
    {
        return match ($dayOfWeekIso) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
            default => 'monday',
        };
    }

    public function flushCache(): void
    {
        Cache::forget('business_setting:'.self::HOURS_KEY);
        Cache::forget('business_setting:'.self::HOLIDAYS_KEY);
        Cache::forget('business_setting:'.self::INFO_KEY);
    }
}
