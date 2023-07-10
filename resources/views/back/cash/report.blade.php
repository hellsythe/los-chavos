@extends('base::back.layouts.app')

@section('title_tab', 'Caja')

@section('content')
    @if (auth()->user()->hasRole(['super-admin']) )
        <a href="/admin/cash-box-report" class="btn btn-neutral mb-4">Reportes</a>
    @endif

    <div id="sale">
        @if ($model)
            <div class="alert alert-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h1>Ya existe un corte de caja para el día de hoy
                    <strong>{{ Date::createFromDate(date('Y-m-d'))->format('l j F Y') }}</strong>
                </h1>
            </div>
            {{-- <div class="mt-4">
                @if (auth()->user()->hasRole(['super-admin']) &&
                        $model->getRawOriginal('status') == $model::STATUS_WAITING_AUTH)
                    <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_INVALID]) }}"
                        class="btn btn-neutral mt-1 w-full lg:w-64 mr-1">Autorizar volver a hacer corte de caja</a>
                @endif
                @if (auth()->user()->hasRole(['Punto de venta']) && $model->getRawOriginal('status') == $model::STATUS_CLOSE)
                    <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_WAITING_AUTH]) }}"
                        class="btn btn-neutral mt-1 w-full lg:w-64 mr-1">Solicitar volver a hacer corte de caja a un administrador</a>
                @endif
                @if (auth()->user()->hasRole(['super-admin']) && $model->getRawOriginal('status') == $model::STATUS_CLOSE)
                    <a href="{{ route('order.update.status', ['id' => $model->id, 'status' => $model::STATUS_INVALID]) }}"
                        class="btn btn-neutral mt-1 w-full lg:w-64 mr-1">Invalidar este corte de caja y volver a generarlo</a>
                 @endif
            </div> --}}
        @else
        @php
        @endphp
            <cashbox-component
                :payments="{{ json_encode($payments) }}"
                isadmin="{{ auth()->user()->hasRole(['super-admin']) }}"
            />
        @endif
    </div>
@endsection
