@extends('base::back.layouts.app')

@section('title_tab', 'Caja')

@section('content')
    <div id="sale">
        <cashbox-component :payments="{{json_encode($payments)}}" />
    </div>
@endsection
