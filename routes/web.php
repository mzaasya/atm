<?php

use App\Http\Controllers\Controller;
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

Route::get('/', [Controller::class, 'view']);
Route::get('machines', [Controller::class, 'getMachines']);
Route::get('transactions', [Controller::class, 'history']);

Route::post('/', [Controller::class, 'transaction']);
Route::post('user', [Controller::class, 'getUser']);
