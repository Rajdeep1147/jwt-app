<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use Mail;
use App\Mail\VerifyMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
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

    public function verifyEmail($email)
    {
        if(auth()->user()){
            $user = User::where('email',$email)->get();
            if(count($user) >0){
           

                $random = Str::random(40);
                $domain = URL::to('/');
                $url = $domain.'/verify-mail/'.$random;

                

                // $data['email'] = $email;
                $details = [

                    'title' => 'Mail from ItSolutionStuff.com',
                    'body' => 'This is for testing email using smtp',
                    'url'=> $url
                ];
                // Mail::send('verifyMail',['data'=>$data,function($message)]);
                \Mail::to($email)->send(new \App\Mail\VerifyMail($details));
                $user = User::find($user[0]['id']);
                $user->remember_token = $random;
                $user->save();
                return response()->json(['success'=>true,'msg'=>'Mail Sent successfully']);
            }else{
                return response()->json(['success'=>false,'msg'=>'User is Not Found']);
            }
        }else{
            return response()->json(['success'=>false,'msg'=>'User is Not Authenticated']);
        }
    }

    public function verificationMail($token)
    {
        $user = User::where('remember_token',$token)->get();
        if(count($user)>0){
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            $user = User::find($user[0]['id']);
            $user->remember_token ='';
            $user->is_verify = '1';
            $user->email_verified_at = $datetime;
            $user->save();

            return "<h1>Email Verify Successfully</h1>";
        }else{
            return view('404');
        }
    }
}
