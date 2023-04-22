@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>

    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex">
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Folio</span></label>
                <input type="text" class="input input-bordered w-full" value="{{ $model->id }}" readonly>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Fecha de entrega</span></label>
                <input type="text" class="input input-bordered w-full"
                    value="{{ date_format(date_create($model->deadline), 'd-m-Y') }}" readonly>
            </div>
            <div class="form-control w-full mb-2">
                <label class="label"><span class="label-text">Estado</span></label>
                <input type="email" class="input input-bordered w-full" value="{{ $model->status }}" readonly>
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
                <!-- head -->
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
                            <th>{{ $index + 1 }}</th>
                            <td>{{ $detail->service->name }}</td>
                            <td>{{ $detail->subservice->name }}</td>
                            <td>${{ number_format($detail->price, 2) }}</td>
                            <td>${{ number_format($detail->total, 2) }}</td>
                        </tr>
                        <tr>
                            @switch($detail->subservice->id)
                                @case(3)
                                    <td>-</td>
                                    <td colspan="2">Costo por modificar diseño</td>
                                    <td>${{number_format($detail->orderUpdateDesign->price, 2)}}</td>
                                    <td>${{number_format($detail->orderUpdateDesign->price, 2)}}</td>
                                @break

                                @case(4)
                                    <td>-</td>
                                    <td colspan="2">Costo por nuevo diseño</td>
                                    <td>${{number_format($detail->orderNewDesign->price, 2)}}</td>
                                    <td>${{number_format($detail->orderNewDesign->price, 2)}}</td>
                                @break
                            @endswitch
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @yield('model')
@endsection
