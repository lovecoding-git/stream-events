<?php

namespace App\Services;

use App\Models\User;
use App\Models\Follower;
use App\Models\Subscriber;
use App\Models\MerchSale;
use App\Models\Donation;
// Import other necessary models and facades

class PostRegistrationService
{
    public function handle(User $user)
    {
        Follower::factory(rand(300, 500))->create(['user_id' => $user->id]);

        Subscriber::factory(rand(300, 500))->create(['user_id' => $user->id]);

        Donation::factory(rand(300, 500))->create(['user_id' => $user->id]);

        MerchSale::factory(rand(300, 500))->create(['user_id' => $user->id]);
    }
}
