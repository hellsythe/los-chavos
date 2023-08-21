<h3>Reporte Generado</h3>
<h3>Desde: {{$start}}</h3>
<h3>Hasta: {{$end}}</h3>
<h3>Metodo de pago: {{$method}}</h3>
<h3>Total: ${{number_format($total, 2)}}</h3>
<table>
    <tr>
        <th>Orden</th>
        <th>Importe</th>
        <th>Metodo de pago</th>
        <th>Fecha</th>
    </tr>
    @foreach ($payments as $payment)
        <tr>
            <td>{{ $payment->order_id }}</td>
            <td>${{ number_format($payment->amount, 2) }}</td>
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
