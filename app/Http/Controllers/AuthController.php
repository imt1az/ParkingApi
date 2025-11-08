<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r){
        $r->validate([
            'name'=>'required|min:2',
            'phone'=>'required|unique:users,phone',
            'password'=>'required|min:6'
        ]);
        $u = User::create([
            'name'=>$r->name,
            'phone'=>$r->phone,
            'password'=>Hash::make($r->password),
            'role'=>$r->role ?? 'driver'
        ]);
        $token = auth('api')->login($u);
        return response()->json([
            'user'=>$u,'access_token'=>$token,'expires_in'=>auth('api')->factory()->getTTL()*60
        ], 201);
    }

    public function login(Request $r){
        $r->validate(['phone'=>'required','password'=>'required']);
        if(! $token = auth('api')->attempt(['phone'=>$r->phone,'password'=>$r->password])){
            return response()->json(['error'=>['code'=>'UNAUTHENTICATED','message'=>'Invalid credentials']], 401);
        }
        return response()->json([
            'user'=>auth('api')->user(),
            'access_token'=>$token,'expires_in'=>auth('api')->factory()->getTTL()*60
        ]);
    }

    public function refresh(){
        return response()->json([
            'access_token'=>auth('api')->refresh(),
            'expires_in'=>auth('api')->factory()->getTTL()*60
        ]);
    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['ok'=>true]);
    }
}
