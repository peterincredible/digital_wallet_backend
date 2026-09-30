<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use stdClass;

class TransactionController extends Controller
{
    public function initTransaction(Request $request){
         $transaction = (new TransactionService())->initTransaction();
         return response()->json($transaction);
    }
    public function getTransactionDetails(Request $request){
        $userId = $request->user()->id;
        $limit = isset($request->limit)? isset($request->limit) :10;
        $transactions = TransactionDetail::with(["user","transaction.currency","transaction.reciever","transaction.sender"])
                                          ->where('user_id', $userId)
                                        //   ->orWhere()
        
       
        ->paginate($limit)
        ->withQueryString();
        return response()->json($transactions);

    }
    public function checkEmail(Request $request){
        $user = User::where("email",$request->email)->first();
        // return $user;
        $temp_reciever = new stdClass();
         $temp_reciever->id=null;
        
        if(!$user || ($user->email == $request->user()->email)){
            $temp_reciever->id=null;
        }else{
            $temp_reciever->id=$user->id;
        }
        return response()->json($temp_reciever);
    }
    public function get(Request $request,int $id){
        $userId = $request->user()->id;
       
        // $transaction = Transaction::with(["sender","reciever","currency"])->where("id",$id)//->first();
        //            ->where(function($query) use($userId){
        //                   $query->where("sender_id",$userId)
        //                         ->orWhere("reciever_id",$userId);
        //            })->first();
        // return $id;
        $transaction = TransactionDetail::with(["user","transaction.currency","transaction.reciever","transaction.sender"])
                                         ->where("id",$id)->first();
                                        //   ->where('user_id', $userId);
        return response()->json($transaction);
        
    }
    public function sendFund(Request $request){
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            "transaction_id"=>"required",
            "reciever_id"=>"required",
            "currency_id"=>"required"
        ]);
         $amount = $request->amount;
         $currency_id = $request->currency_id;
         $transaction_id = $request->transaction_id;
         $reciever_id=$request->reciever_id;
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
         $status = (new TransactionService())->sendFund($amount,$currency_id,$reciever_id,$user->id,$transaction_id);
         if($status == 1){
                return response()->json(['message' => 'Funds Sent successfully.']);
         }else{
            return response()->json([
                    'status' => 'error',
                    'message' => 'Error during processing data in transaction.',
                    // 'error' => $e-s>getMessage() // Omit or adjust in production for security
                ], 500);
         }

    }
}
