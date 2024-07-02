<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyJobController;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'token.expiration'])->group(function(){
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('create-job', [CompanyJobController::class, 'createJob']);
    Route::get('get/jobs', [CompanyJobController::class, 'getAllJobs']);
    Route::get('get-single/job/{id}', [CompanyJobController::class, 'getSingleJob']);
    Route::post('customer-agreement/{jobId}', [CompanyJobController::class, 'customerAgreement']);
});
