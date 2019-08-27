<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request ;
use Illuminate\Auth\Events\Registered;
use \App\Exceptions\Http401;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param \Illuminate\Http\Request
     * @throws \App\Exceptions\Http401
     *
     * @return void
     */

    protected function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if($validator -> fails()) {
            throw new Http401($validator->errors(), "Invalid register fields");
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }


    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \App\Exceptions\Http401
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request);

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return response()->json([
            "success" => true,
            "data" => [
                "code" => 200,
                "token" => $user->token
            ]]);
    }
}
