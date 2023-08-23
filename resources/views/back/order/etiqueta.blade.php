<html>

@php
    function truncate($string, $length = 54, $append = "...")
    {
        $string = trim($string);

        if(strlen($string) > $length)
        {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }

        return $string;
    }
@endphp
<div style="text-align: center; font-size: 16px">
    @foreach ($order->details as $service)

        @for ($x = 1; $x <= $service->garment_amount; $x++)
            <label>Fecha de entrega: {{ $order->deadline }}</label> <br>
            <label>{{ $service->garment->name }} #{{$x}}/{{$service->garment_amount}}</label> <br>
            <center>
                @php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($order->id, $generator::TYPE_CODE_128)) . '">';
                @endphp
            </center>
            <label style="font-size: 16px">Folio: {{ $order->id }}</label> <br>
            <label>{{ truncate($service->design_name) }}</label>
            <label></label><br>
        @endfor
    @endforeach
</div>

</html>
<style>
    html {
        margin: 8px 0;
    }

    img {
        margin-top: 5px;
    }
</style>
