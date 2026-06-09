@extends('base::back.layouts.app')

@section('title_tab', 'Estadísticas de órdenes')

@section('content')
    <form method="GET" action="{{ route('dashboard.order.statistics') }}" class="flex flex-wrap gap-2 items-end">
        <div class="form-control">
            <label class="label"><span class="label-text">Tipo de periodo</span></label>
            <select class="select select-bordered" name="period_type">
                <option value="custom" {{ $period_type === 'custom' ? 'selected' : '' }}>Personalizado</option>
                <option value="yearly" {{ $period_type === 'yearly' ? 'selected' : '' }}>Año completo</option>
                <option value="school" {{ $period_type === 'school' ? 'selected' : '' }}>Periodo escolar (1 ago - 30 sep)</option>
            </select>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Año</span></label>
            <select class="select select-bordered" name="year">
                <option value="">Personalizado</option>
                @foreach ($available_years as $year)
                    <option value="{{ $year }}" {{ (string) $selected_year === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Fecha inicio</span></label>
            <input class="input input-bordered" type="date" name="start" value="{{ $start }}">
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Fecha fin</span></label>
            <input class="input input-bordered" type="date" name="end" value="{{ $end }}">
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Sucursal</span></label>
            <select class="select select-bordered" name="branch_id">
                <option value="">Todas</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (string) $branch_id === (string) $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Top de bordados</span></label>
            <select class="select select-bordered" name="top_limit">
                <option value="10" {{ (int) $top_limit === 10 ? 'selected' : '' }}>Top 10</option>
                <option value="20" {{ (int) $top_limit === 20 ? 'selected' : '' }}>Top 20</option>
                <option value="30" {{ (int) $top_limit === 30 ? 'selected' : '' }}>Top 30</option>
            </select>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Histórico predicción</span></label>
            <select class="select select-bordered" name="history_years">
                <option value="3" {{ (int) $history_years === 3 ? 'selected' : '' }}>Últimos 3 años</option>
                <option value="4" {{ (int) $history_years === 4 ? 'selected' : '' }}>Últimos 4 años</option>
                <option value="5" {{ (int) $history_years === 5 ? 'selected' : '' }}>Últimos 5 años</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Aplicar filtro</button>
        <a href="{{ route('dashboard.order.statistics') }}" class="btn btn-warning">Limpiar</a>
    </form>
    <div class="text-xs opacity-70 mt-2">
        Si seleccionas "Año completo" o "Periodo escolar", se usa el año indicado y se ignoran fechas personalizadas.
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mt-6">
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total de órdenes</div>
                <div class="stat-value">{{ number_format($total_orders) }}</div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Mismo periodo año pasado</div>
                <div class="stat-value">{{ number_format($previous_total_orders) }}</div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Variación vs año pasado</div>
                <div class="stat-value text-{{ is_null($variation_percent) ? 'base-content' : ($variation_percent >= 0 ? 'success' : 'error') }}">
                    {{ is_null($variation_percent) ? 'N/A' : number_format($variation_percent, 2) . '%' }}
                </div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Promedio diario</div>
                <div class="stat-value">{{ number_format($average_orders_per_day, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="tabs tabs-boxed mt-6 w-fit" id="statistics-tabs">
        <button type="button" class="tab tab-active" data-tab-target="orders-monthly">Órdenes por mes</button>
        <button type="button" class="tab" data-tab-target="popular-embroidery">Bordados populares</button>
        <button type="button" class="tab" data-tab-target="period-forecast">Predicción del periodo</button>
    </div>

    <div class="tab-panel mt-3" data-tab-panel="orders-monthly">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Órdenes por mes</h2>
                @php
                    $max = collect($chart_data)->max('total_orders') ?: 1;
                @endphp
                <div class="space-y-3">
                    @forelse ($chart_data as $point)
                        @php
                            $width = ($point['total_orders'] / $max) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ $point['period'] }}</span>
                                <span>{{ number_format($point['total_orders']) }} órdenes</span>
                            </div>
                            <progress class="progress progress-primary w-full" value="{{ $width }}" max="100"></progress>
                        </div>
                    @empty
                        <div class="text-sm opacity-80">No hay datos en el periodo seleccionado.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="tab-panel mt-3 hidden" data-tab-panel="popular-embroidery">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Bordados más populares (Top {{ $top_limit }})</h2>
                @php
                    $popularMax = collect($popular_embroidery)->max('total_orders') ?: 1;
                @endphp
                <div class="space-y-3">
                    @forelse ($popular_embroidery as $item)
                        @php
                            $width = ($item->total_orders / $popularMax) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1 gap-3">
                                <span class="truncate" title="{{ $item->design_name }}">{{ $item->design_name }}</span>
                                <span>{{ number_format($item->total_orders) }} órdenes | {{ number_format($item->total_garments) }} prendas</span>
                            </div>
                            <progress class="progress progress-secondary w-full" value="{{ $width }}" max="100"></progress>
                        </div>
                    @empty
                        <div class="text-sm opacity-80">No hay bordados en el periodo seleccionado.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="tab-panel mt-3 hidden" data-tab-panel="period-forecast">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Predicción del periodo seleccionado ({{ $demand_forecast['start'] }} a {{ $demand_forecast['end'] }})</h2>
                <div class="mt-2 p-3 rounded-lg bg-base-200 text-sm">
                    <div class="font-semibold mb-2">Glosario de colores</div>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-4 h-2 rounded bg-accent"></span>
                            <span>Proyección del periodo</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-4 h-2 rounded bg-success"></span>
                            <span>Órdenes reales</span>
                        </div>
                        @if (!is_null($current_year_projection))
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-4 h-2 rounded bg-info"></span>
                                <span>Proyección año actual</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mt-2">
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Pronóstico total</div>
                            <div class="stat-value">{{ number_format($demand_forecast['predicted_total_orders']) }}</div>
                        </div>
                    </div>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Órdenes reales del periodo</div>
                            <div class="stat-value">{{ number_format($demand_forecast['actual_total_orders']) }}</div>
                        </div>
                    </div>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Cumplimiento a hoy</div>
                            <div class="stat-value">{{ is_null($demand_forecast['progress_percent']) ? 'N/A' : number_format($demand_forecast['progress_percent'], 2) . '%' }}</div>
                        </div>
                    </div>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Error histórico (MAPE)</div>
                            <div class="stat-value">{{ is_null($demand_forecast['backtest_mape']) ? 'N/A' : number_format($demand_forecast['backtest_mape'], 2) . '%' }}</div>
                        </div>
                    </div>
                </div>

                @if (!is_null($current_year_projection))
                    <div class="stats shadow mt-3">
                        <div class="stat">
                            <div class="stat-title">Proyección año actual ({{ $current_year_projection['year'] }})</div>
                            <div class="stat-value">{{ number_format($current_year_projection['predicted_total_orders']) }}</div>
                        </div>
                    </div>
                @endif

                <div class="mt-4 text-sm opacity-80">
                    Pronóstico calculado con últimos {{ $demand_forecast['history_years_used'] }} años.
                </div>
                <div class="mt-1 text-sm opacity-80">
                    Tendencia estimada: {{ number_format($demand_forecast['weighted_growth_percent'], 2) }}% vs años previos.
                </div>

                @php
                    $maxWeek = collect($demand_forecast['weekly_projection'])->max('predicted_orders') ?: 1;
                    $currentYearWeekly = !is_null($current_year_projection)
                        ? collect($current_year_projection['weekly_projection'])->keyBy('label')
                        : collect();
                @endphp
                <div class="space-y-3 mt-4">
                    @foreach ($demand_forecast['weekly_projection'] as $week)
                        @php
                            $predictedWidth = ($week['predicted_orders'] / $maxWeek) * 100;
                            $actualWidth = ($week['actual_orders'] / $maxWeek) * 100;
                            $currentYearWeekProjection = (int) ($currentYearWeekly[$week['label']]['predicted_orders'] ?? 0);
                            $currentYearWidth = ($currentYearWeekProjection / $maxWeek) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1 gap-3">
                                <span>{{ $week['label'] }}</span>
                                <span>
                                    Proy: {{ number_format($week['predicted_orders']) }}
                                    | Real: {{ number_format($week['actual_orders']) }}
                                    @if (!is_null($current_year_projection))
                                        | Año actual: {{ number_format($currentYearWeekProjection) }}
                                    @endif
                                </span>
                            </div>
                            <progress class="progress progress-accent w-full" value="{{ $predictedWidth }}" max="100"></progress>
                            <progress class="progress progress-success w-full mt-1" value="{{ $actualWidth }}" max="100"></progress>
                            @if (!is_null($current_year_projection))
                                <progress class="progress progress-info w-full mt-1" value="{{ $currentYearWidth }}" max="100"></progress>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const tabs = Array.from(document.querySelectorAll('#statistics-tabs [data-tab-target]'));
            const panels = Array.from(document.querySelectorAll('[data-tab-panel]'));

            const activate = (target) => {
                tabs.forEach((tab) => {
                    tab.classList.toggle('tab-active', tab.dataset.tabTarget === target);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.tabPanel !== target);
                });
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => activate(tab.dataset.tabTarget));
            });
        })();
    </script>
@endsection
