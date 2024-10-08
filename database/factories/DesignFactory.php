<?php

namespace Database\Factories;

use App\Models\Design;
use Illuminate\Database\Eloquent\Factories\Factory;

class DesignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Design::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'minutes' => $this->faker->numberBetween(100, 500),
            'price' => $this->faker->numberBetween(50, 500),
            'status' => $this->model::STATUS_ACTIVE,
        ];
    }
}
