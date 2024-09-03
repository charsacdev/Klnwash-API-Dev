<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\users_Tables;
use App\Models\ManageAddress;
use App\Models\Notification_Table;
use App\Models\Referal_Table;
use Illuminate\Support\Facades\Mail;
use App\Mail\KlinwashPasswordNotifyCode;
use GuzzleHttp\Client;
use Carbon\Carbon;

class UserProfile extends Controller
{
    #showing user basic profile information
    public function BasicProfileInfo(Request $request){
        return $request->user();
      
    }

    #update Basic personal Information
    public function UpdateBasicInfo(Request $request){
          try{
                $rules = [
                    'first_name'=>'required',
                    'last_name'=>'required',
                    'state'=>'nullable',
                    'lga'=>'nullable',
                    'address'=>'nullable',
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
                        $updateInfo=users_Tables::where(['id'=>$request->user()->id])->update([
                            'first_name'=>strip_tags($request->first_name),
                            'last_name'=>strip_tags($request->last_name),
                            'state'=>strip_tags($request->state),
                            'lga'=>strip_tags($request->lga),
                            'address'=>strip_tags($request->address),
                            'email'=>$request->email,
                            'phone'=>strip_tags($request->phone),
                            'account_status'=>'active',
                            
                            
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


    #profile photo for  user
    public function ProfilePhoto(Request $request){
        try{
                $rules = [
                    'profile_photo'=>'required|file|mimes:jpeg,png,jpg|max:1024',

                ];

                $messages = [
                    'profile_photo.required' => 'A profile picture is required',
                    'profile_photo.mimes' => 'Only jpeg,png,jpg images are supported',
                    'profile_photo.max' => 'file size should not be more than 1MB',
                ];

                #Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{

                    #handle business document
                    $businessDocument = $request->file('profile_photo');
                    $BusinessDocumentextension = $businessDocument->getClientOriginalExtension();
                    $newBusinessDocumentName= time().uniqid() . '.' . $BusinessDocumentextension;
                    $storagePathBusiness = public_path('KlinwashUploads');
                    $businessDocument->move($storagePathBusiness, $newBusinessDocumentName);
                    

                        $userinfo=$request->user();
                        $updatePhoto=users_Tables::where(['id'=>$request->user()->id])->update([
                            'profile_photo'=>"KlinwashUploads/".$newBusinessDocumentName,
                            
                        ]);
                        if($updatePhoto){

                            #delete previous file
                            if($request->user()->profile_photo!==''){
                                unlink(public_path($userinfo->profile_photo));
                            }
                    
                            #json reponse
                            return response()->json([
                                'reason' => 'Profile Photo added successfully',
                                'photo_link'=>'KlinwashUploads/'.$newBusinessDocumentName,
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



    #update user password information
    public function UpdatePasswordinfo(Request $request){
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
                $updatepass=users_Tables::where(['id'=>$request->user()->id])->first();

                #check old password if correct
                if($updatepass and Hash::check($request->old_password, $userinfo->password)){

                    #update the password
                    $updateInfoPassword=users_Tables::where(['id'=>$request->user()->id])->update([
                        'password'=>Hash::make($request->new_password),
                    ]);
                    if($updateInfoPassword){
                         #email parameters
                         $email=$userinfo->email;
                         $username=$userinfo->first_name." ".$userinfo->last_name;
 
                         #send email
                         Mail::to($email)->send(new KlinwashPasswordNotifyCode($email,$username));
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



    #add addresses for check out
    public function AddAddress(Request $request){
        try{
             
            $rules = [
                'first_name'=>'required',
                'last_name'=>'required',
                'state'=>'required',
                'lga'=>'required',
                'address'=>'required',
                'phone'=>'required|numeric|digits:11',
                

            ];

            $messages = [
                'first_name.required' => 'Please enter a first name to proceed !',
                'last_name.required' => 'Please enter your last name to proceed !',
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
            
                    $userAddress = ManageAddress::create([
                        #address
                        'user_id'=>$request->user()->id,
                        'first_name'=>strip_tags($request->first_name),
                        'last_name'=>strip_tags($request->last_name),
                        'phone'=>strip_tags($request->phone),
                        'state'=>strip_tags($request->state),
                        'lga'=>strip_tags($request->lga),
                        'address'=>strip_tags($request->address)
                    ]);
            
                        if($userAddress){
                            return response()->json([
                                    'code'=>1,
                                    'reason'=>'new address added successfully',
                                    'address'=>$userAddress,
                                ], 200);
            
                        }
               }
         }
        catch (\Throwable$th) {

            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }


    #get all address
    public function getAddress(){
        try{
            $userId=auth()->user(); 
            $getaddress=ManageAddress::where(['user_id'=>$userId->id])->get();
            if($getaddress->count()>0){
                return response()->json([
                    'code'=>1,
                    'address'=>$getaddress,
                ], 200);
            }else{
                return response()->json([
                    'code'=>2,
                    'address'=>$getaddress,
                ], 200);
            }


        }catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }


    #delete address 
    public function DeleteAddress($id){
      try{

          $deleteAddress= ManageAddress::find($id)->delete();

          if($deleteAddress){
              return response()->json([
                    'code'=>1,
                    'reason'=>'address deleted successfully',
                ], 200);
           }else{
                return response()->json([
                    'code'=>2,
                    'reason'=>'could not perform this operation',
                ], 200);
           }
       }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }

    #adding funds to users wallet
    public function fundUserWallet(Request $request){
        try{

            $rules = [
                'amount'=>'required',
                'reference'=>'required',
              ];

            $messages = [
                'amount.required' => 'Paid amount is required !',
                'reference.required' => 'Payment reference is required !',
                ];

            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{

                 #checking old balance
                 $oldBalance=users_Tables::where(['id'=>auth()->user()->id])->first();

                 $updateBalance=users_Tables::where(['id'=>auth()->user()->id])->update([
                    'account_balance'=>($oldBalance->account_balance+$request->amount),
                ]);
                if($updateBalance){
                     #email parameters
                     $email=$oldBalance->email;
                     $username=$oldBalance->first_name." ".$oldBalance->last_name;
                     $reference=$request->reference;
                     $amount=$request->amount;

                     #send email
                     //Mail::to($email)->send(new KlinwashPasswordNotifyCode($email,$username));
                    return response()->json([
                        'code'=>'200',
                        'message' => 'Payment completed successfully',
                    ], 200);
                }
            }

        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }


    #get all users referals
    public function getReferals(){
        try{
            $getReferal=Referal_Table::with('ReferalDetails')->where([
                'referee_id'=>auth()->user()->id
            ])->get();
            if($getReferal->count()>0){
                return response()->json([
                    'code'=>'1',
                    'data' => $getReferal,
                ], 200);
            }
            else{
                return response()->json([
                    'code'=>'2',
                    'message'=>"no active referal",
                ], 422);
            }

        }catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
        
    }
    
    #get referal balance
    public function getReferalBalance(){
        try{
            $getReferalBalance=Referal_Table::where(['referee_id'=>auth()->user()->id])->sum('amount');
            if($getReferalBalance){
                 return response()->json([
                    'code'=>'1',
                    'data' => $getReferalBalance,
                ], 200);
            }
            else{
                return response()->json([
                    'code'=>'2',
                    'message'=>"no active balance",
                ], 422);
            }
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }

    #push notification
    public function UpdatePushToken(Request $request){
        try{
            $rules = [
                'user_id'=>'required',
                'pn_token'=>'required',
              ];


            #Validate the request
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                $getReferalBalance= users_Tables::where('id', $request->user_id)->update(['pn_token'=> $request->pn_token]);
                return response()->json([
                    'code'=>'1',
                    'data' => 'data updated',
                ], 200);
            }
        }
        catch (\Throwable $th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }
    
    
    #get all user notification
     public function getNotification(Request $request){
        try{
           
            $getNotifications= Notification_Table::where('notify_id',auth()->user()->id)->get();
            return response()->json([
                'code'=>'1',
                'data' => $getNotifications,
            ], 200);
            
        }
        catch (\Throwable $th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }
    
    #delete user notification
     public function deleteNotification(){
        try{
            $post24date=Carbon::now()->subHours(24);
            $getNotifications= Notification_Table::where('created_at','<=',$post24date)->delete();
            if($getNotifications){
                return response()->json([
                'code'=>'1',
                'data' => "notification deleted",
            ], 200);
            }
            else{
                return response()->json([
                'code'=>'1',
                'data' => "notification not up to 24hours",
            ], 200);
            }
            
        }
        catch (\Throwable $th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }
    


}
