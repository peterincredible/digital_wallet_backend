<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
Route::get('/currencies/getAll', [CurrencyController::class, 'getAll'])
    ->middleware('auth:sanctum');

Route::get('/wallets/user_wallet', [WalletController::class, 'getUserWallet'])
    ->middleware('auth:sanctum');
Route::post("/wallets/addFund", [WalletController::class, 'addFund'])
       ->middleware('auth:sanctum');

Route::post("/transactions/init",[TransactionController::class,"initTransaction"])
       ->middleware('auth:sanctum');
Route::get("/transactions/getTransactionDetails",[TransactionController::class,"getTransactionDetails"])
        ->middleware('auth:sanctum');
Route::get("/transactions/get/{id}",[TransactionController::class,"get"])->middleware('auth:sanctum');
Route::post("/transactions/checkMail",[TransactionController::class,"checkEmail"])->middleware('auth:sanctum');
Route::post("/transactions/sendFund",[TransactionController::class,"sendFund"])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
