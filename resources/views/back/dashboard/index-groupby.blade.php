@extends('base::back.layouts.app')

@section('title_tab', 'Dashboard')

@section('content')
    <div class="stats shadow mt-2">
        <div class="stat">
            <div class="stat-title">Ordenes sin pago al 100%</div>
            <div class="stat-value">{{ number_format($order_missing_payment) }}</div>
        </div>
    </div>
    <div class="stats shadow mt-2">

        <div class="stat">
            <div class="stat-title">Ordenes pendientes</div>
            <div class="stat-value">{{ number_format($order_pending) }}</div>
        </div>

    </div>
    <div class="stats shadow mt-2">

        <div class="stat">
            <div class="stat-title">Ordenes Listos sin entregar</div>
            <div class="stat-value">{{ number_format($order_ready) }}</div>
        </div>
    </div>
    <div class="stats shadow mt-2">
        <div class="stat">
            <div class="stat-title">Ordenes Esperando autorización</div>
            <div class="stat-value">{{ number_format($missing_auth) }}</div>
        </div>
    </div>
    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-active">Ver toda la lista de Ordenes</a>
    </div>

    <form action="" class="mt-2 mb-2">
        <input class="input input-bordered" min="{{date('Y-m-d')}}" type="date" name="start" value="{{ request()->get('start') }}">
        <input class="input input-bordered" min="{{date('Y-m-d')}}" type="date" name="end" value="{{ request()->get('end') }}">
        <a class="btn btn-warning" href="/admin/orders-by-design"> Limpiar </a>
        <button class="btn btn-primary" type="submit">Agrupar por fecha de entrega</button>
    </form>
    <div class="overflow-x-auto">
        <table class="table w-full mt-3 mb-3">
            <thead>
                <tr>
                    <td>#</td>
                    <td>Total de Ordenes</td>
                    <td>Diseño</td>
                    <td>Total de prendas</td>
                    <th width="100px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->total }}</td>
                        <td>{{ $item->design }}</td>
                        <td>{{ $item->garment }}</td>
                        <td>
                            <a class="link link-primary"
                                href="{{ route('dashboard.grouped', ['id' => $item->desing_id]) }}">Detalles</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
