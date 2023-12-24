@extends('base::back.layouts.app')

@section('title_tab', 'Punto de venta')

@section('content')
    <div id="sale">
        <sale-point
            :availableservices="{{json_encode($available_services)}}"
            :extrap="{{json_encode([
                'steps' => [
                    'client' => true,
                    'service' => false,
                    'confirm' => false,
                ],
                'errors' => [
                    'client' => '',
                    'services' => [],
                ],
                'cost' => [
                    'update_embroidery_price' => $update_embroidery_price,
                    'new_embroidery_price' => $new_embroidery_price,
                ],
                'employees' => $employees,
            ])}}"
        />
    </div>
@endsection
