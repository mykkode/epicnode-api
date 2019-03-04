<?php 

namespace App\Extensions;

//not sure ab this one
use Illuminate\Auth\GuardHelpers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;

class UserTokenGuard implements Guard {
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
    	// If we have retrieved the user by now, we will return that value.
        // Fetching it multiple times in a session is costly
        if (! is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();

        // If in the request is present a token, we will use it to fetch the data
         if (!empty($token)) {
            $this->user = $this->provider->retrieveByCredentials(
                [$this->storageKey => $token]
            );
        }

        return $this->user;
    }

	/**
     * Attempt to authenticate a user by credentials (username and pass)
     * WARNING: Should only be used on the login page, when you provide
     * a username and a password
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function attempt(array $credentials = [], $remember = false) {
        // Retrieve the first user in the database that matches the provided credentials, in this case the email.
        // WARNING: the retrieveByCredentials() function does NOT verify the password
        $this->user = $this->provider->retrieveByCredentials($credentials);

        // Check if the retrieved user exists and if it does check if its password matches the provided one.
        if ((!is_null($this->user)) && $this->provider->validateCredentials($this->user, $credentials)) {
            // If the credentials are valid, ask the provider to store a new token in the database
            $this->provider->updateRememberToken($this->user, str_random(64));
            // Return our user
	        return $this->user;
        }
        // There is no user that matches these credentials
        return null;
    }

    /**
     * Determine if the current user's token is valid (authenticate him).
     *
     * @return bool
     */
    public function check() {
        $token = ["token" => $this->getTokenFromRequest()];
        $this->user = $this->provider->retrieveByCredentials($token);
        // Check if the retrieved user exists and if it does check if its password matches the provided one.
        if ((!is_null($this->user))) {
            // The token is valid
            return true;
        }
        // There is no user that matches these credentials
        return false;
    }


     /**
     * Validate a user's credentials (EXCLUDING the password)
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
     * Get the token from the current request.
     *
     * @return string or null
     */
    private function getTokenFromRequest() {
       	$token = $this->request->input($this->inputKey);
       	return $token;
    }

    /**
     * Delete the token from the database
     */
    public function logout() {
        if (! is_null($this->user)) {
            $this->provider->updateRememberToken($this->user, null);
        }
    }
}
