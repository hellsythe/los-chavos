@extends('base::back.layouts.app')

@section('title_tab', 'Dashboard')

@section('content')
    <?= Base::breadcrumb([url('admin/orders-by-design') => 'Ordenes agrupados por diseño', $model->name]) ?>
    <div class="overflow-x-auto">
        <table class="table w-full mt-3 mb-3">
            <thead>
                <tr>
                    <td>#</td>
                    <td>Fecha de entrega</td>
                    <td>Cliente</td>
                    <td>Diseño</td>
                    <td>Folio</td>
                    <th width="100px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($models as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->deadline }}</td>
                        <td>{{ $item->client->name }}</td>
                        <td>{{ $model->name }}</td>
                        <td>{{ $item->id }}</td>
                        <td>
                            <a class="link link-primary" href="{{ url('/admin/order/' . $item->id) }}">Detalles</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
