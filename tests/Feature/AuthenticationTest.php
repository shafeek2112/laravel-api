<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRequiredFieldsForRegistration()
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

    public function testRepeatPassword()
    {
        $userData = [
            "name" => "Shafeek",
            "email" => "test@test.com",
            "password" => "12345"
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
            ->assertJsonStructure([
                // "status"    => "Success",
                // "message"   => "User Successfully Registered",
                "data"      => [
                    "name"  => "Shafeek",
                    "email"  => "shafeek11@example.com",
                    "updated_at",
                    "created_at",
                    "id"
                ],
                "code"  => 200
            ]);
        dd($this->response->getContent());
    }
}
