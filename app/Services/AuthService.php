<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\UserStatus;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Config;

/**
 * ClaimRepository 
 */
class AuthService
{
    protected $userModelInstance;
    protected $user;

    public function __construct()
    { 
        $this->userModelInstance = new User();
    }

    /* 
    * This will set the Authuser 
    */
    public function setAuthUser(): void
    {
        $this->user = Auth::user();
    }

    /**
    * Store a newly created user.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response or array
    */
    public function register($request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        if($validator->fails())
            return ['error' => $validator->errors()];

        $data['password'] = bcrypt($data['password']);

        return $this->userModelInstance::create($data);
    }

    /**
    * Login user and create token
    *
    * @param  [string] email
    * @param  [string] password
    * @param  [boolean] remember_me
    * @return [string] access_token
    * @return [string] token_type
    * @return [string] expires_at
    */
    public function login($request)
    {
        $data = $request->all();
        
        $validator = Validator::make($data, [
            'email'             => 'required|string|email',
            'password'          => 'required|string',
            'remember_token'    => 'in:true,false'
        ]);

        if($validator->fails())
            return ['error' => $validator->errors()];

        $authInput = ['email' => $data['email'], 'password' => $data['password']];
        if (!Auth::attempt($authInput))
            return ['error' => 'Credentials mismatch'];

        $user = $request->user();
        
        if($user['status'] === UserStatus::REJECTED)
            return ['error' => 'You account is deactivated. Please contact admin'];
        
        if($user['status'] === UserStatus::PENDING)
            return ['error' => 'You account is not activated yet, Please try after sometimes'];

        if (request()->remember_me === 'true')
            Passport::personalAccessTokensExpireIn(now()->addDays(15));

        //Genereate the token and save into DB
        return $user->createToken(Config::get('constants.ADMIN_NAME'));
    }

    /**
    * Logout user (Revoke the token)
    *
    * @return [string] message
    */
    public function logout($request)
    {
        return $request->user()->token()->revoke();
    }
}