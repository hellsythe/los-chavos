@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>

    <div class=" mb-2 mr-2 flex justify-between sm:flex-col lg:flex-row flex-wrap w-full">
        <div class="flex w-full">
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
                    {!! $model->missing_payment>0 ? 'data-question="¿Esta orden tiene saldo pendiente de $'.number_format($model->missing_payment, 2).', desea marcarlo como entregado de todas formas?"':'' !!}
                    class="btn btn-active mt-1 w-full lg:w-64 mr-1 question">Marcar como Entregado</a>
            @endif
            @if (auth()->user()->hasRole(['super-admin', 'Bordador', 'Estampador']) && $model->getRawOriginal('status') == $model::STATUS_PENDING)
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

            @if (auth()->user()->hasRole(['super-admin', 'Punto de venta']) && $model->getRawOriginal('status') == $model::STATUS_READY)
                <a href="{{ route('order.notify.whatsapp', ['id' => $model->id, 'status' => $model::STATUS_FINISH]) }}"
                    {!! $model->missing_payment>0 ? 'data-question="Ya se notifico al cliente el '.$model->whatsapp_notification.', ¿Desea enviar una nueva notificación?"':'' !!}
                    class="btn btn-active mt-1 w-full lg:w-64 question"
                    style="background-color: #25d366; color:white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"> <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/> </svg>
                    Notificar al cliente </a>
            @endif

        </div>
        <div class="flex justify-end flex-col text-right w-full" >
            <strong class="text-3xl text-right">Folio de venta: &nbsp;#{{ $model->id }}</strong>
            <strong class="text-3xl text-right">Fecha de registro: &nbsp;{{ $model->created_at }}</strong>
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
                <label class="label"><span class="label-text">Vendedor</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->employee->name ?? '-' }}" readonly>
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
