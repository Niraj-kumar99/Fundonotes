<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //creating Note with validated Title and description
    public function test_SuccessfullCreateNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDY0MDc2OSwiZXhwIjoxNjM0NjQ0MzY5LCJuYmYiOjE2MzQ2NDA3NjksImp0aSI6IjRNMkFwUDVtMWJnUGVneU4iLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.UF6Nl9yoU2fmpuaXBiM-twWD6vEW7TQYBnSCBh12wxQ'
            ])->json('POST', '/api/auth/createnote',
            [
                "title" => "fundonote Test 7",
                "description" => "descrip Test 7",
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'note created successfully']);
    }
    public function test_FailNoteCreation()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDcwNjg4MiwiZXhwIjoxNjM0NzEwNDgyLCJuYmYiOjE2MzQ3MDY4ODIsImp0aSI6IjE5a3JEenl4SHA1ZlBmenkiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.S-wD8JDjnhw74pbTPJzrGGnH5A0UNUzJny3tqT1NOOI'
            ])->json('POST', '/api/auth/createnote',
            [
                "title" => "fundonote Test 7",
                "description" => "descrip Test 7",
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Note already created......']);
    }

    /*
     * Updating note by taking proper
     * noteId , title , description and Bearer token .
     */
    public function test_SuccessfullUpdateNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDY1MzEzNywiZXhwIjoxNjM0NjU2NzM3LCJuYmYiOjE2MzQ2NTMxMzcsImp0aSI6IlB6YXZRRFVheGVXclVRMnMiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.OyNSvp4q1i_gju6cV_OOj-goD3T0qg_mfYhcFt7VeoA '
            ])->json('POST', '/api/auth/updatenote',
            [
                "id" => "8",
                "title" => "fundonote 2 Test",
                "description" => "descrip 2 Test",
            ]);
            $response->assertStatus(201)->assertJson(['message' => 'Updation done']);
    }
    public function test_FailUpdateNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDY1MzEzNywiZXhwIjoxNjM0NjU2NzM3LCJuYmYiOjE2MzQ2NTMxMzcsImp0aSI6IlB6YXZRRFVheGVXclVRMnMiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.OyNSvp4q1i_gju6cV_OOj-goD3T0qg_mfYhcFt7VeoA '
            ])->json('POST', '/api/auth/updatenote',
            [
                "id" => "7",
                "title" => "fundonote 2 Test",
                "description" => "descrip 2 Test",
            ]);
            $response->assertStatus(400)->assertJson(['message' => 'no note found']);
    }

    //Deleting note with valid note id of note table
    public function test_SuccessfullDeleteNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDY1MzEzNywiZXhwIjoxNjM0NjU2NzM3LCJuYmYiOjE2MzQ2NTMxMzcsImp0aSI6IlB6YXZRRFVheGVXclVRMnMiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.OyNSvp4q1i_gju6cV_OOj-goD3T0qg_mfYhcFt7VeoA '
            ])->json('POST', '/api/auth/deletenote',
            [
                "id" => "17"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note deleted']);
    }
    //Negetive Test if its a invalid note id
    public function test_FailNoteDeletion()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDcwMjUzMiwiZXhwIjoxNjM0NzA2MTMyLCJuYmYiOjE2MzQ3MDI1MzIsImp0aSI6ImhZNXRWZGpzNHJZUXBYck4iLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.vHAuvkRE53Dhp2YBOtehDyNTRwMm6oCIgYWPVlRrPaA '
            ])->json('POST', '/api/auth/deletenote',
            [
                "id" => "17"
            ]);
        $response->assertStatus(400)->assertJson(['message' => 'no note found']);
    }
}
