<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\InvalidClient;
use App\Client as Client;

class AuthenticateClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);
        return $next($request);
    }

     /**
     * Determine if the client is valid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\InvalidClient
     */
    protected function authenticate($request) {

        $token = ["token" => $this->getClientTokenFromRequest($request)];
        if(!is_null($token)){
            $client = Client::where("token", $token)
                ->where("active", 1)
                ->first();
            
            if(!is_null($client)){
                return;
            }
        }

        throw new InvalidClient(
            'Invalid client. Please refresh or delete your cookies and/or cache!'
        );
    }

     /**
     * Get the token from the current request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string or null
     */
    private function getClientTokenFromRequest($request) {
        $token = $request->input('client_token');
        return $token;
    }
}
