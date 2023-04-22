<?php

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Order;
use App\Models\Subservice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Models\Traits\OrdersBuilder;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use WithFaker;
    use OrdersBuilder;

    public function test_enter_guess_user(): void
    {
        $response = $this->get('/admin/sale-point');
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function test_enter_user_without_permission(): void
    {
        $user = $this->makeUser();
        $response = $this->actingAs($user)->get('/admin/sale-point');
        $response->assertStatus(403);
    }

    public function test_enter_user_with_permission(): void
    {
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->get('/admin/sale-point');
        $response->assertStatus(200);
    }

    public function test_embroidery_existing_total_payment()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_PENDING,
            'missing_payment' => '0.00'
        ]);
    }


    public function test_embroidery_existing_missing_payment()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total/2),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_MISSING_PAYMENT,
            'missing_payment' => $total/2
        ]);
    }

    public function test_embroidery_existing_total_payment_as_order()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total),
            'extra' => [
                'date' => date('Y-m-d'),
                'orderId' => 1
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_WAITING_ORDER,
            'missing_payment' => '0.00',
            'order_number' => 1,
        ]);
    }


    public function test_embroidery_existing_missing_payment_as_order()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total/2),
            'extra' => [
                'date' => date('Y-m-d'),
                'orderId' => 1
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_MISSING_PAYMENT,
            'missing_payment' => $total/2,
            'order_number' => 1,
        ]);
    }

    public function test_new_embroidery_total_payment_with_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment(3);
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_PENDING,
            'missing_payment' => '0.00'
        ]);
    }

    public function test_new_embroidery_total_payment_without_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_PENDING,
            'missing_payment' => '0.00'
        ]);
    }

    public function test_new_embroidery_missing_payment_with_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment(3);
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total/2),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_MISSING_PAYMENT,
            'missing_payment' => $this->getPayment($total/2)
        ]);
    }

    public function test_new_embroidery_missing_payment_without_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment(15);
        $total = $this->calculateTotalByService($services, $garment);
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total/2),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response['id'],
            'status' => Order::STATUS_MISSING_PAYMENT,
            'missing_payment' => $this->getPayment($total/2)
        ]);
    }

    // public function test_custom_embroidery()
    // {
    //     $services = [
    //         $this->getSubservice(Subservice::findModel(2)),
    //     ];
    //     $user = $this->makeUser(['permission' => 'order:create']);
    //     $response = $this->actingAs($user)->post('/admin/sale-save', [
    //         'client' => $this->getClient(),
    //         'garment' => $this->getGarment(),
    //         'services' => $services,
    //         'payment' => $this->getPayment(),
    //         'extra' => [
    //             'date' => date('Y-m-d')
    //         ]
    //     ]);

    //     $response->status(200);
    //     $response->assertJsonStructure(['id']);
    //     $this->assertDatabaseHas('order_details', [
    //         'order_id' => $response['id']
    //     ]);
    // }
}
