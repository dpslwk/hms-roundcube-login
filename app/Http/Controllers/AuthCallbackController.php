<?php

namespace App\Http\Controllers;

use App\Roundcube\RoundCubeException;
use App\Roundcube\RoundcubeAutoLogin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class AuthCallbackController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Socialite::driver('laravelpassport')->user();

        $teamEmail = $request->session()->get('teamEmail');
        abort_if(is_null($teamEmail), Response::HTTP_BAD_REQUEST);

        $response = Http::withToken($user->token)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->post(
                config('services.laravelpassport.host') . 'api/user/can-team-email-login',
                [
                    'teamEmail' => $teamEmail,
                ]
            );

        abort_if($response->failed(), Response::HTTP_UNAUTHORIZED);
        $teamDetails = $response->json();

        $roundcubeAutoLogin = new RoundcubeAutoLogin(config('services.roundcube.url'));

        try {
            $cookies = $roundcubeAutoLogin->login($teamDetails['email'], $teamDetails['emailPassword']);
        } catch (RoundCubeException $e) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        foreach ($cookies as $cookie) {
            Cookie::queue(
                $cookie->getName(),
                $cookie->getValue(),
                0,
                $cookie->getPath(),
            );
        }

        return redirect(config('services.roundcube.url') . '?task=mail');
    }
}
