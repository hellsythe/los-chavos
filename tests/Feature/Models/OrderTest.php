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
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_PENDING, '0');
    }


    public function test_embroidery_existing_missing_payment()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment) / 2;
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_MISSING_PAYMENT, $total);
    }

    public function test_embroidery_existing_total_payment_as_order()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total, 1);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_WAITING_ORDER, '0.00', 1);
    }


    public function test_embroidery_existing_missing_payment_as_order()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(1)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment) / 2;
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total, 1);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_MISSING_PAYMENT, $total, 1);
    }

    public function test_new_embroidery_total_payment_with_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment(3);
        $total = $this->calculateTotalByService($services, $garment);
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_PENDING, '0.00');
    }

    public function test_new_embroidery_total_payment_without_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment();
        $total = $this->calculateTotalByService($services, $garment);
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_PENDING, '0.00');
    }

    public function test_new_embroidery_missing_payment_with_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment(3);
        $total = $this->calculateTotalByService($services, $garment) / 2;
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_MISSING_PAYMENT, $total);
    }

    public function test_new_embroidery_missing_payment_without_new_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(4)),
        ];
        $garment =  $this->getGarment(15);
        $total = $this->calculateTotalByService($services, $garment) / 2;

        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_MISSING_PAYMENT, $total);
    }

    public function test_update_embroidery_total_payment_with_update_cost()
    {
        $services = [
            $this->getSubservice(Subservice::findModel(3)),
        ];
        $garment =  $this->getGarment(3);
        $total = $this->calculateTotalByService($services, $garment);
        $response = $this->makeUserSentRequestAndAssertStatusResponse($garment, $services, $total);
        $this->assertOrderStatusAndMissingPayment($response['id'], Order::STATUS_PENDING, '0.00');
    }

    protected function makeUserSentRequestAndAssertStatusResponse($garment, $services, $total, $orderId = null)
    {
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $garment,
            'services' => $services,
            'payment' => $this->getPayment($total),
            'extra' => [
                'date' => date('Y-m-d'),
                'orderId' => $orderId
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);

        return $response;
    }

    protected function assertOrderStatusAndMissingPayment($id, $status, $missingPayment, $orderId = 0)
    {
        $this->assertDatabaseHas('orders', [
            'id' => $id,
            'status' => $status,
            'missing_payment' => $missingPayment,
            'order_number' => $orderId,
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
