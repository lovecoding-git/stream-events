<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MerchSale;

class MerchSaleFactory extends Factory
{
    protected $model = MerchSale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $itemNames = ['T-Shirt', 'Mug', 'Poster', 'Cap', 'Keychain'];  // Add or modify as per your merch items
        $creation_time = \Carbon\Carbon::now()->subMonths(3)->addDays(rand(0, 90));

        return [
            'item_name'  => $this->faker->randomElement($itemNames),
            'count'      => $this->faker->numberBetween(1, 10),
            'price'      => $this->faker->randomFloat(2, 5, 200),
            'buyer_name' => $this->faker->name,
            'created_at' => $creation_time,
            'updated_at' => $creation_time,

        ];
    }
}
