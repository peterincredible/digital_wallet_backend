<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /*
         php artisan make:migration create_currencies_table
         php artisan make:model Currency
         php artisan make:migration create_wallets_table
         php artisan make:model Wallet
         php artisan make:seeder CurrencySeeder
         php artisan db:seed --class=CurrencySeeder
         php artisan make:controller CurrencyController
         php artisan make:controller WalletController
         php artisan make:migration create_transactions_table
         php artisan make:model Transaction
         php artisan make:migration create_transaction_details_table
         php artisan make:model TransactionDetail
         php artisan make:controller TransactionController


    */
    public function register(Request $request){
          $validated = $request->validate([
            'lastName' => ['required', 'string'],
            'firstName' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);
         DB::beginTransaction();
        
        try{
           
            $user = new User();
            $user->last_name = $validated['lastName'];
            $user->first_name = $validated['firstName'];
            $user->name = $validated['firstName']." ".$validated['lastName'];
            $user->email = $validated['email'];
            $user->password = $validated['password']; // The User model's hashed cast hashes this
            $user->save();
            $token = $user->createToken('auth-token')->plainTextToken;
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->save();
            DB::commit();

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);

        }catch(Exception $e){
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error during processing data in transaction.',
                    // 'error' => $e-s>getMessage() // Omit or adjust in production for security
                ], 500);
        }
        
    }
    public function login(Request $request){
       $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        return response()->json([
            'message' => 'Invalid email or password.',
        ], 401);
    }

    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);
    }
    public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully.',
    ]);
}
}
