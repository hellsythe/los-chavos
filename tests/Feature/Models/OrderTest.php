<?php

namespace Tests\Feature\Models;

use App\Models\Client;
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

    public function test_new_embroidery_existing()
    {
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $this->getGarment(),
            'services' => [
                $this->getSubservice(Subservice::findModel(1)),
            ],
            'payment' => $this->getPayment(),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);
    }


    public function test_custom_embroidery()
    {
        $user = $this->makeUser(['permission' => 'order:create']);
        $response = $this->actingAs($user)->post('/admin/sale-save', [
            'client' => $this->getClient(),
            'garment' => $this->getGarment(),
            'services' => [
                $this->getSubservice(Subservice::findModel(2)),
            ],
            'payment' => $this->getPayment(),
            'extra' => [
                'date' => date('Y-m-d')
            ]
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $response['id']
        ]);
    }
}
