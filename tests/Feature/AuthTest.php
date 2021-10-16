<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_SuccessfulRegistration()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',])
        ->json('POST', '/api/auth/register', [
            "firstname" => "vidya",
            "lastname" => "gowda",
            "email" => "vidyagowda@gmail.com",
            "password" => "Vidya@123",
            "confirm_password" => "Vidya@123"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'User successfully registered']);
    }

    public function test_If_Usere_Already_Registered()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/register', 
        [
            "firstname" => "vidya",
            "lastname" => "gowda",
            "email" => "vidyagowda@gmail.com",
            "password" => "Vidya@123",
            "confirm_password" => "Vidya@123"
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Mail already taken......']);

    }

    
    //Login
    public function test_SuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/login', 
        [
            "email" => "kumarnkj35@gmail.com",
            "password" => "kumar3516",
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'User successfully login']);
    }

    public function test_IfUser_With_Invalid_LoginCredentials()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/login', 
        [
            "email" => "kumarnkj35@gmail.com",
            "password" => "kumar@123",
        ]);

        $response->assertStatus(401)->assertJson(['message' => 'Mail or password incorrect']);
    }
}
