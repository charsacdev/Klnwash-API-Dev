<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\ExpoPushNotification\CloudMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminTable;
use App\Models\users_Tables;
use App\Models\orders_Tables;
use App\Models\Notification_Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManager extends Controller
{
    #=============================Super Admin Functions================================#

    #------------all users----------------#
    public function getAllUsers(){
            try{
                if(Auth::user()->role==="super"){
                    $allUsers=users_Tables::all();
                        if($allUsers->count()>0){
                        return response(["code" => 200, "data" =>$allUsers]);
                        }
                        else{
                        return response(["code" => 200, "data" =>$allUsers]);
                        }
                }
                else{
                    $allUsers=users_Tables::where(['lga'=>Auth::user()->Manage_Lga])->get();
                        if($allUsers->count()>0){
                          return response(["code" => 200, "data" =>$allUsers]);
                        }
                        else{
                          return response(["code" => 200, "data" =>$allUsers]);
                    }
                }
                
                
            }
            catch (\Throwable$th) {
                #return response(["code" => 3, "error" => $th->getMessage()]);
                return response(["code" => 3, "error" => "an error occured"]);
            }

    }



    #Get User info,pending orders,total orders,sum of order price
    public function GetUserInfoSummary($id){
        try{
            if(Auth::user()->role==="super"){
                $singleUser=users_Tables::with(['orders' => function ($query) {
                    return $query->with('order_type')->withCount('order_quantity')->withSum('order_price','order_price')->groupBy('order_tag_code');
                }])->withCount('orders')->withSum('spent','order_price')->where(['id'=>$id])->get();

                if($singleUser->count()>0){
                  return response(["code" => 1, "data" =>$singleUser]);
                }
                else{
                  return response(["code" => 2, "data" =>$singleUser]);
                }
            }

            else{
                $singleUser=users_Tables::with(['orders' => function ($query) {
                    return $query->where('order_status','received')->groupBy('order_tag_code');
                }])->withCount('orders')->withSum('spent','order_price')->where(['id'=>$id,'lga'=>Auth::user()->Manage_Lga])->get();

                if($singleUser->count()>0){
                  return response(["code" => 1, "data" =>$singleUser]);
                }
                else{
                   return response(["code" => 2, "data" =>"no record found"]);
                }
            }
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }

    }
    
    

    #Order Status
    public function EditOrderStatus(Request $request, CloudMessagingService $cloudMessage){
        try{
            $rules = [
                'order_code'=>'required',
                'order_status'=>'required',
            ];

            $messages = [
                'order_code.required' => 'order code required',
                'order_status.required' => 'order status required',
            ];

            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                
                $userOrdersRaw = orders_Tables::where(['order_tag_code'=>$request->order_code]);

                $userOrders=$userOrdersRaw->update([
                    'order_status'=>$request->order_status,
                ]);

                $userOrdersRawData = $userOrdersRaw->get();
                if($userOrders){
                    // get user
                    $user = users_Tables::find($userOrdersRawData[0]->user_id);
                    $to = $user->pn_token;
                    // send send notification 

                    $title = '';
                    $body = '';

                    switch ($request->order_status) {
                        case 'confirmed':
                                # code...
                                $title = "Order Confirmation";
                                $body = "Order #$request->order_code: Has been Confirmed";
                            break;
                            
                            case 'completed':
                                # code...
                                $title = "Order Completion";
                                $body = "Order #$request->order_code: Has been Completed";
                            break;
                        
                        default:
                            # code...
                            break;
                    }

                    if(strlen($title) > 1){
                        #insert all the messaged mapped to a user
                        Notification_Table::create([
                            'notify_id'=>auth()->user()->id,
                            'notify_type'=>'Order Message',
                            'notify_title'=>$title,
                            'notify_body'=>$body,
                        ]);
                            
                        $cloudMessage->setTitle($title);
                        $cloudMessage->setBody($body);
                        $cloudMessage->setTo($to);
                        $cloudMessage->send();
                    }


                   return response(["code" => 200, "data" =>"updated"],200);
                }
                else{
                    return response(["code" => 200, "data" =>"order code not found"],200);
                }
            }
            
        }
        catch (\Throwable $th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => $th->getMessage()]);
        }


    }



    #Edit User
    public function EditUser(Request $request, $id){
        try{
            $rules = [
                'first_name'=>'required',
                'last_name'=>'required',
                'address'=>'nullable',
                'email' => 'required|email',
                'phone'=>'nullable|numeric|min:11',
                

            ];

            $messages = [
                'first_name.required' => 'Please enter a first name to proceed !',
                'last_name.required' => 'Please enter your last name to proceed !',
                'email.required' => 'Please enter your email address !',
                'address.required'=>'Please enter your address',
                'phone.required'=>'Please enter a valid phone number !',
                

            ];

            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
                 if(Auth::user()->role==="super"){
                        $userinfo=$request->user();
                        $updateInfo=users_Tables::where(['id'=>$id])->update([
                            'first_name'=>strip_tags($request->first_name),
                            'last_name'=>strip_tags($request->last_name),
                            'address'=>strip_tags($request->address),
                            'email'=>$request->email,
                            'phone'=>strip_tags($request->phone),
                            
                        ]);
                        if($updateInfo){
                            return response()->json([
                                'code'=>'200',
                                'reason' => 'updated',
                            ], 200);
                        }
                        else{
                            return response()->json([
                                'reason' => 'an error occured',
                                'code'=>'201',
                            ], 422);
                        }
                 }
                 else{
                        $userinfo=$request->user();
                        $updateInfo=users_Tables::where(['id'=>$id,'lga'=>Auth::user()->Manage_Lga])->update([
                            'first_name'=>strip_tags($request->first_name),
                            'last_name'=>strip_tags($request->last_name),
                            'address'=>strip_tags($request->address),
                            'email'=>$request->email,
                            'phone'=>strip_tags($request->phone),
                            
                        ]);
                        if($updateInfo){
                            return response()->json([
                                'code'=>'200',
                                'reason' => 'updated',
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
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }

    }



    
    #Block User
    public function BlockUser($id){
        try{

            if(Auth::user()->role==="super"){
                $userBlock=users_Tables::where(['id'=>$id])->update([
                    'account_status'=>"blocked",
                ]);
                if($userBlock){
                return response(["code" => 1, "data" =>"blocked"]);
                }
            }
            else{

                $userBlock=users_Tables::where(['id'=>$id,'lga'=>Auth::user()->Manage_Lga])->update([
                    'account_status'=>"blocked",
                ]);
                if($userBlock){
                return response(["code" => 1, "data" =>"blocked"]);
                }
            }
            
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }

    }

    

    #Unblock User
    public function UnBlockuser($id){
        try{
            if(Auth::user()->role==="super"){
                $userBlock=users_Tables::where(['id'=>$id])->update([
                    'account_status'=>"active",
                ]);
                if($userBlock){
                return response(["code" => 1, "data" =>"active"]);
                }
            }
            else{

                $userBlock=users_Tables::where(['id'=>$id,'lga'=>Auth::user()->Manage_Lga])->update([
                    'account_status'=>"active",
                ]);
                if($userBlock){
                return response(["code" => 1, "data" =>"active"]);
                }
            }
            
       }
       catch (\Throwable$th) {
           #return response(["code" => 3, "error" => $th->getMessage()]);
           return response(["code" => 3, "error" => "an error occured"]);
       }

    }


    

    #----------Manager All Admin-----------#
    
    #getting sub admin
    public function SubAdmin(){
        try{
             $allSubAdmin=AdminTable::where(['role'=>'admin'])->get();
             if($allSubAdmin->count()>0){
                return response([
                    "code" => 200,
                    "data" =>$allSubAdmin
                    ],200);
             }
             else{
                return response([
                    "code" => 200,
                    "data" =>$allSubAdmin
                ],200);
             }
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 200, "error" => "an error occured"],200);
        }
    }

    #update block a user
    public function SubAdminBlock($id){
        try{
             $allSubAdminBlock=AdminTable::where(['id'=>$id])->update([
                    'account_status'=>"blocked",
                ]);
             if($allSubAdminBlock){
                return response(["code" => 200, "data" =>"blocked"],200);
             }
             
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 200, "error" => "an error occured"],200);
        }
    }

     #update active a user
     public function SubAdminActive($id){
        try{
             $allSubAdminActive=AdminTable::where(['id'=>$id])->update([
                    'account_status'=>"active",
                ]);
             if($allSubAdminActive){
                return response(["code" => 200, "data" =>"active"],200);
             }
             
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 200, "error" => "an error occured"],200);
        }
    }

    #access subadmin dashboard
    public function AccessSubAdminAccount($id){
        try{

            if(Auth::guard('admin')->loginUsingId($id)){
                #auth user
                $user=Auth::guard('admin')->user();
                    return response()->json([
                        'message'=>'success',
                        'data'=>$user,
                        'token_type' => 'Bearer',
                        'token_key' =>$user->createToken('AdminToken')->plainTextToken,
                    ]);
                
            } 
        }
        catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 200, "error" => "an error occured"],200);
        }
    }
    

   #Admin Update Profile
   public function SubAdminEditProfile(Request $request){
    try{
          $rules = [
              'subadmin_id'=>'required',
              'first_name'=>'nullable',
              'last_name'=>'nullable',
              'state'=>'nullable',
              'lga'=>'nullable',
              'address'=>'nullable',
              'email' => 'nullable|email',
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
                  $updateInfo=AdminTable::where(['id'=>$request->subadmin_id])->update([
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

    


    
}
