<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\Http\Repository\PaymentRepository;


use App\Models\Account;

use App\Models\Wallet;

use App\Models\Transaction;

use Illuminate\Support\Str;

use DB;

use Auth;

class AccountController extends Controller
{
    private $account;
    private $result;
    protected $payment;

    public function __construct(PaymentRepository $payment)
    {
        $this->payment = $payment;
    }

    public function getBalance(Request $request){
      
      $details = Account::where("user_id" , Auth::user()->id)->first();

      if(!$details){
        return response()->json([
            'success' => false,
            'message' => 'Unable to fetch your Account Details',
          ]);
        }


        return response()->json([
          'success' => true,
          'message' => 'Account Details fetched successfully',
          'data' => $details
        ]);
    }

    public function create(Request $request){
         $validator = Validator::make($request->all(), [
        'account_name' => 'required',
        'account_number' => 'required|string|max:10|min:10',
        'bank' => 'required',
        ]);

        if ($validator->fails()) {
          return response()->json([
            'success' => false,
            'message' => $validator->errors(),
          ], 401);
        }

        DB::transaction(function() use ($request){

            $this->account = Account::create([
              'account_name'=> $request['account_name'],
              'account_number'=>$request['account_number'],
              'bank'=>$request['bank'],
              'user_id'=>Auth::user()->id,
              'balance'=> 0.00,
            ]);

            $wallet = Wallet::create([
              'account_id'=> $this->account->id,
              'wallet_number'=>substr(str_shuffle("0123456789"), 0, 5),
            ]);

            $this->account->wallet = $wallet;
            
        });

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $this->account,
        ]);
    }

    public function topUp(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
          return response()->json([
            'success' => false,
            'message' => $validator->errors(),
          ], 401);
        }
        
        $userId = Auth::user()->id;
        $amount = $request['amount'];
        $account = $this->payment->addMoney($userId, $amount );

        if($account){
            $result = Account::where('user_id', $userId)->first();
            $transaction = Transaction::create([
                'user_id' => $userId,
                'amount' =>  $amount,
                'action' => 'Topup',
                'status' =>  'SUCCESS',
                'message' => 'Wallet was topup successfully',
            ]);

            return response()->json([
              'success' => true,
              'message' => 'Account Topup successfully',
              'data' => $result
            ]);

        }else {

             $transaction = Transaction::create([
                'user_id' => $userId,
                'amount' =>  $amount,
                'action' => 'Topup',
                'status' =>  'FAILED',
                'message' => 'Wallet was unable to topup',
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to find User Wallet to TopUp',
              ]);
        }

    }

    public function payment(Request $request){

        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
            'receiver'=>'required|integer',
        ]);

        if ($validator->fails()) {
          return response()->json([
            'success' => false,
            'message' => $validator->errors(),
          ], 401);
        }

        $this->account = Account::where("user_id" , Auth::user()->id)->first();
        $amount = $request['amount'];
        $userId = Auth::user()->id;
        if($this->account){
            
            if($amount > $this->account->balance){
                $transaction = Transaction::create([
                    'user_id' => $userId,
                    'amount' =>  $amount,
                    'action' => 'Transfer',
                    'status' =>  'FAILED',
                    'message' => 'Insuficient Wallet balance',
                ]);
                return response()->json([
                'success' => false,
                'message' => 'Insuficient Wallet balance please top-up and try again',
              ]);
            }

            DB::transaction(function() use ($request){
                $amount = $request['amount'];
                $receiver_id = $request['receiver'];
                $userId = Auth::user()->id;
                $balance = $this->account->balance - $amount;
                $this->account->fill([
                  'balance'=>$balance,
                ])->save();

                $account = $this->payment->addMoney($receiver_id, $amount );

                $this->result = $account;
            });

            if($this->result){
                $transaction = Transaction::create([
                    'user_id' => $userId,
                    'amount' =>  $amount,
                    'action' => 'Transfer',
                    'status' =>  'SUCCESS',
                    'message' => 'Wallet transfer to other user successfully',
                ]);

                return response()->json([
                  'success' => true,
                  'message' => 'Account payment successfully',
                  'data' => $this->account
                ]);
            }else{

                 $transaction = Transaction::create([
                    'user_id' => $userId,
                    'amount' =>  $amount,
                    'action' => 'TopTransfer',
                    'status' =>  'FAILED',
                    'message' => 'Wallet transfer to other user was failed',
                ]);
            
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to find User Wallet to Transfer into',
                  ]);
            }

        }else {
            $transaction = Transaction::create([
                'user_id' => $userId,
                'amount' =>  $amount,
                'action' => 'TopTransfer',
                'status' =>  'FAILED',
                'message' => 'Wallet transfer to other user was failed',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to find User Wallet to Transfer into',
              ]);
        }

    }
}
