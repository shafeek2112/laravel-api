<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Factory as Faker; 
use Tests\TestCase;
use App\Models\Thread;

class LoanApplicationTest extends TestCase
{
    /**
    * Get loan applciation before login
    *
    * @return void
    */
    public function testGetLoanApplicationByUserWithoutToken():void
    {
        $this->json('GET', 'api/v1/user/loan-application', ['Accept' => 'application/json'])
            ->assertStatus(401  )
            ->assertJson([
                "message"   => "Unauthenticated.", 
            ]);
    }
    
    /**
    * Get loan applciations after login
    *
    * @return void
    */
    public function testGetLoanApplicationByUserWithToken():void
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-application', [], ['Accept' => 'application/json']);
        dd(gettype($response));



        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-application', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Succcess", 
                "message"   => "Successfully Fetched Loans", 
        ]);
    }

    public function getToken()
    {
        $userData = [
            "email"     => "approved@aspire.test",
            "password"  => "approved",
        ];

        $response = $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Success",
                "message"   => "Successfully Logged In",
                "code"      => 200
        ]);;
        return $response->json()['data']['accessToken'];
        
    }
}
