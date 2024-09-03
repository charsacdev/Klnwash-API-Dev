<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminTable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewAdminUser;
use App\Mail\AdminResetLink;
use App\Mail\AdminNewpassword;

class AdminAuthentication extends Controller
{
    #Admin Login
    public function Adminlogin(Request $request){

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
               
                   if(Auth::guard('admin')->attempt($credentials)){
                       #auth user
                       $user=Auth::guard('admin')->user();

                       if($user->account_status=='active'){
                            return response()->json([
                                'message'=>'success',
                                'data'=>$user,
                                'token_type' => 'Bearer',
                                'token_key' =>$user->createToken('AdminToken')->plainTextToken,
                            ]);
                       }
                       else{
                            #$request->user()->tokens()->delete();
                            return response()->json([
                                'reason' => 'account is blocked',
                                'code'=>'201',
                            ], 422);
                       }
                       
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
               return response(["code" => 3, "error" => $th->getMessage()]);
           }
   
        }


    #register a user admin
    public function Adminregister(Request $request){

        try{

            #validation rules
            $rules = [
                'first_name' => 'required|string',
                'last_name'=> 'required|string',
                'email' => 'required|email|unique:admin_tables',
                'phone'=>'required|numeric|digits:11',
                'Admin_State'=>'required',
                'Admin_Lga'=>'required',
                'Address'=>'required',
                'Manage_State'=>'required',
                'Manage_Lga'=>'required',
            ];
    
            $messages = [
                'first_name.required'=>'Please enter your first name !',
                'last_name.require'=>'Please enter your last name !',
                'email.required' => 'We need to know your email address!',
                'email.unique' => 'This email address is already in use !',
                'phone.required'=>'Please a number is required !',
                'phone.number'=>'Only number is allowed !',
                'phone.digits'=>'Phone mumber must be up to 11 digits',
                'Admin_State.required'=>'Select this managers state of origin',
                'Admin_Lga.required'=>'Select this managers lga of origin',
                'Address.required'=>'Select this managers address',
                'Manage_State.required'=>'Specify a state for this admin operations',
                'Manage_Lga.required'=>'Specify a local government for this admin operations',
                
            ];
    
            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                   
                   #generate referal code
                   $codeGen = "KLNW".sprintf("%05d", rand(0, 9999));
                
                    #create a new admin
                    $user = AdminTable::create([
                       #admin personal profile
                        'first_name'=>$request->first_name,
                        'last_name'=>$request->last_name,
                        'email'=>$request->email,
                        'password'=>'',
                        'phone'=>$request->phone,
                        'authcode'=>$codeGen,
                        'state'=>$request->Admin_State,
                        'lga'=>$request->Admin_Lga,
                        'address'=>$request->Address,
                        'Manage_State'=>$request->Manage_State,
                        'Manage_Lga'=>$request->Manage_Lga,
                        'role'=>'admin',
                        'last_login'=>'',
                        'profile_photo'=>'',
                        'account_status'=>'active',
                  ]);
    
                if($user){
                    #email parameters
                    $email=$request->email;
                    $username=$request->first_name." ".$request->last_name;
                    $resetId=$codeGen;

                    #send email to user and to referal
                    Mail::to($email)->send(new NewAdminUser($email,$username,$resetId));
                    
                    
                    return response()->json([
                            'code'=>200,
                            'reason'=>'Account successfully created',
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


     #user forgot password
     public function ForgotPassword(Request $request){

        try{
            $rules = [
                'email' => 'required|email',
            ];

            $messages = [
                'email.required' => 'Enter email address to get a reset link',
            ];

            # Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                #checking of email exist
                $user=AdminTable::where(['email'=>$request->email])->first();
                if($user){
                    #write a code generating function
                    $codeGen = sprintf("%05d", rand(0, 9999));
                    #update the auth_code in table
                    $updateAuthCode=AdminTable::where(['email'=>$request->email])->update([
                       'authcode'=>$codeGen
                    ]);

                    #get the selected parameters
                    $userinfo=AdminTable::where(['email'=>$request->email])->first();

                    #email parameters
                    $email=$userinfo->email;
                    $code=$userinfo->authcode;
                    $userId=$userinfo->id;
                    $username=$userinfo->last_name;

                    #send email
                    Mail::to($email)->send(new AdminResetLink($email,$code,$userId,$username));
                    return response()->json([
                        "code"=>1,
                        "reason" => "email sent",
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

    
     #update new password
     public function AdminewPassword(Request $request){
        try{
                $rules = [
                    'password' => 'required|string|min:8',
                
                ];

                $messages = [
                    'password.required'=>'Password is required to proceed !',
                ];

                # Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{
                    #checking of email exist
                    $user=AdminTable::where(['email'=>base64_decode($request->kln)])->first();
                    if($user){
                       
                         #write a code generating function
                        $codeGen = "KLN".sprintf("%05d", rand(0, 9999));
                        
                         #update the password in table and code
                        $updateAuthCode=AdminTable::where(['email'=>base64_decode($request->kln)])->update([
                          'password'=>Hash::make($request->password),
                          'authcode'=>$codeGen,

                        ]);

                        #get the selected parameters
                        $userinfo=AdminTable::where(['email'=>$user->email])->first();

                        #email parameters
                        $email=$user->email;
                        $username=$user->last_name;

                        #send email
                        Mail::to($email)->send(new AdminNewpassword($email,$username));
                        return response()->json([
                            'reason' => 'Password Updated',
                            'code'=>'200',
                        ], 201);
                    }
                    else{

                        return response()->json([
                            'reason' => 'An error occure check again please',
                            'code'=>'200',
                        ], 200);
                    }
                }
            }
            catch (\Throwable$th) {
                return response(["code" => 3, "error" => $th->getMessage()]);
            }
     }


     #Admin Profile
     public function AdminProfile(Request $request){
        return $request->user();
     }


     #Admin Update Profile
     public function AdminUpdateProfile(Request $request){
        try{
              $rules = [
                  'first_name'=>'required',
                  'last_name'=>'required',
                  'state'=>'nullable',
                  'lga'=>'nullable',
                  'address'=>'required',
                  'email' => 'required|email',
                  'phone'=>'nullable|numeric|min:11',
                  

              ];

              $messages = [
                  'first_name.required' => 'Please enter a first name to proceed !',
                  'last_name.required' => 'Please enter your last name to proceed !',
                  'email.required' => 'Please enter your email address !',
                  'state.required'=>'Please select a state',
                  'lga.required'=>'Please select a local governemnt area',
                  'address.required'=>'Please enter your address',
                  'phone.required'=>'Please enter a valid phone number !',
                  

              ];

              #Validate the request
              $validator = Validator::make($request->all(), $rules,$messages);
              if ($validator->fails()) {
                  return response()->json(['errors' => $validator->errors()], 422);
              }
              else{
                      $userinfo=$request->user();
                      $updateInfo=AdminTable::where(['id'=>$request->user()->id])->update([
                          'first_name'=>strip_tags($request->first_name),
                          'last_name'=>strip_tags($request->last_name),
                          'state'=>strip_tags($request->state),
                          'lga'=>strip_tags($request->lga),
                          'address'=>strip_tags($request->address),
                          'email'=>$request->email,
                          'phone'=>strip_tags($request->phone),
                          
                      ]);
                      if($updateInfo){
                          return response()->json([
                              'reason' => 'profile information updated',
                              'code'=>'200',
                          ], 200);
                      }
                      else{
                          return response()->json([
                              'reason' => 'an error occured',
                              'code'=>'201',
                          ], 422);
                      }
              }
        }
      catch (\Throwable$th) {
          #return response(["code" => 3, "error" => $th->getMessage()]);
          return response(["code" => 3, "error" => "an error occured"]);
      }
      
  }

     #Update Passsword Admin
     public function UpdatePassword(Request $request){
        try{
            $rules = [
                'old_password' => 'required',
                'new_password' => 'required|string|min:8',
        
    
            ];
    
            $messages = [
                'old_password.required'=>'Old Password is required to proceed !',
                'new_password.required'=>'Password is required to proceed !',
                'new_password.min'=>'Password must contain 8 character !',
    
            ];

            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{

                #get user request and the existing password
                $userinfo=$request->user();
                $updatepass=AdminTable::where(['id'=>$request->user()->id])->first();

                #check old password if correct
                if($updatepass and Hash::check($request->old_password, $userinfo->password)){

                    #update the password
                    $updateInfoPassword=AdminTable::where(['id'=>$request->user()->id])->update([
                        'password'=>Hash::make($request->new_password),
                    ]);
                    if($updateInfoPassword){
                         #email parameters
                         $email=$userinfo->email;
                         $username=$userinfo->first_name." ".$userinfo->last_name;
 
                         #send email
                         Mail::to($email)->send(new AdminNewpassword($email,$username));
                        return response()->json([
                            'reason' => 'Password update successfully',
                            'code'=>'200',
                        ], 200);
                    }
                    
                }else{
                    return response()->json([
                        'reason' => 'Old password is incorrect',
                        'code'=>'201',
                    ], 422);
                }
            }
          }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }



     #logout code
     public function logout(Request $request){
            $request->user()->tokens()->delete();

            return response()->json([
                'code'=>201,
                'message' => 'admin logged out'
            ]);
        }


}
