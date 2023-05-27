@extends('base::back.layouts.app')

@section('title_tab', 'Punto de venta')

@section('content')
    <div id="sale">
        <sale-point
            :availableservices="{{json_encode($available_services)}}"
            :order="{{json_encode($order)}}"
        />
    </div>
@endsection
