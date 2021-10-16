<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

use App\Models\User;

use Auth;

class UserController extends Controller
{
  
   /**
   * Register api.
   *
   * @return \Illuminate\Http\Response
   */

   private $user;
   private $token;


  public function register(Request $request){
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => $validator->errors(),
      ], 401);
    }

  
    $user = User::create([
      'name'=> $request['name'],
      'email'=>$request['email'],
      'password'=> bcrypt($request['password']),
    ]);

    $success['token'] = $user->createToken('appToken')->accessToken;
    
    $this->user = $user;
    $this->token = $success;

   return response()->json([
        'success' => true,
        'token' => $this->token,
        'user' => $this->user
      ]);
  }

  public function login(Request $request){
    if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        $user = Auth::user();
        $success['token'] = $user->createToken('appToken')->accessToken;
        //After successfull authentication
        return response()->json([
          'success' => true,
          'token' => $success,
          'user' => $user
      ]);
    } else {
    //if authentication is unsuccessfull
      return response()->json([
        'success' => false,
        'message' => 'Invalid Email or Password',
    ], 401);
    }
  }

  public function logout(Request $res){
    if (Auth::user()) {
      $user = Auth::user()->token();
      $user->revoke();

      return response()->json([
        'success' => true,
        'message' => 'Logout successfully'
    ]);
    }else {
      return response()->json([
        'success' => false,
        'message' => 'Unable to Logout'
      ]);
    }
  }
}
