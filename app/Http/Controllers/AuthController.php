<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    public function register(Request $req){
        $val = Validator::make($req->all(),[
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if ($val->fails()) {
            return response()->json($val->messages(),422);
        }

        $inp = $req->all();
        $inp['password'] = bcrypt($inp['password']);
        $user = User::create($inp);

        $su['email'] = $user->email;

        return response()->json(['success'=>true,'data'=>$su]);
    }

    public function login(Request $req){
        $val = Validator::make($req->all(),[
            'email'=>'required|email',
            'password'=>'required|min:8',
        ]);

        if ($val->fails()) {
            $error = $val->messages();

            return response()->json($error);
        }

        if (Auth::attempt(['email' => $req->email, 'password' => $req->password])) {
            $user = Auth::user();

            if ($user->role == 1) {
                $suc['token'] = $user->createToken('auth_token',['server:admin'])->plainTextToken;
            }else{
                $suc['token'] = $user->createToken('auth_token',['server:user'])->plainTextToken;
            }

            $suc['user_email'] = $user->email;
            $suc['role'] = $user->role;
            $suc['status'] = true;
            $suc['email'] = [""];
            $suc['password'] = [""];

            return response()->json($suc);
        }else{
            return response()->json([
                'email' => ['wrong email or password'],
            ]);
        }
    }

    public function logout(Request $req){
        $req->user()->currentAccessToken()->delete();
        return response()->json(['success'=>true]);
    }
}
