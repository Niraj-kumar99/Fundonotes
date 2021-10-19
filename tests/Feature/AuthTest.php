<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use app\Http\Requests\SendEmailRequest;


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

    //Logout
    public function test_SuccessfulLogout()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDYyNjk4OCwiZXhwIjoxNjM0NjMwNTg4LCJuYmYiOjE2MzQ2MjY5ODgsImp0aSI6IjdMOU5FMkR5QzRyVFdMZWciLCJzdWIiOjI5LCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.1x0VYum2YFu84Q7GtIAVcK8TxmNE3y3QkAebK8qRCvQ'
        ])->json('POST', '/api/auth/logout');
        $response->assertStatus(200)->assertJson(['message'=> 'User successfully signed out']);
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

      //Forgot Password
    public function test_SuccessfulForgotPassword()
    {
        {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/auth/forgotpassword', [
                "email" => "kumarnkj35@gmail.com"
            ]);
            
            $response->assertStatus(205)->assertJson(['message'=> 'password reset link genereted in mail']);
        }

    }
      public function test_IfGiven_InvalidEmailId()
      {
        {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/auth/forgotpassword', [
                "email" => "kumarnkj@gmail.com"
            ]);
            
            $response->assertStatus(205)->assertJson(['message'=> 'can not find the email addressl']);
        }
      } 

      //Reset Password
      public function test_SuccessfulResetPassword()
      {
          {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDYzNDk1MCwiZXhwIjoxNjM0NjM4NTUwLCJuYmYiOjE2MzQ2MzQ5NTAsImp0aSI6IldZM0xaWXlZNWl5dGpSMzQiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.36JvJK4tPU7CztsJj4vyyQCQ2ETC415Npz-yyFuLplg'
            ])->json('POST', '/api/auth/resetpassword', [
                "new_password" => "kumar3516123",
                "confirm_password" => "kumar3516123"
            ]);
            
            $response->assertStatus(205)->assertJson(['message'=> 'Password updated successfull!']);
          }
      }
      

}
