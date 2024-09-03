<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\orders_Tables;
use App\Models\users_Tables;
use App\Models\ServiceCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserOrdersMail;
use App\Models\Referal_Table;
use App\Models\Reviews;

class UserOrders extends Controller
{
    #adding product to cart
    public function AddToCart($id){
       try{
        
            #getting the orders
            $cart=ServiceCategory::where(['id'=>$id])->first();
            if($cart->count()>0){
                 #getting the userId
                $user=auth()->user();

                #adding to cart
                $addCart = orders_Tables::create([
                    'user_id'=>$user->id,
                    'order_category'=>$cart->services_catergory,
                    'service_id'=>$cart->service_id,
                    'order_type'=>$cart->service_name,
                    'order_quantity'=>'',
                    'order_price'=>$cart->services_price,
                    'order_tag_code'=>'',
                    'order_status'=>'cart',
                    'pickup_date'=>'',
                    'delivery_date'=>'',
                    'order_date'=>Carbon::now(),
                    'checkout_address'=>'',

                ]);

                if($addCart){
                    return response()->json([
                        'code'=>1,
                        'message' =>'Order added to cart',
                    ], 200);
                }else{

                    return response()->json([
                        'code'=>2,
                        'message' =>'Please try adding to cart',
                    ], 422);
                }
            }

            else{

                return response()->json([
                    'code'=>4,
                    'message' =>'No product found',
                ], 422);
            }

    }
    catch (\Throwable$th) {
        return response(["code" => 3, "error" => $th->getMessage()]);
    }
 }

    
     #get cart for checkout
     public function checkout_order(Request $request){
         try{
             
             $rules = [
                    'cart'=>[],
                    'pickup_date'=>'required',
                    'delivery_date'=>'required',
                    'pickup_time'=>'nullable',
                    'delivery_time'=>'nullable',
                    'express_delivery'=>'required',
                    'total_amount'=>'required',
                    'vat_amount' => 'required',
                    'address'=>'required',
                    'payment_mode'=>'required',
                ];

                #Validate the request
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
            
                else{
                    
                     #check payment mode and check balance
                    if($request->payment_mode==="klnwash"){
                        #user details
                        $walletBalance=users_Tables::where(['id'=>auth()->user()->id])->first();

                        if($walletBalance->account_balance < $request->total_amount){
                            return response()->json(["code" => 3, "message" =>"insufficient fund"], 422);
                        }
                        $deductBalance=users_Tables::where(['id'=>auth()->user()->id])->update([
                           'account_balance'=>($walletBalance->account_balance-$request->total_amount)
                        ]);

                    }

                    
                    #generate tracking code
                    $codeGen = "KLN".sprintf("%05d", rand(0, 99999999));
                        
                    #adding to cart
                    $countCart=$request->cart;
                    foreach($countCart as $carted){
                        $addCart = orders_Tables::create([
                            'user_id'=>auth()->user()->id,
                            'order_category'=>$carted["services_catergory"],
                            'service_id'=>$carted["service_id"],
                            'order_type'=>$carted["service_name"],
                            'order_quantity'=>$carted["quantity"],
                            'order_price'=>$carted["services_price"],
                            'order_sum_total'=>($carted["quantity"]*$carted["services_price"]),
                            'order_tag_code'=>$codeGen,
                            'order_status'=>'unconfirmed',
                            'pickup_date'=>$request->pickup_date,
                            'delivery_date'=>$request->delivery_date,
                            'pickup_time'=>$request->pickup_time ?? "0",
                            'delivery_time'=>$request->delivery_time ?? "0",
                            'express_delivery'=>$request->express_delivery,
                            'order_date'=>Carbon::now()->format('Y-m-d'),
                            'checkout_address'=>$request->address,
        
                        ]);
                    }
                    
                    
                     #check payment mode and check balance
                    if($request->payment_mode==="klnwash"){
                       
                        #klnwash update order completed
                        $payment=orders_Tables::where(['order_tag_code'=>$codeGen])->update([
                            'order_status'=>'received'
                        ]);
                    }
                    
                    
                    
                    #adding referal balance only once
                    $addReferalEarnings=Referal_Table::where('refered_id',auth()->user()->id)->first();

                    if($addReferalEarnings && $addReferalEarnings->amount ==0){

                        #getting user old referal balance
                        $UserRefBalance=users_Tables::where('id',$addReferalEarnings->referee_id)->first();

                        #update referal balance
                        $insertReferal=Referal_Table::where('refered_id',auth()->user()->id)->update([
                            'amount'=>'150'
                        ]);

                        #update balance
                        $addValue="150";
                        $updateReferal=users_Tables::where('id',$addReferalEarnings->referee_id)->update([
                            'account_balance'=>($UserRefBalance->account_balance+$addValue)
                        ]);

                    }

                    #email parameters
                    $email=auth()->user()->email;
                    $username=auth()->user()->first_name." ".auth()->user()->last_name;
                    
                    #send email to user with order code
                    Mail::to($email)->send(new UserOrdersMail($email,$username,$codeGen));
                    
                    #show message when cart is added
                    return response()->json([
                                'reason' => 'cart updated',
                                'order_code'=>$codeGen,
                                'code'=>'200',
                            ], 200);
                   }
               }catch (\Throwable$th) {
                   return response(["code" => 3, "error" => $th->getMessage()]);
          }
        
     }


