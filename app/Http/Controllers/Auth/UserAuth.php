<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\users_Tables;
use App\Models\Referal_Table;
use App\Models\Waitlist;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationEmail;
use App\Mail\ReferalsMail;
use App\Mail\KlinwashAuthCode;
use App\Mail\KlinwashPasswordNotifyCode;


class UserAuth extends Controller
{
    #register user account
    public function register(Request $request){

        try{

            #validation rules
            $rules = [
                'first_name' => 'required|string',
                'last_name'=> 'required|string',
                'email' => 'required|email|unique:users__tables',
                'phone'=>'nullable|numeric|digits:11',
                'password' => 'required|min:8',
                'referee_code'=>'nullable',
            ];
    
            $messages = [
                'first_name.required'=>'Please enter your first name !',
                'last_name.require'=>'Please enter your last name !',
                'email.required' => 'We need to know your email address!',
                'email.unique' => 'This email address is already in use !',
                'phone.required'=>'Please a number is required !',
                'phone.number'=>'Only number is allowed !',
                'phone.digits'=>'Phone mumber must be up to 11 digits',
                'password.required'=>'Password is required to proceed !',
                
            ];
    
            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                #generate referal code
                $codeGen = "KLNW".sprintf("%05d", rand(0, 9999));
                
                #checking for referal
                $referee_id;
                if($request->referee_code!==""){
                    $selectReferee=users_Tables::where(['referal_code'=>$request->referee_code])->first();
                    if($selectReferee && $selectReferee->count()>0){
                        $referee_id=$selectReferee->id;
                    }
                    // else{
                    //     $referee_id="0";
                    //     return response()->json([
                    //         'code'=>'201',
                    //         'reason' => 'referee not found for this code',
                            
                    //     ], 422);
                    // }
                }
               
                
                #without referal
                $user = users_Tables::create([
                #personal profile
                'google_id'=>'0',
                'first_name'=>strip_tags($request->first_name),
                'last_name'=>strip_tags($request->last_name),
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'phone'=>strip_tags($request->phone),
                'state'=>'',
                'lga'=>'',
                'address'=>'',
                'auth_code'=>'',
                'account_balance'=>'75',
                'pay_api_code'=>'',
                'referal_balance'=>'0',
                'referal_code'=>$codeGen,
                'referee_id'=>$referee_id ?? "0",
                'profile_photo'=>'',
                'pin_transaction'=>'',
                'account_status'=>'unverified',
                'pn_token'=>''
    
                ]);
            
                #checking if the referal inserted
                if($user->referee_id!=="0"){
                     #insert into referal table and send an email too
                    $insertReferal=Referal_Table::create([
                        'referee_id'=>$referee_id,
                        'refered_id'=>$user->id,
                        'amount'=>'0'
                    ]);

                    #referal email parameters
                    $refDetails=users_Tables::where('id',$referee_id)->first();
                    $refemail=$refDetails->email;
                    $refname=$refDetails->first_name." ".$refDetails->last_name;
                    Mail::to($refemail)->send(new ReferalsMail($refemail,$refname));
                }
                
    
                #create a token
                $token=$user->createToken('klinwashToken')->plainTextToken;
    
                if($user){
                    #email parameters
                    $email=$request->email;
                    $username=$request->first_name." ".$request->last_name;

                    #send email to user and to referal
                    Mail::to($email)->send(new RegistrationEmail($email,$username));
                    return response()->json([
                            'user'=>$user,
                            'reason'=>'Account successfully created',
                            'token'=>$token
                         ], 200);
    
                } else {
                    return response()->json([
                        'reason' => 'An error occured please try again',
                        'code'=>'201',
                    ], 500);
                }
            }

        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
        }

        

     }

    #user login normal mood
    public function login(Request $request){

     try{

            $rules = [
                'email' => 'required|email',
                'password'=>'required',
            ];

            $messages = [
                'email.required' => 'Please enter your email address !',
                'password.required'=>'Password is required to proceed !',

            ];

            # Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                $credentials = $request->only('email', 'password');
            
                if(Auth::attempt($credentials)){
                    #auth user
                    $user=Auth::user();
                    return response()->json([
                        'reason' => 'authenticated',
                        'code'=>'200',
                        'id'=> $user->id,
                        'google_id'=>$user->google_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'state'=>$user->state,
                        'lga'=>$user->lga,
                        'address'=>$user->address,
                        'account_balance'=>$user->account_balance,
                        'referal_balance'=>$user->referal_balance,
                        'referal_code'=>$user->referal_code,
                        'profile_photo'=>$user->profile_photo,
                        'token_type' => 'Bearer',
                        'token_key' =>$user->createToken('klinwashToken')->plainTextToken,
                        
                    ], 200);
                } 
                else{
                    return response()->json([
                        'reason' => 'invalid login details',
                        'code'=>'201',
                    ], 422);
                }
            }

        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "network error please try again"]);
        }

     }
     
     
     #google auth sign and sign
     public function googleAuth(Request $request){

     try{

             #validation rules
            $rules = [
                'first_name' => 'required|string',
                'last_name'=> 'required|string',
                'email' => 'required|email',
                'google_id'=>'required|numeric',
                'image' => 'required',
            ];
    
            $messages = [
                'first_name.required'=>'Please enter your first name !',
                'last_name.require'=>'Please enter your last name !',
                'email.required' => 'We need to know your email address!',
                // 'email.unique' => 'This email address is already in use !',
                'google_id.required'=>'google id is required !',
                'image.required'=>'Image url required !',
                
            ];
            
            # Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                
                #check if email exisit and login user
                $user=users_Tables::where(['email'=>$request->email,'google_id'=>$request->google_id])->first();
                if($user){
                      
                      #login user with google auth,
                      #$credentials = $request->only('email', 'password');
    
                        if(Auth::loginUsingId($user->id)){
                            #auth user
                            $user=Auth::user();
                            return response()->json([
                                'reason' => 'authenticated',
                                'id'=> $user->id,
                                'google_id'=>$user->google_id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name,
                                'email' => $user->email,
                                'state'=>$user->state,
                                'lga'=>$user->lga,
                                'address'=>$user->address,
                                'account_balance'=>$user->account_balance,
                                'referal_balance'=>$user->referal_balance,
                                'referal_code'=>$user->referal_code,
                                'profile_photo'=>$user->profile_photo,
                                'token_type' => 'Bearer',
                                'token_key' =>$user->createToken('klinwashToken')->plainTextToken,
                                'code'=>'200',
                            ], 200);
                        } 
                        else{
                              return response()->json([
                                'reason' => 'invalid login details',
                                'code'=>'201',
                            ], 422);
                        }
                }
                
                #register user if email dont exisit
                else{
                        $checkmail=users_Tables::where(['email'=>$request->email])->first();
                        if($checkmail){
                              return response()->json([
                                    'reason'=>'an error occured',
                                 ], 200);
                        }
                    
                        #without referal
                        $user = users_Tables::create([
                        #personal profile
                        'google_id'=>$request->google_id,
                        'first_name'=>strip_tags($request->first_name),
                        'last_name'=>strip_tags($request->last_name),
                        'email'=>$request->email,
                        'password'=>'0',
                        'phone'=>'',
                        'state'=>'',
                        'lga'=>'',
                        'address'=>'',
                        'auth_code'=>'',
                        'account_balance'=>'0',
                        'pay_api_code'=>'',
                        'referal_balance'=>'0',
                        'referal_code'=>'',
                        'referee_id'=>'',
                        'profile_photo'=>$request->image,
                        'pin_transaction'=>'',
                        'account_status'=>'unverified',
            
                        ]);
            
                        #create a token
                        $token=$user->createToken('klinwashToken')->plainTextToken;
            
                        if($user){
                            #email parameters
                            $email=$request->email;
                            $username=$request->first_name." ".$request->last_name;
                            #send email
                            Mail::to($email)->send(new RegistrationEmail($email,$username));
            
                             return response()->json([
                                    'user'=>$user,
                                    'reason'=>'Account successfully created',
                                    'token'=>$token
                                 ], 200);
            
                        } else {
                            return response()->json([
                                'reason' => 'An error occured please try again',
                                'code'=>'201',
                            ], 500);
                        }
                    
                }
                
               
            }

        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
        }

     }


     #user forgot password
     public function ForgotPassword(Request $request){

        try{
            $rules = [
                'email' => 'required|email',
            ];

            $messages = [
                'email.required' => 'Enter email address to get a reset code',
            ];

            # Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                #checking of email exist
                $user=users_Tables::where(['email'=>$request->email])->first();
                if($user){
                    #write a code generating function
                    $codeGen = sprintf("%04d", rand(0, 9999));
                    #update the auth_code in table
                    $updateAuthCode=users_Tables::where(['email'=>$request->email])->update([
                    'auth_code'=>$codeGen
                    ]);

                    #get the selected parameters
                    $userinfo=users_Tables::where(['email'=>$request->email])->first();

                    #email parameters
                    $email=$userinfo->email;
                    $code=$userinfo->auth_code;
                    $username=$userinfo->last_name;

                    #send email
                    Mail::to($email)->send(new KlinwashAuthCode($email,$code,$username));
                    return response()->json([
                        "code"=>1,
                        "reason" => "user found and code sent to email",
                    ],200);
                }
                else{

                    return response()->json([
                        "code"=>2,
                        "reason"=> "user not found try creating an account instead",
                    ],422);
                }
            }
        }
            catch (\Throwable$th) {
                return response(["code" => 3, "error" => $th->getMessage()]);
            }
    

     }


     #confirm autocode update
     public function RestCode(Request $request){
        try{
                $rules = [
                    'code'=>'required|numeric',
                    'email' => 'required|email',
                ];

                $messages = [
                    'code.required'=>'Enter code sent to registered email !',
                    'email.required' => 'Enter email reset code was send to !',
                ];

                # Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{
                    #checking of email exist
                    $user=users_Tables::where(['email'=>$request->email,'auth_code'=>$request->code])->first();
                    if($user){
                       
                        return response()->json([
                            'reason' => 'success',
                            'code'=>'201',
                        ], 201);
                    }
                    else{

                        return response()->json([
                            'reason' => 'Failed',
                            'code'=>'201',
                        ], 201);
                    }
                }
            }
            catch (\Throwable$th) {
                return response(["code" => 3, "error" => $th->getMessage()]);
            }
     }

     public function NewPassword(Request $request){
        try{
                $rules = [
                    'email' => 'required|email',
                    'password' => 'required|string|min:8',
                
                ];

                $messages = [
                    'email.required' => 'Enter email reset code was send to !',
                    'password.required'=>'Password is required to proceed !',
                ];

                # Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{
                    #checking of email exist
                    $user=users_Tables::where(['email'=>$request->email])->first();
                    if($user){
                       
                         #write a code generating function
                        $codeGen = sprintf("%04d", rand(0, 9999));
                        
                         #update the password in table and code
                        $updateAuthCode=users_Tables::where(['email'=>$request->email])->update([
                        'password'=>Hash::make($request->password),
                        'auth_code'=>$codeGen,

                        ]);

                        #get the selected parameters
                        $userinfo=users_Tables::where(['email'=>$request->email])->first();

                        #email parameters
                        $email=$user->email;
                        $username=$user->last_name;

                        #send email
                        Mail::to($email)->send(new KlinwashPasswordNotifyCode($email,$username));
                        return response()->json([
                            'reason' => 'Password Updated',
                            'code'=>'201',
                        ], 201);
                    }
                    else{

                        return response()->json([
                            'reason' => 'An error occured check again please',
                            'code'=>'201',
                        ], 201);
                    }
                }
            }
            catch (\Throwable$th) {
                return response(["code" => 3, "error" => $th->getMessage()]);
            }
     }


     #logout code
     public function logout(Request $request){
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }

    #deleting an account
    public function DeleteAccount(Request $request){
            try{
                $deleteUser=users_Tables::where(['id'=>auth()->user()->id])->delete();
                if($deleteUser){
                    $request->user()->tokens()->delete();
                    return response(["code" => 200, "message" => "Account deleted Successfully, You will be logged out shortly.."],200);
                }
            }
            catch (\Throwable$th) {
                #return response(["code" => 3, "error" => $th->getMessage()]);
                return response(["code" => 3, "error" => "an error occured"]);
            }
        }


    #adding waitlist
    public function Addwaitlist(Request $request){
        try{
   
               $rules = [
                   'first_name' => 'nullable',
                   'last_name' => 'nullable',
                   'email' => 'required|email|unique:waitlists',
                   'phone'=>'required',
               ];
               # Validate the request
               $validator = Validator::make($request->all(), $rules);
               if ($validator->fails()) {
                   return response()->json(['errors' => $validator->errors()], 422);
               }
               else{
                      #add waitlist
                      $waitlist=Waitlist::create([
                           'first_name' =>$request->first_name ?? '',
                           'last_name'=>$request->last_name ?? '',
                           'email'=> $request->email,
                           'phone'=>$request->phone
                        ], 200);
                        if($waitlist){
                            return response(["code" => 200, "error" => "waitlist created"],200);
                        }
                    }
                }
           catch (\Throwable$th) {
               #return response(["code" => 3, "error" => $th->getMessage()]);
               return response(["code" => 3, "error" => "an error occured"]);
           }
   
    }

    #getting waitlist
    public function Getwaitlist(){
        try{
            #gettting waitlist
            $getwaitlist=Waitlist::all();
            if($getwaitlist){
                return response(["code" => 200, "data" => $getwaitlist]);
            }
        }
        catch (\Throwable$th) {
               #return response(["code" => 3, "error" => $th->getMessage()]);
               return response(["code" => 3, "error" => "an error occured"]);
        }
    }
}
