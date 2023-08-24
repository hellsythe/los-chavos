@extends('base::back.layouts.app')

@section('title', $model->getTranslation('plural'))

@section('content')
    <?= Base::breadcrumb([$model->getTranslation('plural')]) ?>
    <?php
    $array = ['id', 'amount', 'total_embroidery', 'total_print', 'payment_method', 'created_at'];
    $array_translation = array_merge($model->getFullTranslations(), [
        'start' => 'Fecha de inicio',
        'end' => 'Fecha de fin',
        'method' => 'Metodo de pago',
        'service_type' => 'Tipo de servicio',
        'total_print' => 'Pago por estampado',
        'total_embroidery' => 'Pago por bordado',
    ]);
    $array_search = array_merge([], [
        ['type' => 'date', 'field' => 'start'],
        ['type' => 'date', 'field' => 'end'],
        ['type' => 'select', 'field' => 'method', 'options' => [
            'cash'=>'Efectivo',
            'label'=>'Tarjeta',
            '' => 'Todos'
        ]],
        ['type' => 'select', 'field' => 'service_type', 'options' => [
            'total_embroidery'=>'Bordado',
            'total_print'=>'Estampado',
            '' => 'Todos'
        ]]
    ]);
    ?>
    <div id=app>
        <grid-view :create_route="false" :routes={{ json_encode($model->getIndexRoutes()) }}
            :fields="{{ json_encode($array) }}" :filters={{ json_encode($array_search) }}
            :translations='{!! json_encode($array_translation) !!}' :searchisopen="true"
            :template_actions="{{ json_encode([
                'update' => false,
                'delete' => false,
                'show' => false,
            ]) }}" >
                <custom-link link="/admin/payment/report/pdf" class="btn btn-primary ml-2">Descargar Reporte PDF</custom-link>
        </grid-view>
    </div>
@endsection
