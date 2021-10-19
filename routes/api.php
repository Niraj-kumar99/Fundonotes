<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LableController;
use App\Http\Controllers\NoteController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']); 
    Route::post('/forgotpassword', [ForgotPasswordController::class, 'forgotPassword']);   
    Route::post('/resetpassword', [ForgotPasswordController::class, 'resetPassword']);

    Route::post('/createnote', [NoteController::class, 'createNote']);
    Route::get('/readnote', [NoteController::class, 'readNote_ByNoteId']);
    Route::post('/updatenote', [NoteController::class, 'updateNote_ByNote']);
    Route::post('/deletenote', [NoteController::class, 'delete_ByNote']);
    Route::get('/allnotes', [NoteController::class, 'allNotes']);

    Route::post('/createlable', [LableController::class, 'createLable']);
    Route::get('/readlables', [LableController::class, 'readLableByLableId']);
    Route::post('/updatelable', [LableController::class, 'updateLableByLableId']);
    Route::post('/deletelable', [LableController::class, 'deleteLable']);
});