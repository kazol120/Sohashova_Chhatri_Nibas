<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectregistrationController;
use App\Http\Controllers\Api\ProjectinformationController;
use App\Http\Controllers\Api\ClientinformationController;
use App\Http\Controllers\Api\ContractpersonController;
use App\Http\Controllers\Api\ContractInformationController;
use App\Http\Controllers\Api\ElectricityController;
use App\Http\Controllers\Api\OtherUtilityController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();





});




    Route::apiResource('projectinformation', ProjectInformationController::class);
    Route::apiResource('projectregistraion', ProjectRegistrationController::class);
    Route::apiResource('client', ClientInformationController::class);
    Route::apiResource('contact_person', ContractPersonController::class);
    Route::apiResource('contaract_information', ContractInformationController::class);
    Route::apiResource('electricity', ElectricityController::class);
    Route::apiResource('othersutility', OtherUtilityController::class);




    













