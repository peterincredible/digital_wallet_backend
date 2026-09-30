<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Wallet;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Log;

class TransactionService 
{
    public function initTransaction(){
        $transaction = new Transaction();
        $transaction->status = Transaction::STATUS_PENDING;
        $transaction->refrence_id = $this->generateReferenceId();
        $transaction->init_time= date('Y-m-d H:i:s');
        $transaction->save();
        return $transaction;
    }
    public function addFund(float $amount, int $currency_id,int $user_id,int $transaction_id){
      $date_time = date('Y-m-d H:i:s');
      $data = new stdClass();
      $data->transaction_id=$transaction_id;
      $data->amount = $amount;
      $data->currency_id = $currency_id;
      $data->sender_id = null;
      $data->reciever_id = $user_id;
      $data->transaction_type= Transaction::TRANSACTION_TYPE_TOPUP;
      $data->status = Transaction::STATUS_COMPLETE;
      $data->note= "Funds Added";
    //   $data->refrence_id = $this->generateReferenceId();
      $data->init_time = $date_time;
      $data->completed_time= $date_time;
      $status = $this->createTransaction($data);       
       return $status;
    }
    public function SendFund(float $amount, int $currency_id,int $reciever_id,int $user_id,int $transaction_id){
      $date_time = date('Y-m-d H:i:s');
      $data = new stdClass();
      $data->transaction_id=$transaction_id;
      $data->amount = $amount;
      $data->currency_id = $currency_id;
      $data->sender_id = $user_id;
      $data->reciever_id = $reciever_id;
      $data->transaction_type= Transaction::TRANSACTION_TYPE_P2P;
      $data->status = Transaction::STATUS_COMPLETE;
      $data->note= "Funds Transfer";
    //   $data->refrence_id = $this->generateReferenceId();
      $data->init_time = $date_time;
      $data->completed_time= $date_time;
      $status = $this->createTransaction($data);       
       return $status;
    }
    public function createTransaction(stdClass $data){
        /*

            DB::table('users')
                ->where('votes', '>', 100)
                ->lockForUpdate()
                ->get();

        */
        $status = -1;
       if($data->transaction_type == Transaction::TRANSACTION_TYPE_TOPUP){
            $status = $this->TopupTransaction($data);
       }else{
           $status = $this->P2PTransaction($data);
       }
       return $status;
    }
    public function TopupTransaction(stdClass $data){
        DB::beginTransaction();
        $wallet = Wallet::where('user_id', $data->reciever_id)
                        ->lockForUpdate()
                        ->first(); 
        
        try{
             //create a new transaction
            $transaction = Transaction::find($data->transaction_id);
            if(!$transaction){
                 throw new Exception("transaction not found");
            }
            if($transaction->status == Transaction::STATUS_COMPLETE){
                throw new Exception("transaction is already processed");
            }
                
                $transaction->amount=$data->amount;
                $transaction->currency_id=$data->currency_id;
                $transaction->sender_id=$data->sender_id;
                $transaction->reciever_id=$data->reciever_id;
                $transaction->transaction_type=$data->transaction_type;
                $transaction->status=Transaction::STATUS_COMPLETE;
                $transaction->note=$data->note;
                
                $transaction->completed_time = $data->completed_time;
                $transaction->save();
                //create a transaction detail with just the credit
                $transaction_detail = new TransactionDetail();
                $transaction_detail->transaction_id =$transaction->id;
                $transaction_detail->amount =$transaction->amount;
                $transaction_detail->user_id =$transaction->reciever_id;
                $transaction_detail->transaction_flow = TransactionDetail::TRANSACTION_FLOW_CREDIT;
                $transaction_detail->save();
                //update for nigeria
                if($transaction->currency_id == 1){
                    $wallet->ngn_amount += $transaction_detail->amount;
                }elseif($transaction->currency_id == 2){//update for us dollar
                        $wallet->usd_amount += $transaction_detail->amount;
                }else{//update for usdt
                        $wallet->usdt_amount += $transaction_detail->amount;
                }
                $wallet->save();
            
            
           DB::commit();
           return 1;
        }catch(\Exception $e){
                DB::rollBack();
                Log::error($e->getTrace());
                return -1;
               
        }
            
    }
    public function P2PTransaction(stdClass $data){
        DB::beginTransaction();
        $reciever_wallet = Wallet::where('user_id', $data->reciever_id)
                        ->lockForUpdate()
                        ->first(); 
        $sender_wallet = Wallet::where('user_id', $data->sender_id)
                        ->lockForUpdate()
                        ->first();
        try{
            //update transaction
            $transaction = Transaction::find($data->transaction_id);
            if(!$transaction){
                 throw new Exception("transaction not found");
                 
            }
            if($transaction->status == Transaction::STATUS_COMPLETE){
                throw new Exception("transaction is already processed");
            }
                
                $transaction->amount=$data->amount;
                $transaction->currency_id=$data->currency_id;
                $transaction->sender_id=$data->sender_id;
                $transaction->reciever_id=$data->reciever_id;
                $transaction->transaction_type=$data->transaction_type;
                $transaction->status=Transaction::STATUS_COMPLETE;
                $transaction->note=$data->note;
                $transaction->completed_time = $data->completed_time;
                $transaction->save();

                //create credit transaction detail for the reciever
                $transaction_detail = new TransactionDetail();
                $transaction_detail->transaction_id = $transaction->id;
                $transaction_detail->amount = $transaction->amount;
                $transaction_detail->user_id = $transaction->reciever_id;
                $transaction_detail->transaction_flow = TransactionDetail::TRANSACTION_FLOW_CREDIT;
                $transaction_detail->save();
                //create debit transaction detail for the reciever
                $transaction_detail = new TransactionDetail();
                $transaction_detail->transaction_id = $transaction->id;
                $transaction_detail->amount = $transaction->amount * -1;
                $transaction_detail->user_id = $transaction->sender_id;
                $transaction_detail->transaction_flow = TransactionDetail::TRANSACTION_FLOW_DEBIT;
                $transaction_detail->save();

                //update the sender and reciever wallet
                if($transaction->currency_id == 1){
                    $reciever_wallet->ngn_amount += $transaction->amount;
                    $sender_wallet->ngn_amount -= $transaction->amount;
                }elseif($transaction->currency_id == 2){//update for us dollar
                        $reciever_wallet->usd_amount += $transaction->amount;
                        $sender_wallet->usd_amount -= $transaction->amount;
                }else{//update for usdt
                        $reciever_wallet->usdt_amount += $transaction->amount;
                        $sender_wallet->usdt_amount -= $transaction->amount;
                }
                $reciever_wallet->save();
                $sender_wallet->save();
            
            DB::commit();
            return 1;
        }catch(\Exception $e){

                DB::rollBack();
                Log::error($e->getTrace());
                return -1;
               
        }

       
    }

    public function generateReferenceId(){
        // $check = true;
        // $prefix = "ref-";
        // $datePart = date('Ymd');
        // $randomPart = strtoupper(bin2hex(random_bytes(2)));
        // $reference_id = $prefix . $datePart . '-' . $randomPart;
           $referenceId="";
        do {
                $referenceId = 'ref-' . now()->format('Ymd') . '-'
                    . strtoupper(bin2hex(random_bytes(8)));
            } while (Transaction::where('refrence_id', $referenceId)->exists());

            return $referenceId;

        /*
            do {
                $referenceId = 'ref-' . now()->format('Ymd') . '-'
                    . strtoupper(bin2hex(random_bytes(8)));
            } while (Transaction::where('refrence_id', $referenceId)->exists());

            return $referenceId;

        */

    }

}