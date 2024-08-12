<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LoginTeamController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validatedData = $request->validate([
            'team-email' => 'required|email',
        ]);

        $request->session()->put('teamEmail', $validatedData['team-email']);

        return Socialite::driver('laravelpassport')->redirect();
    }
}
