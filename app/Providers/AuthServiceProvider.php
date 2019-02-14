<?php

namespace App\Providers;
use Illuminate\Auth\TokenGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();


        Auth::extend('ujt', function ($app, $name, array $config) {
            // The token guard implements a basic API token based guard implementation
            // that takes an API token field from the request and matches it to the
            // user in the database or another persistence layer where users are.
            $guard = new TokenGuard(
                Auth::createUserProvider($config['provider'] ?? null),
                $app['request'],
                'user_token',
                'user_token'
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        Auth::extend('cjt', function ($app, $name, array $config) {
            // The token guard implements a basic API token based guard implementation
            // that takes an API token field from the request and matches it to the
            // client in the database or another persistence layer where clients are.
            $guard = new TokenGuard(
                Auth::createUserProvider($config['provider'] ?? null),
                $app['request'],
                'client_token',
                'client_token'
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

       
    }
}
