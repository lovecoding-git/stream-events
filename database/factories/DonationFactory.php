<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Donation;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $currencies = ['USD', 'EUR', 'GBP', 'JPY'];
        $creation_time = \Carbon\Carbon::now()->subMonths(3)->addDays(rand(0, 90));

        return [
            'donor_name'       => $this->faker->name,
            'amount'           => $this->faker->randomFloat(2, 1, 500), // Amount between 1 and 500 with 2 decimal points
            'currency'         => $this->faker->randomElement($currencies),
            'donation_message' => $this->faker->sentence(10), // Generates a 10-word sentence. Adjust as needed.
            'created_at' =>  $creation_time,
            'updated_at' => $creation_time,
        ];
    }
}
