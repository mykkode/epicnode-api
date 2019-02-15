<?php 

namespace App\Extensions;

//not sure ab this one
use Illuminate\Auth\GuardHelpers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use App\User as User;

class ATG implements Guard {
	use GuardHelpers;
	private $inputKey='';
	private $storageKey='';
	private $request;

	public function __construct  (UserProvider $provider, Request $request, 
		$inputKey = 'user_token', $storageKey = 'token') {
		$this->provider = $provider;
		$this->request = $request;

		$this->inputKey = $inputKey;
		$this->storageKey = $storageKey;
	}

    /**
     * Get the currently authenticated user or authenticate him.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user() {
    	// If 
        if (! is_null($this->user)) {
            return $this->user;
        }

    	$user = null;

        $token = $this->getTokenFromRequest();

         if (!empty($token)) {
            $user = $this->provider->retrieveByCredentials(
                [$this->storageKey => $token]
            );
        }

        return $this->user = $user;
    }

	/**
     * Attempt to authenticate a user by credentials (username and pass)
     * 
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function attempt(array $credentials = [], $remember = false) {
        $this->user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if (!is_null($this->user)) {
        	$this->login($this->user);
            return $this->user;
        }
        return;
    }

     /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }

     /**
     * Get the token for the current request.
     *
     * @return string or null
     */
    public function getTokenFromRequest() {
       	$token = $this->request->input($this->inputKey);
       	return $token;
    }

    public function login(User $user)
    {
        $user->setRememberToken($token = str_random(64));

        $this->provider->updateRememberToken($user, $token);
    }
}