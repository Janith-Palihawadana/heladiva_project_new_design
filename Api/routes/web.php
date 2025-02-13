<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return response()->json(
        [
            'data' => [],
            'message' => 'Un Authorized',
            'status' => ResponseAlias::HTTP_UNAUTHORIZED
        ],
        ResponseAlias::HTTP_UNAUTHORIZED
    );
})->name('login');
