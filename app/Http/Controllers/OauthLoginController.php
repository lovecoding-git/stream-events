<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\User;
use App\Services\PostRegistrationService;

class OauthLoginController extends Controller
{
    protected $postRegistrationService;

    public function __construct(PostRegistrationService $postRegistrationService)
    {
        $this->postRegistrationService = $postRegistrationService;
    }

    public function redirectToGoogle() {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }
    
    public function handleGoogleCallback() {

        $user = Socialite::driver('google')->stateless()->user();

        $finduser = User::where('google_id', $user->id)->first();

        if($finduser){       
                Auth::login($finduser);

                $token = $finduser->createToken('main')->plainTextToken;
       
            }else{
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => bcrypt('password')
                ]);

                $this->postRegistrationService->handle($newUser);
      
                Auth::login($newUser);
      
                $token = $newUser->createToken('main')->plainTextToken;
            }
    
        // Redirect back to the frontend Vue app with the token in URL
        return redirect('http://localhost:3000' . '/login?token=' . $token);
    }
    
}
