<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subscriber;

class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $creation_time = \Carbon\Carbon::now()->subMonths(3)->addDays(rand(0, 90));
        
        return [
            'name' => $this->faker->name,
            'tier' => $this->faker->randomElement([1, 2, 3]),
            'created_at' => $creation_time,
            'updated_at' => $creation_time,
        ];
    }
}
