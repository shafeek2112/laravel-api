<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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

        // $this->withHeader('Authorization', 'Bearer ' . $token)
        //     ->json('GET', 'api/v1/auth/logout', [], ['Accept' => 'application/json'])
        //     ->assertStatus(200)
        //     ->assertJson([
        //         "status"    => "Success",
        //         "message"   => "Successfully Logged Out",
        //         "code"      => 200
        // ]);
    }
    
    /**
    * Get loan applciations after login
    *
    * @return void
    */
    public function testGetLoanApplicationByUserWithToken():void
    {
        $this->json('GET', 'api/v1/user/loan-application', ['Accept' => 'application/json'])
            ->assertStatus(401  )
            ->assertJson([
                "message"   => "Unauthenticated.", 
            ]);

        // $this->withHeader('Authorization', 'Bearer ' . $token)
        //     ->json('GET', 'api/v1/auth/logout', [], ['Accept' => 'application/json'])
        //     ->assertStatus(200)
        //     ->assertJson([
        //         "status"    => "Success",
        //         "message"   => "Successfully Logged Out",
        //         "code"      => 200
        // ]);
    }

    public function getToken()
    {
        $userData = [
            "email" => "admin@aspire.test",
            "password" => "password",
        ];

        $response = $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json']);

        
    }
}
