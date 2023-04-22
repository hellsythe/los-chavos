<?php

namespace Tests\Feature\Models\Traits;

use App\Models\Client;
use App\Models\Subservice;

trait OrdersBuilder
{

    protected function getClient(Client $client = null)
    {
        if (!$client) {
            $client = Client::factory()->make();
        }

        return [
            'name' => $client->name,
            'phone' => $client->phone,
            'email' => $client->email,
        ];
    }

    protected function getSubservice(Subservice $subservice = null)
    {
        $subserviceDetails = $this->getSubserviceDetails($subservice);
        return array_merge([
            'service_id' => [
                'id' => $subservice->service_id,
            ],
            'subservice_id' => [
                'id' => $subservice->id,
            ],
            'point' => [
                'x' => $this->faker()->numberBetween(10, 450),
                'y' => $this->faker()->numberBetween(10, 250),
            ],
            'price' => 500,
        ],  $subserviceDetails);
    }

    protected function getSubserviceDetails(Subservice $subservice)
    {
        switch ($subservice->id) {
            case 1:
                return $this->getSubserviceBordado();
                break;

            case 2:
                return $this->getSubserviceBordadoCustom();
                break;

            case 4:
                return $this->getSubserviceNewBordado();
                break;
            default:
                # code...
                break;
        }
    }

    protected function getSubserviceBordado()
    {
        return [
            'design' => [
                'id' => 1
            ],
            'comments' => $this->faker()->text()
        ];
    }

    protected function getSubserviceBordadoCustom()
    {
        return [
            'typography' => [
                'id' => 1
            ],
            'textsize' => $this->faker()->text(),
            'custom' => ['text' => $this->faker()->text()],
        ];
    }

    protected function getSubserviceNewBordado()
    {
        return [
            'newDesign' => "data:application/octet-stream;base64,Z2hwX21PUGQ2M0VteWdkM1hsQTFvOXJVT3FCQ3ptUnFUSjBrQjh6Sgo=",
            'new_design_name' => $this->faker()->name(),
            'price' => $this->faker()->numberBetween(30,200),
            'price_new' => $this->faker()->numberBetween(50,200),
            'puntadas' => $this->faker()->numberBetween(100,500),
        ];
    }

    protected function getGarment(int $total = null)
    {
        return [
            "data" => [
                "id" => 1
            ],
            "amount" => $total ?? $this->faker()->numberBetween(10, 99)
        ];
    }

    protected function calculateTotalByService($services, $garment)
    {
        $total = 0;
        foreach ($services as $service) {
            $total += $service['price'] * $garment['amount'];

            if ($service['price_new'] ?? false) {
                if($garment['amount'] <= config('app.max_garment')) {
                    $total += $service['price_new'];
                }
            }
        }
        return $total;
    }

    protected function getPayment($total = null)
    {
        return [
            'advance' => $total ?? 300,
        ];
    }
}
