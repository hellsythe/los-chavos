@extends('base::back.layouts.app')

@section('title_tab', 'Punto de venta')

@section('content')
    <div id="sale">
        <sale-component :services="{{json_encode($services)}}" />
    </div>
@endsection
