<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService;

class AuthController extends Controller
{
    use ApiResponser;
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
    * Create user
    *
    * @param  [string] name
    * @param  [string] email
    * @param  [string] password
    * @param  [string] password_confirmation
    * @return [string] message
    */
    public function register(Request $request)
    {
        $register = $this->authService->register($request);  

        if(!empty($register['error'])) 
            return $this->error($register,401);

        return $this->success($register,'User Successfully Registered', 200);
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
    public function login(Request $request)
    {
        /* $attr = $request->validate([
            'email'         => 'required|string|email',
            'password'      => 'required|string',
            'remember_me'   => 'boolean'
        ]);

        if (!Auth::attempt($attr))
            return $this->error('Credentials mismatch', 401);

        $user = $request->user();
        
        if($user['status'] === UserStatus::REJECTED)
            return $this->error('You account is deactivated. Please contact admin', 401);
        
        if($user['status'] === UserStatus::PENDING)
            return $this->error('You account is not activated yet, Please try after sometimes', 401);

        if (request()->remember_me === 'true')
            Passport::personalAccessTokensExpireIn(now()->addDays(15));

        //Genereate the token and save into DB
        $tokenResult = $user->createToken(Config::get('constants.ADMIN_NAME'));
        return $this->token($tokenResult,'Successfully Logged In'); */


        $login = $this->authService->login($request);  
        if(gettype($login) === 'array' && !empty($login['error'])) 
            return $this->error($login,401);

        return $this->success($login,'Successfully Logged In Registered', 200);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $logout = $this->authService->logout($request); 
        if(gettype($logout) === 'array' && !empty($logout['error'])) 
            return $this->error($logout,401);

        return $this->success('','User Logged Out');
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $user = $this->authService->user($request);  
        if(gettype($user) === 'array' && !empty($user['error'])) 
            return $this->error($user,401);

        return $this->success($user,'Fetched User');
    }
}
