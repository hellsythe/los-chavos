@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>

    <div class=" mb-2 mr-2 flex justify-between sm:flex-col lg:flex-row flex-wrap">
        <div class="flex">
            <div class="tooltip w-full lg:w-64 mr-1" data-tip="Genera el ticket">
                <a href="{{ URL::to('admin/ticket/' . $model->id) }}" class="btn btn-active mt-1 w-full lg:w-64">Ver ticket</a>
            </div>
            <div class="tooltip w-full lg:w-64 mr-1" data-tip="Genera el ticket">
                <a href="{{ URL::to('admin/etiquetas/' . $model->id) }}" class="btn btn-active mt-1 w-full lg:w-64">Ver Etiquetas</a>
            </div>
            @if (auth()->user()->hasRole(['super-admin', 'Punto de venta']) &&
                    $model->getRawOriginal('status') == $model::STATUS_WAITING_ORDER)
                <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_ORDER_ARRIVED]) }}"
                    class="btn btn-active mt-1 w-full lg:w-64 mr-1">Marcar que el pedido llego</a>
            @endif
            @if (auth()->user()->hasRole(['super-admin', 'Punto de venta']) && $model->getRawOriginal('status') == $model::STATUS_READY)
                <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_FINISH]) }}"
                    class="btn btn-active mt-1 w-full lg:w-64 mr-1">Marcar como Entregado</a>
            @endif
            @if (auth()->user()->hasRole(['super-admin', 'Bordador']) && $model->getRawOriginal('status') == $model::STATUS_PENDING)
                <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_READY]) }}"
                    class="btn btn-active mt-1 w-full lg:w-64 mr-1">Marcar como Terminado</a>
            @endif

            @if (auth()->user()->hasRole(['super-admin']) &&
                    ($model->getRawOriginal('status') == $model::STATUS_MISSING_PAYMENT ||
                        $model->getRawOriginal('status') == $model::STATUS_WAITING_AUTH))
                <div class="tooltip w-full lg:w-64 mr-1"
                    data-tip="Este orden no tiene el pago 100%, pero se puede autorizar a que se realize">
                    <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_PENDING]) }}"
                        class="btn btn-active mt-1 w-full lg:w-64 mr-1">Autorizar orden para su realización</a>
                </div>
            @endif

            @if (auth()->user()->hasRole(['super-admin', 'Punto de venta', 'Bordador']) &&
                    $model->getRawOriginal('status') == $model::STATUS_MISSING_PAYMENT)
                <div class="tooltip w-full lg:w-64 mr-1"
                    data-tip="Este orden no tiene el pago 100%, pero se puede solicitar a un Administrador la autorización para realizarlo">

                    <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_WAITING_AUTH]) }}"
                        class="btn btn-active mt-1 w-full lg:w-64 mr-1">Solicitar autorización para orden</a>
                </div>
            @endif

        </div>
        <div class="flex justify-end flex-col text-right">
            <strong class="text-3xl text-right">Folio de venta: &nbsp;#{{ $model->id }}</strong>
            <strong class="text-3xl text-right">Fecha de entrega: &nbsp;{{ $model->deadline }}</strong>
        </div>
    </div>

    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="lg:flex">
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Elaboro</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->createdBy->email }}" readonly>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Estado</span></label>
                <input type="text" class="input input-bordered w-full bg-{{ $model->color_status }}"
                    value="{{ $model->status }}" readonly>
            </div>
            <div class="form-control w-full mb-2">
                <label class="label"><span class="label-text">Estado de la orden</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->order_status }}" readonly>
            </div>
        </div>
    </div>

    <div id="sale">
        <sale-point :availableservices="{{ json_encode($available_services) }}" :orderp="{{ json_encode($order) }}"
            :extrap="{{ json_encode([
                'steps' => [
                    'client' => true,
                    'service' => true,
                    'confirm' => true,
                ],
                'errors' => [
                    'client' => '',
                    'services' => [],
                ],
                'readonly' => true,
            ]) }}" />
        </sale-point>
    </div>

    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex justify-between mb-2">
            <h1 class="font-bold">Pagos</h1>
            @if (
                $model->missing_payment != 0 &&
                    auth()->user()->hasRole(['super-admin', 'Punto de venta']))
                <label for="confirmpayment" class="btn btn-active">Realizar Abono</label>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Folio De Pago</th>
                        <th>Fecha</th>
                        <th>Elaboro</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach ($model->payments as $payment)
                        @php
                            $total += $payment->amount;
                        @endphp
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ date_format(date_create($payment->createt_at), 'd-m-Y H:i') }}</td>
                            <td>{{ $payment->createdBy->email }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total de abonos</strong></td>
                        <td>${{ number_format($total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Total del orden</strong></td>
                        <td>${{ number_format($model->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Pendiente de pago</strong></td>
                        <td>${{ number_format($model->missing_payment, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @yield('model')
@endsection
