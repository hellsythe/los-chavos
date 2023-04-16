@extends('base::back.layouts.app')

@section('title_tab', 'Dashboard')

@section('content')
<div class="stats shadow">
  <div class="stat">
    <div class="stat-title">Pedidos sin pago al 100%</div>
    <div class="stat-value">{{number_format($order_missing_payment)}}</div>
  </div>
</div>
<div class="stats shadow">

    <div class="stat">
      <div class="stat-title">Pedidos pendientes</div>
      <div class="stat-value">{{number_format($order_pending)}}</div>
    </div>

  </div>
  <div class="stats shadow">

    <div class="stat">
      <div class="stat-title">Pedidos Listos sin entregar</div>
      <div class="stat-value">{{number_format($order_ready)}}</div>
    </div>

  </div>
@endsection
