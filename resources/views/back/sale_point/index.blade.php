@extends('base::back.layouts.app')

@section('title_tab', 'Punto de venta')

@section('content')
    <div id="sale">
        <sale-point
            :availableservices="{{json_encode($available_services)}}"
            :orderp="{{json_encode($order)}}"
            :extrap="{{json_encode([
                'steps' => [
                    'client' => true,
                    'service' => false,
                    'confirm' => false,
                ],
                'errors' => [
                    'client' => '',
                    'services' => [],
                ]
            ])}}"
        />
    </div>
@endsection
