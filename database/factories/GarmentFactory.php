<?php

namespace Database\Factories;

use App\Models\Garment;
use Illuminate\Database\Eloquent\Factories\Factory;

class GarmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Garment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => $this->model::STATUS_ACTIVE,
        ];
    }
}
