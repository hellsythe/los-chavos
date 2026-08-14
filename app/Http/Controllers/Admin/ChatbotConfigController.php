<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BusinessInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotConfigController extends Controller
{
    public function index()
    {
        $service = app(BusinessInfoService::class);

        return view('back.chatbot-config.index', [
            'config' => [
                'business_hours' => $service->getHours() ?? $this->defaultHours(),
                'business_holidays' => $service->getHolidays() ?? [],
                'business_info' => $service->getBusinessInfo() ?? [
                    'business_name' => '',
                    'address' => '',
                    'phone' => '',
                    'email' => '',
                ],
            ],
            'urls' => [
                'save' => route('chatbot-config.update'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_hours' => ['required', 'array'],
            'business_hours.monday' => ['nullable', 'array'],
            'business_hours.tuesday' => ['nullable', 'array'],
            'business_hours.wednesday' => ['nullable', 'array'],
            'business_hours.thursday' => ['nullable', 'array'],
            'business_hours.friday' => ['nullable', 'array'],
            'business_hours.saturday' => ['nullable', 'array'],
            'business_hours.sunday' => ['nullable', 'array'],
            'business_holidays' => ['present', 'array'],
            'business_holidays.*' => ['array'],
            'business_holidays.*.date' => ['required_with:business_holidays.*.reason', 'date_format:Y-m-d'],
            'business_holidays.*.reason' => ['required_with:business_holidays.*.date', 'string', 'max:200'],
            'business_holidays.*.closed' => ['boolean'],
            'business_info' => ['required', 'array'],
            'business_info.business_name' => ['nullable', 'string', 'max:200'],
            'business_info.address' => ['nullable', 'string', 'max:500'],
            'business_info.phone' => ['nullable', 'string', 'max:50'],
            'business_info.email' => ['nullable', 'email', 'max:200'],
        ]);

        $hours = $this->normalizeHours($request->input('business_hours'));
        $holidays = $this->normalizeHolidays($request->input('business_holidays'));
        $info = $this->normalizeInfo($request->input('business_info'));

        $this->saveSetting(BusinessInfoService::HOURS_KEY, 'Horarios del negocio', $hours);
        $this->saveSetting(BusinessInfoService::HOLIDAYS_KEY, 'Días feriados', $holidays);
        $this->saveSetting(BusinessInfoService::INFO_KEY, 'Datos del negocio', $info);

        app(BusinessInfoService::class)->flushCache();

        return response()->json([
            'ok' => true,
            'message' => 'Configuración guardada correctamente.',
        ]);
    }

    protected function defaultHours(): array
    {
        return [
            'monday' => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            'thursday' => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            'friday' => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            'saturday' => ['closed' => false, 'open' => '09:00', 'close' => '14:00'],
            'sunday' => ['closed' => true, 'open' => '', 'close' => ''],
        ];
    }

    protected function normalizeHours(array $hours): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $out = [];
        foreach ($days as $day) {
            $d = $hours[$day] ?? null;
            $closed = (bool) ($d['closed'] ?? false);
            $open = trim((string) ($d['open'] ?? ''));
            $close = trim((string) ($d['close'] ?? ''));

            if ($closed) {
                $out[$day] = ['closed' => true];
            } else {
                if ($open === '' || $close === '') {
                    $out[$day] = ['closed' => true];
                } else {
                    $out[$day] = ['open' => $open, 'close' => $close];
                }
            }
        }
        return $out;
    }

    protected function normalizeHolidays(array $holidays): array
    {
        $out = [];
        foreach ($holidays as $h) {
            if (empty($h['date']) || empty($h['reason'])) {
                continue;
            }
            $out[] = [
                'date' => $h['date'],
                'reason' => trim($h['reason']),
                'closed' => (bool) ($h['closed'] ?? true),
            ];
        }
        usort($out, fn ($a, $b) => strcmp($a['date'], $b['date']));
        return $out;
    }

    protected function normalizeInfo(array $info): array
    {
        $allowed = ['business_name', 'address', 'phone', 'email'];
        $out = [];
        foreach ($allowed as $k) {
            $out[$k] = trim((string) ($info[$k] ?? ''));
        }
        return $out;
    }

    protected function saveSetting(string $name, string $label, array $value): void
    {
        $row = \App\Models\Setting::query()
            ->withTrashed()
            ->where('name', $name)
            ->first();

        if ($row && $row->trashed()) {
            $row->restore();
        }

        if (! $row) {
            $row = new \App\Models\Setting();
            $row->name = $name;
        }
        $row->label = $label;
        $row->value = json_encode($value, JSON_UNESCAPED_UNICODE);
        $row->status = \App\Models\Setting::STATUS_ACTIVE;
        $row->save();
    }
}
