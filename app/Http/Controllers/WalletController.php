<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    //

    public function getUserWallet(Request $request){
         $user = $request->user();
         $wallet = Wallet::where("user_id",$user->id)->first();
         return response()->json([
                'wallet' => $wallet,
        ]);
    }
     public function addFund(Request $request){
         $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            "transaction_id"=>"required",
        ]);
         $amount = $request->amount;
         $currency_id = $request->currency_id;
         $transaction_id = $request->transaction_id;
         $user = $request->user();
         $transaction = Transaction::find($transaction_id);
         if($transaction->status == Transaction::STATUS_COMPLETE){
                return response()->json([
                    'status' => 'error',
                    'message' => 'transaction already processed',
                    // 'error' => $e-s>getMessage() // Omit or adjust in production for security
                ], 409);
            }
        //  return "ok";
         /** */
         $status = (new TransactionService())->addFund($amount,$currency_id,$user->id,$transaction_id);
         if($status == 1){
                return response()->json(['message' => 'Funds added successfully.']);
         }else{
            return response()->json([
                    'status' => 'error',
                    'message' => 'Error during processing data in transaction.',
                    // 'error' => $e-s>getMessage() // Omit or adjust in production for security
                ], 500);
         }


         /**/
         
         
    }
}
