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
        <a href="{{ route('dashboard') }}" class="btn btn-active">Ver toda la lista de pedidos</a>
    </div>

    <table class="table w-full mt-3 mb-3">
        <thead>
            <tr>
                <td>#</td>
                <td>Total</td>
                <td>Diseño</td>
                <th width="100px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{$index+1}}</td>
                    <td>{{$item->total}}</td>
                    <td>{{$item->design}}</td>
                    <td>
                        <a class="link link-primary" href="{{route('dashboard.grouped', ['id' => $item->desing_id])}}">Detalles</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
