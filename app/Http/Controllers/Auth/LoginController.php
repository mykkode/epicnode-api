<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Http401;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \App\Exceptions\Http401
     */
    public function login(Request $request)
    {
       $this -> validateLogin($request);

        if ($this->guard()->attempt($this->credentials($request))) {
            return $this->sendLoginResponse($request);
        }

        $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \App\Exceptions\Http401
     */
    protected function validateLogin(Request $request)
    {
         $validator = Validator::make($request->all(), [
              $this->usernameField() => 'required|string',
             'password' => 'required|string',
         ]);

         if($validator -> fails()) {
             throw new Http401('Invalid authentication fields.');
         }
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            $this -> username($request) => $request->input($this->usernameField()),
            'password' => $request->input('password')
        ];
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        return response()->json([
            "success" => true,
            "data" => [
                "code" => 200,
                "token" => $this->guard()->user()->token
            ]]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \App\Exceptions\Http401
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw new Http401(
            'Bad authentication data.'
        );
    }

    /**
     * Get the field name of the identification field (email or username)
     *
     * @return string
     */
    public function usernameField()
    {
        return 'username';
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username(Request $request)
    {
        if(filter_var($request->input($this->usernameField()), FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        else {
            return 'username';
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
    }


    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

}
