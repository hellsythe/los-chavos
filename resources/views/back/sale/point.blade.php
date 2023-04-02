@extends('base::back.layouts.app')

@section('title_tab', 'Punto de venta')

@section('content')
    <div class="p-4 bg-white mb-5 shadow rounded-lg">
        <div class="flex">
            <div class="form-control w-full mb-2">
                <label for="" class="label"><span class="label-text">Tipo de servicio</span></label>
                <select class="select select-bordered w-full">
                    <option disabled selected>Elije uno</option>
                    <option>Bordado</option>
                    <option>Bordado 1</option>
                    <option>Bordado 2</option>
                    <option>Bordado 3</option>
                    <option>Bordado 4</option>
                </select>
                <div class="text-red-500 text-xs font-semibold"></div>
            </div>
            <div class="form-control w-full mb-2 ml-2">
                <label for="" class="label"><span class="label-text">Fecha</span></label>
                <input readOnly value="{{ date('d-m-Y') }}" type="text" class="input input-bordered w-full">
                <div class="text-red-500 text-xs font-semibold"></div>
            </div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Nombre del cliente</span></label>
            <input name="name" type="text" class="input input-bordered w-full" placeholder="John Fulanito">
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Teléfono del cliente</span></label>
            <input name="name" type="numeric" class="input input-bordered w-full" placeholder="2747430512">
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Correo del cliente</span></label>
            <input name="name" type="email" class="input input-bordered w-full" placeholder="cliente@gmail.com">
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="flex">
            <div class="form-control w-full mb-2 mr-2">
                <label for="" class="label"><span class="label-text">Cantidad de piezas</span></label>
                <input name="name" type="email" class="input input-bordered w-full" placeholder="">
                <div class="text-red-500 text-xs font-semibold"></div>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label for="" class="label"><span class="label-text">Cantidad de bordados</span></label>
                <input name="name" type="email" class="input input-bordered w-full" placeholder="">
                <div class="text-red-500 text-xs font-semibold"></div>
            </div>
            <div class="form-control w-full mb-2 mr-2">
                <label for="" class="label"><span class="label-text">Ubicacion de bordado</span></label>
                <input name="name" type="email" class="input input-bordered w-full" placeholder="">
                <div class="text-red-500 text-xs font-semibold"></div>
            </div>
            <div class="form-control w-full mb-2">
                <label for="" class="label"><span class="label-text">Urgencia</span></label>
                <input name="name" type="email" class="input input-bordered w-full" placeholder="">
                <div class="text-red-500 text-xs font-semibold"></div>
            </div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Colores de bordado</span></label>
            <input name="name" type="email" class="input input-bordered w-full" placeholder="gris o blanco ">
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>

        <div class="form-control w-full mb-2">
            <label for="" class="label"><span class="label-text">Comentarios especiales</span></label>
            <textarea class="textarea textarea-bordered" placeholder="Comentarios"></textarea>
            <div class="text-red-500 text-xs font-semibold"></div>
        </div>
    </div>
@endsection
