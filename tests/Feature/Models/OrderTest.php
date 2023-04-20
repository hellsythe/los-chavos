<?php

namespace Tests\Feature\Models;

use App\Models\Client;
use App\Models\Subservice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use WithFaker;

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
        ]);

        $response->status(200);
        $response->assertJsonStructure(['id']);
    }

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
        return [
            'service_id' => [
                'id' => $subservice->service_id,
            ],
            'subservice_id' => [
                'id' => $subservice->id,
            ],
            'point' => [
                'x' => $this->faker()->numberBetween(100, 9999),
                'y' => $this->faker()->numberBetween(100, 9999),
            ],
            'price' => 500
        ];
    }

    protected function getGarment(int $total = null)
    {
        return [
            "data" => [
                "id" => 1
            ],
            "amount" => $total ?? $this->faker()->numberBetween(1, 99)
        ];
    }

    protected function getPayment()
    {
        return [
            'advance' => 300,
        ];
    }
}
