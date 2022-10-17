<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|min:2|max:40',
            'email'=>'required|string|email|max:100|unique:users',
            'password'=>'required|string|min:6',

        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors());
        }else{
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password =  Hash::make($request->password);
           if($user->save()){
            return response()->json([
                "msg"=>"User Register Successfully",
                "user"=>$user
            ]);
           }
        }
    }

    // Login Api Method Call
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email'=>'required|string|email',
            'password'=>'required|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());
        }
        if(!$token = auth()->attempt($validator->validated()))
        {
            return response()->json(['success'=>false,'msg'=>'Username & Password is Incoorect']);
        }
           return $this->responseToken($token);
    }

    protected function responseToken($token)
    {
        return response()->json([
            'success'=>true,
            'access_token'=>$token,
            'token_type'=>'Bearer',
            
        ]);
    }

    // logout Method for API
    public function logout()
    {
        try{
            auth()->logout();
            return response()->json(['success'=>true,'message'=>'User Logged Out']);
        }catch(\Exception $e){
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
       
    }

    // Profile MEthod for Api

    public function profile()
    {
        try{
           return response()->json(['success'=>true,'data'=>auth()->user()]);
        }catch(Exception $e){
            return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
        }
    }
}
