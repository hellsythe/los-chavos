@extends('base::back.layouts.app')

@section('title', $model->getTranslation('showed'))

@section('content')
    <?= Base::breadcrumb([$model->getRoute('index') => $model->getTranslation('plural'), $model->getTranslation('showed')]) ?>
    <div class="p-4 bg-base-200 mb-5 shadow rounded-lg">
        <div class="flex justify-between">
            <h1 class="font-bold">Información del cliente</h1>
        </div>
        <div class="flex">
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Nombre del cliente</span></label>
                <input type="text" class="input input-bordered w-full" value="{{$model->client->name}}" readonly>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label class="label"><span class="label-text">Teléfono del cliente</span></label>
                <input type="number" class="input input-bordered w-full" value="{{$model->client->phone}}" readonly>
            </div>
            <div class="form-control w-full mb-2">
                <label class="label"><span class="label-text">Correo del cliente</span></label>
                <input type="email" class="input input-bordered w-full" value="{{$model->client->email}}" readonly>
            </div>
        </div>
    </div>
    @yield('model')
@endsection
