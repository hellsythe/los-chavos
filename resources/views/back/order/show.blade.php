@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    @php
        $colors = ['red', 'lime', 'black', 'fuchsia', 'darkorange', 'darkolivegreen', 'navy', 'purple', 'crimson', 'coral', 'darkseagreen', 'darkviolet', 'indigo'];
    @endphp


    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>

    <div class=" mb-2 mr-2 flex justify-between">
        <div>
            @if (auth()->user()->hasRole(['super-admin', 'Punto de venta']) && $model->getRawOriginal('status') == $model::STATUS_WAITING_ORDER)
                <a href="{{route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_ORDER_ARRIVED])}}" class="btn btn-active">Marcar que el pedido llego</a>
            @endif
            @if (auth()->user()->hasRole(['super-admin', 'Punto de venta']) && $model->getRawOriginal('status') == $model::STATUS_READY)
                <a href="{{route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_FINISH])}}" class="btn btn-active">Marcar como Entregado</a>
            @endif
            @if (auth()->user()->hasRole(['super-admin', 'Bordador']) && $model->getRawOriginal('status') == $model::STATUS_PENDING)
            <a href="{{route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_READY])}}" class="btn btn-active">Marcar como Terminado</a>
            @endif

        </div>
        <div><label class="label"><strong class="text-3xl">Folio: &nbsp;#{{ $model->id }}</strong> </label></div>
    </div>
    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex">
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Elaboro</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->createdBy->email }}" readonly>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Fecha de entrega</span></label>
                <input type="text" class="input input-bordered w-full"
                    value="{{ date_format(date_create($model->deadline), 'd-m-Y') }}" readonly>
            </div>
            <div class="form-control w-full mb-2">
                <label class="label"><span class="label-text">Estado</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->status }}" readonly>
            </div>
        </div>
        <div class="flex">
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Prenda</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->garment->name }}" readonly>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Cantidad de prendas</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->garment_amount }}" readonly>
            </div>
            <div class="form-control w-full mb-2">
                <label class="label"><span class="label-text">Estado del pedido</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->order_status }}" readonly>
            </div>
        </div>
    </div>

    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex justify-between">
            <h1 class="font-bold">Información del cliente</h1>
        </div>
        <div class="flex">
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Nombre del cliente</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->client->name }}" readonly>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Teléfono del cliente</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->client->phone }}" readonly>
            </div>
            <div class="form-control w-full mb-2">
                <label class="label"><span class="label-text">Correo del cliente</span></label>
                <input type="email" class="input input-bordered w-full" value="{{ $model->client->email }}" readonly>
            </div>
        </div>
    </div>


    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex justify-between">
            <h1 class="font-bold">Servicios</h1>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Servicio</th>
                        <th>Subservicio</th>
                        <th>Precio por prenda</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($model->details as $index => $detail)
                        <tr>
                            <th>
                                <div class="ml-1 w-4 h-4 rounded-full" style="background-color: {{ $colors[$index] }}">
                                </div>
                            </th>
                            <td>{{ $detail->service->name }}</td>
                            <td>{{ $detail->subservice->name }}</td>
                            <td>${{ number_format($detail->price, 2) }}</td>
                            <td>${{ number_format($detail->total, 2) }}</td>
                        </tr>
                        <tr>
                            @switch($detail->subservice->id)
                                @case(3)
                                    <td>-</td>
                                    <td colspan="2">Costo por modificar diseño existente</td>
                                    <td>${{ number_format($detail->orderUpdateDesign->price, 2) }}</td>
                                    <td>${{ number_format($detail->orderUpdateDesign->price, 2) }}</td>
                                @break

                                @case(4)
                                    <td>-</td>
                                    <td colspan="2">Costo por nuevo diseño</td>
                                    <td>${{ number_format($detail->orderNewDesign->price, 2) }}</td>
                                    <td>${{ number_format($detail->orderNewDesign->price, 2) }}</td>
                                @break
                            @endswitch
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total</strong></td>
                        <td>${{ number_format($model->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex justify-between mb-2">
            <h1 class="font-bold">Detalles</h1>
        </div>
        <div id="sale">
            <show-order-component :garment="{{ json_encode($model->garment) }}"
                :services="{{ json_encode($model->details) }}"> </show-order-component>
            <payment-detail-component :model="{{ $model }}">
                </show-order-component>
        </div>
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
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Folio</th>
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
                    <td colspan="3" class="text-right"><strong>Total del pedido</strong></td>
                    <td>${{ number_format($model->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right"><strong>Pendiente de pago</strong></td>
                    <td>${{ number_format($model->missing_payment, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @yield('model')
@endsection
