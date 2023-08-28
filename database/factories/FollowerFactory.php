<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Follower;

class FollowerFactory extends Factory
{
    protected $model = Follower::class;
    
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
            'created_at' => $creation_time,
            'updated_at' => $creation_time,
        ];
    }
}
