@extends('base::back.layouts.app')

@section('title', $model->getTranslation('plural'))

@php
    $garment = \App\Models\Garment::all();
    $garment = $garment->keyBy('id')->pluck('name', 'id')->toArray();
    $garment[''] = 'Cualquier Prenda';
@endphp
@section('content')
    <?= Base::breadcrumb([
        $model->getTranslation('plural')
    ]) ?>
    <?php
    $array = array_merge([], ['id', 'client.name', 'deadline', 'status', 'desings', 'service_type']);
    $array_translation = array_merge($model->getFullTranslations(), [
        'client.name' => 'Cliente',
        'client' => 'Cliente',
        'deadline' => 'Fecha de entrega',
        'desings' => 'Diseños',
        'service_type' => 'Tipo de servicio',
        'status' => 'Estado',
        'design' => 'Diseño',
    ]);
    $array_search = array_merge([], [
        ['field' => 'id'],
        ['field' => 'client'],
        ['field' => 'design'],
        ['field' => 'deadline'],
        ['field' => 'status', 'options' => $model->statusMapping(), 'type' => 'select'],
        ['field' => 'garment', 'options' => $garment, 'type' => 'select'],
    ]);
    ?>
    <div id=app>
        <grid-view
            :create_route="false"
            :routes={{json_encode($model->getIndexRoutes())}}
            :fields="{{json_encode($array)}}"
            :filters="{{json_encode($array_search)}}"
            :translations='{!! json_encode($array_translation) !!}'
            :template_actions="{{json_encode([
                'update' => auth()->user()->hasRole(['super-admin']) || $model->editable,
                'delete' => auth()->user()->hasRole(['super-admin']),
                'show' => true,
            ])}}"
        />
    </div>
@endsection
