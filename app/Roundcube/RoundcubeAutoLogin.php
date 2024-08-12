<?php

namespace App\Roundcube;

use Exception;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;

class RoundcubeAutoLogin
{
    public function __construct(
        private string $roundcubeLink
    ) {}

    public function login(string $email, string $password): CookieJar
    {
        $tokenResponse = Http::get($this->roundcubeLink);

        preg_match('|<input type="hidden" name="_token" value="([A-z0-9]*)">|', $tokenResponse->body(), $matches);

        if ($matches) {
            $token = $matches[1];
        } else {
            throw new RoundCubeException('Unable to get token, is your RC link correct?');
        }

        $postParams = [
            '_token' => $token,
            '_task' => 'login',
            '_action' => 'login',
            '_timezone' => '',
            '_url' => '_task=login',
            '_user' => $email,
            '_pass' => $password
        ];

        $response = Http::withOptions([
          'cookies' => $tokenResponse->cookies()
        ])
          ->withoutRedirecting()
          ->asForm()
          ->post($this->roundcubeLink . '?task=login', $postParams);

        if (! $response->found()) {
            throw new RoundCubeException('Login failed, please check your credentials.');
        }

        return $response->cookies();
    }
}
