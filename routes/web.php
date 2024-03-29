<?php

use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('api/patient/create', [PatientController::class, 'create']);
Route::get('api/patient/patients', [PatientController::class, 'getAllPatients']);

Route::get('api/token', function () {
    return csrf_token();
});
