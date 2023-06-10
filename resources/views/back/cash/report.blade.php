@extends('base::back.layouts.app')

@section('title_tab', 'Caja')

@section('content')


    <button class="btn btn-neutral">Reportes</button>

    <div class="p-4 bg-base-100 mb-5 shadow rounded-lg mt-2">
        <div class="overflow-x-auto">
            <table class="table">
                <tbody>
                    <tr>
                        <td></td>
                        <td>Contado</td>
                        <td>Calculado</td>
                        <td>Diferencia</td>
                    </tr>
                    <tr>
                        <td><strong>Efectivo</strong></td>
                        <td><input class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td><input readonly class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td><strong>Tarjeta</strong></td>
                        <td><input class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td><input readonly class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td><strong>Vales</strong></td>
                        <td><input class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td><input readonly class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td><strong>Cheques</strong></td>
                        <td><input class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td><input readonly class="input input-bordered w-full max-w-xs" type="numeric"></td>
                        <td>0</td>
                    </tr>
                </tbody>
            </table>

            <button class="btn btn-neutral">Corte de Caja</button>
        </div>
    </div>
@endsection
