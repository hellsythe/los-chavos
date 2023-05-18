@extends('base::back.layouts.app')

@section('title_tab', 'Punto de venta')

@section('content')
    <div id="sale">
        <sale-edit-component
            :services="{{json_encode($available_services)}}"
            :model="{{json_encode($model)}}"
        />
    </div>
@endsection
