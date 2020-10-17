<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Factory as Faker; 
use Tests\TestCase;

class AuthenticationTest extends TestCase
{

    protected $faker;

    /** 
    * Create a new faker instance. 
    * 
    * @return void 
    */ 

    public function __construct() { 

        parent::__construct(); 
        $this->faker = Faker::create();
    }

    /**
     * Test signup required field error
     *
     * @return void
     */
    public function testRequiredFieldsForRegistration():void
    {
        $this->json('POST', 'api/v1/auth/signup', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status" => "Error",
                "message" => [
                    "error" => [
                        "name" => ["The name field is required."],
                        "email" => ["The email field is required."],
                        "password" => ["The password field is required."],
                    ]
                ],
                "code"  => 401
            ]);
    }

    /**
    * Test wrong confirm password
    *
    * @return void
    */
    public function testRepeatPassword()
    {
        $userData = [
            "name" => $this->faker->name,
            "email" => $this->faker->password,
            "password" => $this->faker->password,
        ];

        $this->json('POST', 'api/v1/auth/signup', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status" => "Error",
                "message" => [
                    "error" => [
                        "password" => ["The password confirmation does not match."],
                    ]
                ],
                "code"  => 401
            ]);
    }

    /**
    * Test already registered email
    *
    * @return void
    */
    public function testAlreadyTakenEmailRegister()
    {
        $userData = [
            "name" => "Test User 1",
            "email" => "testuser1@aspire.test",
            "password" => "testuser1",
            "password_confirmation" => "testuser1"
        ];

        $this->json('POST', 'api/v1/auth/signup', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Error",
                "message"   =>[
                    "error" => [
                        "email" => [
                            "The email has already been taken."
                        ]
                    ]
                ],
                "code"      => 401
            ]);
    }
    
    /**
    * Test successful registration
    *
    * @return void
    */
    public function testSuccessfulRegistration()
    {
        $userData = [
            "name" => "Shafeek",
            "email" => "shafeek1@example.com",
            "password" => "demo12345",
            "password_confirmation" => "demo12345"
        ];

        $this->json('POST', 'api/v1/auth/signup', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Success",
                "message"   => "User Successfully Registered",
                "data"      => [
                    "name"  => "Shafeek",
                    "email"  => "shafeek1@example.com",
                ],
                "code"  => 200
            ]);
    }

    /** 
    *   Test login before approved
    *   @return void
    */
    public function testLoginBeforeApprove()
    {
        $this->testSuccessfulRegistration();
        $userData = [
            "email" => "shafeek1@example.com",
            "password" => "demo12345",
        ];

        $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Error",
                "message"   => [
                    'error' => 'You account is not activated yet, Please try after sometimes',
                ],
                "code"      => 401
        ]);
    }
    
    /**
    *   Test login after rejected
    *   @return void
    */
    public function testLoginRejectedUser()
    {
        $userData = [
            "email" => "rejected@aspire.test",
            "password" => "rejected",
        ];

        $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Error",
                "message"   => [
                    'error' => 'You account is deactivated. Please contact admin',
                ],
                "code"      => 401
        ]);
    }

    /* 
    *   Test login with wrong url
    *   @return void
    */
    public function testInvalidLogin()
    {
        $userData = [
            "email" => "admin@aspire.test",
            "password" => "password1",
        ];

        $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Error",
                "message"   => [
                    'error' => 'Credentials mismatch',
                ],
                "code"      => 401
        ]);;
    }

    /** 
    *   Test login successfully
    *   @return object 
    */
    public function testLoginSuccess()
    {
        $userData = [
            "email" => "admin@aspire.test",
            "password" => "password",
        ];

        return $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Success",
                "message"   => "Successfully Logged In",
                "code"      => 200
        ]);;
    }

    /** 
    *   @depends testLoginSuccess
    *   Test login successfully
    *   @return void 
    */
    public function testLogOut()
    {
        $response = $this->testLoginSuccess();
        $token = $response->json()['data']['accessToken'];

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/auth/logout', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Success",
                "message"   => "Successfully Logged Out",
                "code"      => 200
        ]);
    }
    
    /** 
    *   @depends testLoginSuccess
    *   Test login successfully
    *   @return void 
    */
    public function testLogoutForNonActiveUser(object $response)
    {
        $token = $response->json()['data']['accessToken'];
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/auth/logout', [], ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message"   => "Unauthenticated.", 
        ]);
    }

}
