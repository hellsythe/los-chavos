@extends('base::back.layouts.app')

@section('title_tab', 'Dashboard')

@section('content')
    <div class="stats shadow">
        <div class="stat">
            <div class="stat-title">Pedidos sin pago al 100%</div>
            <div class="stat-value">{{ number_format($order_missing_payment) }}</div>
        </div>
    </div>
    <div class="stats shadow">

        <div class="stat">
            <div class="stat-title">Pedidos pendientes</div>
            <div class="stat-value">{{ number_format($order_pending) }}</div>
        </div>

    </div>
    <div class="stats shadow">

        <div class="stat">
            <div class="stat-title">Pedidos Listos sin entregar</div>
            <div class="stat-value">{{ number_format($order_ready) }}</div>
        </div>
    </div>
    <div class="mt-4">
        <a href="{{route('dashboard.groupby')}}" class="btn btn-active">Agrupar por Diseños iguales</a>
    </div>
    <?php
    $array = array_merge($model->getIndexFields(), [
        'service.name',
        'subservice.name',
        'deadline',
        // 'detail.design_id',
        'detail.design.name',
    ]);
    $array_translation = array_merge($model->getFullTranslations(), [
        'service.name' => 'Servicio',
        'service' => 'Servicio',
        'subservice' => 'Tipo',
        'subservice.name' => 'Tipo',
        'deadline' => 'Fecha de entrega',
        // 'detail.design_id' => 'Folio de Diseño',
        'detail.design.name' => 'Nombre del Diseño',
    ]);

    $array_search = array_merge($model->getParseSearchFilters(), [
        // ['field' => 'service'],
        // ['field' => 'subservice'],
        ['field' => 'deadline'],
        // ['field' => 'detail.design_id'],
    ]);
    ?>

    <div id="app" class="mt-4">
        <grid-view :create_route="false" :routes={{ json_encode($model->getIndexRoutes()) }}
            :fields="{{ json_encode($array) }}" :filters={{ json_encode($array_search) }}
            :translations='{!! json_encode($array_translation) !!}'
            :template_actions="{{ json_encode([
                'update' => false,
                'delete' => false,
                'show' => true,
            ]) }}" />
    </div>
@endsection
