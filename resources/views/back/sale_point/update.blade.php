@extends('base::back.layouts.app')

@section('title_tab', 'Editar una orden')

@section('content')
    <div id="sale">
        <sale-point
            :availableservices="{{json_encode($available_services)}}"
            :orderp="{{json_encode($order)}}"
            :extrap="{{json_encode([
                'steps' => [
                    'client' => true,
                    'service' => true,
                    'confirm' => true,
                ],
                'errors' => [
                    'client' => '',
                    'services' => [],
                ],
                'employees' => $employees,
            ])}}"
        />
    </div>
@endsection
