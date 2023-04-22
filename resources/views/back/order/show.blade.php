@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    @php
        $colors = ['red', 'lime', 'black', 'fuchsia', 'darkorange', 'darkolivegreen', 'navy', 'purple', 'crimson', 'coral', 'darkseagreen', 'darkviolet', 'indigo'];
    @endphp


    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>

    <div class="flex justify-end">
        <div class=" mb-2 mr-2 flex">
            <label class="label"><strong class="text-3xl	">Folio: &nbsp;#{{ $model->id }}</strong> </label>
        </div>
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
                <input type="number" class="input input-bordered w-full" value="{{ $model->client->phone }}" readonly>
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
                    <tr>
                        <td colspan="4" class="text-right"><strong>Pendiente de pago</strong></td>
                        <td>${{ number_format($model->missing_payment, 2) }}</td>
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
                :services="{{ json_encode($model->details) }}" />
        </div>
    </div>

    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex justify-between mb-2">
            <h1 class="font-bold">Pagos</h1>
            <div id="sale"></div>
            @if ($model->missing_payment != 0)
                <button class="btn btn-active">Realizar Abono</button>
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
                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                    <td>${{ number_format($total, 2) }}</td>
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
