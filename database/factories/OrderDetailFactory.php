<?php

namespace Database\Factories;

use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => $this->model::STATUS_ACTIVE,
            'service_id' => 1,
            'subservice_id' => 1,
            'garment_id' => 1,
            'garment_amount' => $this->faker->numberBetween(1, 10),
        ];
    }
}