     #update payment 
     public function CompletedPayment(Request $request){
        try{
             
            $rules = [
                   'order_code'=>'required',
               ];

               #Validate the request
               $validator = Validator::make($request->all(), $rules);
               if ($validator->fails()) {
                   return response()->json(['errors' => $validator->errors()], 422);
               }
           
               else{
                    #update order completed
                    $payment=orders_Tables::where(['order_tag_code'=>$request->order_code])->update([
                        'order_status'=>'received'
                    ]);
                    if($payment){
                        return response(["code" => 200, "message" =>"successful"],200);
                    }
                    else{
                        return response(["code" => 3, "message" =>"failed"]);
                    }
               }
          }
          catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
         }
     }

     #order histroy
     public function orderHistroy(){
        try{
           $allOrders=orders_Tables::where('user_id',auth()->user()->id)->select('order_tag_code','order_status')->distinct()->get();
           if($allOrders->count() > 1){
              return response(["code" => 200, "data" =>$allOrders],200);
           }
          else{
              #json response
              return response(["code" => 2, "data" =>$allOrders],200);
          }
            
       }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
         }
     }

    
     #get order details
     public function OrderDetails($id){

        try{
            $allDetails=orders_Tables::where(['user_id'=>auth()->user()->id,'order_tag_code'=>$id])->get();
            if($allDetails->count() < 1){
               return response(["code" => 1, "data" =>$allDetails]);
            }
            else{
               #json response
               return response(["code" => 2, "data" =>$allDetails]);
            }
             
        }
         catch (\Throwable$th) {
             return response(["code" => 3, "error" => $th->getMessage()]);
          }
     }


     #order reviews
     public function OrderReview(Request $request){

        try{
             
            $rules = [
                   'order_code'=>'required',
                   'review_rating'=>'required'
                ];

               #Validate the request
               $validator = Validator::make($request->all(), $rules);
               if ($validator->fails()) {
                   return response()->json(['errors' => $validator->errors()], 422);
               }
              else{

                    $newReviews=Reviews::create([
                            'user_state'=>auth()->user()->state,
                            'user_lga'=>auth()->user()->lga,
                            'order_code'=>$request->order_code,
                            'review_rating'=>$request->review_rating
                        ]);
                    if($newReviews){
                        return response(["code" => 200, "data" =>"Review submitted successfully"],200);
                    }             
              }
          }
         catch (\Throwable$th) {
             return response(["code" => 3, "error" => $th->getMessage()]);
          }
     }
}
