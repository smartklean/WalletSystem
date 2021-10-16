<?php

namespace App\Http\Repository;

use App\Models\Account;

class PaymentRepository
{
    protected $payment;

    public function __contruct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function addMoney($userId, $amount){

        $account = Account::where("user_id" , $userId)->first();

        if($account){
            $balance = $amount + $account->balance;
            $account->fill([
              'balance'=>$balance
            ])->save();

           return true;

        }else {

           return false;
        }
       
    }

    
}
