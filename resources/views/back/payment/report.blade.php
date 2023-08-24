<h3>Reporte Generado</h3>
<h3>Desde: {{$start}}</h3>
<h3>Hasta: {{$end}}</h3>
<h3>Metodo de pago: {{$method}}</h3>
<h3>Tipo de reporte: {{$type}}</h3>
<h3>Total: ${{number_format($total, 2)}}</h3>
<h3>Total de bordado: ${{number_format($total_embroidery, 2)}}</h3>
<h3>Total de estampado: ${{number_format($total_print, 2)}}</h3>
<table>
    <tr>
        <th>Orden</th>
        <th>Importe total</th>
        <th>Importe bordado</th>
        <th>Importe estampado</th>
        <th>Metodo de pago</th>
        <th>Fecha</th>
    </tr>
    @foreach ($payments as $payment)
        <tr>
            <td>{{ $payment->order_id }}</td>
            <td>${{ number_format($payment->amount, 2) }}</td>
            <td>${{ number_format($payment->total_embroidery, 2) }}</td>
            <td>${{ number_format($payment->total_print, 2) }}</td>
            <td>{{ $payment->payment_method }}</td>
            <td>{{ $payment->created_at }}</td>
        </tr>
    @endforeach
</table>

<style>
    table {
        width: 100%;
    }

    table,
    th,
    td {
        border: 1px solid;
    }
</style>
