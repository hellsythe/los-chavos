    <div id="ticket">
        <div style="font-size: 13px; margin-top: -20px">
            <div style="text-align: center">
                <img style="width: 130px;display: inline" src="{{ public_path('img/logo.svg') }}" alt="">
            </div>
            <div style="margin-top: -20px"></div>
            <label>Folio: {{ $order->id }}</label> <br>
            <label>Fecha de orden: {{ Date::createFromDate($order->created_at)->format('d-m-Y') }}</label> <br>
            <label>Fecha de entrega: {{ Date::createFromDate($order->getAttributes()['deadline'])->format('d-m-Y') }}</label> <br>
            <label>Prenda: {{ $order->garment->name }}</label> <br>
            <label>Cantidad de prendas: {{ $order->garment_amount }}</label> <br>
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
                        <td style="font-size: 12px; border: 1px solid; padding: 2px">
                            {{ $service->subservice->name }}
                            @switch($service->subservice->id)
                                 @case(2)
                                    {{$service->detail->text}}
                                    @break

                                @default
                                    {{$service->detail->design->name}}
                            @endswitch
                        </td>
                        <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">
                            ${{ number_format($service->price * $order->garment_amount, 2) }}
                        </td>
                    </tr>
                    @if ($service->subservice->id == 4 && $order->garment_amount <=6)
                        <tr>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px"> Costo Por Diseño nuevo</td>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">
                                ${{ number_format($service->detail->price, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if ($service->subservice->id == 3 && $order->garment_amount <=6)
                        <tr>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px"> Costo Por Modificar diseño </td>
                            <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: end;">
                                ${{ number_format($service->detail->price, 2) }}
                            </td>
                        </tr>
                    @endif
                @endforeach

                <tr>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong>Total</strong>
                    </td>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong> ${{ number_format($order->total, 2)}}</strong>
                    </td>
                <tr>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong>Anticipo</strong>
                    </td>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong> ${{ number_format($order->total - $order->missing, 2)}}</strong></td>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong>Resta</strong>
                    </td>
                    <td style="font-size: 12px; border: 1px solid; padding: 2px; text-align: right;">
                        <strong> ${{ number_format($order->missing, 2)}}</strong></td>
                    </td>
                </tr>
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
