    <div id="ticket">
        <div style="font-size: 13px;">
            <center>
                <img style="width: 130px;" src="{{ public_path('img/logo.svg') }}" alt="">
            </center>
            <label>Folio: {{ $order->id }}</label> <br>
            <label>Fecha de Orden: {{ Date::createFromDate($order->created_at)->format('d-m-Y') }}</label> <br>
            <label>Fecha de entrega: {{ Date::createFromDate($order->getAttributes()['deadline'])->format('d-m-Y') }}</label> <br>
            @if ($order->order_number)
                <label>Es un pedido</label>
            @endif

            <table style="border-collapse: collapse; border: 1px solid; padding: 5px; width: 100%; margin-top: 10px;">
                <tr>
                    <th style="font-size: 12px;border: 1px solid; padding: 2px; text-align: left;">Concepto</th>
                    <th style="font-size: 12px;border: 1px solid; padding: 2px; text-align: left;">Total</th>
                </tr>
                @foreach ($order->details as $service)

                    <tr>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px">{{ $service->subservice->name }} </td>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">${{ number_format($service->price * $order->garment_amount), 2 }}</td>
                    </tr>
                    {{-- <tr v-if="service.subservice_id.id == 4 && garmentData.amount <= 6">
                        <td style="font-size: 12px; border: 1px solid; padding: 2px"> Costo Por Diseño nuevo</td>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">{{
                            formatter(service.price_new) }} </td>
                    </tr>
                    <tr v-if="service.subservice_id.id == 3 && garmentData.amount <= 6">
                        <td style="font-size: 12px; border: 1px solid; padding: 2px"> Costo Por Modificar diseño </td>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">{{
                            formatter(service.price_update) }}</td> --}}
                    </tr>
                @endforeach

                {{-- <tr>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>Total</strong>
                    </td>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>{{
                        formatter(payment.total) }}</strong></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong>Anticipo</strong>
                    </td>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>{{
                        formatter(payment.advance) }}</strong></td>
                </tr>
                <tr>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>Resta</strong>
                    </td>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;"><strong>{{
                        formatter(payment.total - payment.advance) }}</strong></td>
                </tr>  --}}
            </table>
            <div style="display: flex; justify-content: center;">
                <center>
                @php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($order->id, $generator::TYPE_CODE_128)) . '">';
                @endphp
                </center>
            </div>
            <p style="text-align: center;">“Favor de corroborar su nota verificando que los datos de su servicio sean
                correctos, tal como usted lo solicita, no nos hacemos responsables si usted no los verifica”</p>
            <p style="text-align: center;">“Todas las entregas de servicio son a partir de las 18:00 hrs del día de la
                entrega”</p>
        </div>
    </div>
