<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;

use Auth;

class TransactionController extends Controller
{
    

    public function index (Request $request){

        $transaction = Transaction::where('user_id', Auth::user()->id)->get();

        return response()->json([
          'success' => true,
          'message' => 'Transaction history was fetched successfully',
          'data' => $transaction
        ]);
    }


    public function failedTrans(Request $request){

        $transaction = Transaction::where('user_id', Auth::user()->id)->where('status', 'FAILED')->get();

        return response()->json([
          'success' => true,
          'message' => 'Transaction history was fetched successfully',
          'data' => $transaction
        ]);
    }

    public function successTrans(Request $request){

        $transaction = Transaction::where('user_id', Auth::user()->id)->where('status', 'SUCCESS')->get();

        return response()->json([
          'success' => true,
          'message' => 'Transaction history was fetched successfully',
          'data' => $transaction
        ]);
    }


}
