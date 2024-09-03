<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminTable;
use App\Models\users_Tables;
use App\Models\Reviews;
use App\Models\orders_Tables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class OrdersManager extends Controller
{

    #===================Super Admin Orders=======================#

    #get all orders counting
    public function getAllOrders(){
        try{
            
           if(Auth::user()->role==="super"){
                $totalrecievedOrders = orders_Tables::where('order_status', 'received')->count();
                $totalunconfirmedOrders = orders_Tables::where('order_status', 'unconfirmed')->count();
                $totalPendingOrders = orders_Tables::where('order_status', 'pending')->count();
                $totalCompletedOrders = orders_Tables::where('order_status', 'completed')->count();
                
                #Get the date one month ago from today
                $oneMonthAgo = Carbon::now()->subMonth();
                $formattedMonthly = $oneMonthAgo->format('Y-m-d');
                $totalRevenueMonthly = orders_Tables::where(['order_status'=>'completed'])->whereDate('order_date','>=',$formattedMonthly)->sum('order_sum_total');
                
                
                #Get the today orders
                $formattedDaily =Carbon::now()->format('Y-m-d');
                $totalRevenueDaily = orders_Tables::where(['order_status'=>'completed'])->whereDate('order_date','=',$formattedDaily)->sum('order_sum_total');
                
               
                
                return response([
                    'totalrecievedOrders'=>$totalrecievedOrders,
                    'totalunconfirmedOrders'=>$totalunconfirmedOrders,
                    'totalPendingOrders' => $totalPendingOrders,
                    'totalCompletedOrders' => $totalCompletedOrders,
                    'totalRevenueMonthly' => $totalRevenueMonthly,
                    'totalDailyRevenueDaily'=>$totalRevenueDaily,
                ]);
           } 
           else{

                #statistical information for subadmin
                $location=Auth::user()->Manage_Lga;
                $totalrecievedOrders=orders_Tables::whereHas('userorder', function ($query) use ($location) {
                    $query->where('lga', $location);
                })
                ->where(['order_status'=>'received'])
                ->groupBy('order_tag_code')
                ->get()
                ->count();

                $totalunconfirmedOrders=orders_Tables::whereHas('userorder', function ($query) use ($location) {
                    $query->where('lga', $location);
                })
                ->where(['order_status'=>'unconfirmed'])
                ->groupBy('order_tag_code')
                ->orderBy('id', 'desc')
                ->get()
                ->count();

                $totalPendingOrders=orders_Tables::whereHas('userorder', function ($query) use ($location) {
                    $query->where('lga', $location);
                })
                ->where(['order_status'=>'pending'])
                #->groupBy('order_tag_code')
                ->orderBy('id', 'desc')
                ->get()
                ->count();

                $totalCompletedOrders=orders_Tables::whereHas('userorder', function ($query) use ($location) {
                    $query->where('lga', $location);
                })
                ->where(['order_status'=>'completed'])
                ->groupBy('order_tag_code')
                ->orderBy('id', 'desc')
                ->get()
                ->count();


            #===========================total revenue===========================#
                #Get the date one month ago from today
                    $oneMonthAgo = Carbon::now()->subMonth();
                    $formattedMonthly = $oneMonthAgo->format('Y-m-d');
                    $formattedDaily =Carbon::now()->format('Y-m-d');
                
                
                #montly revenue
                $totalRevenueMonthly=orders_Tables::whereHas('userorder', function ($query) use ($location) {
                    $query->where('lga', $location);
                  })
                ->where(['order_status'=>'completed'])
                ->groupBy('order_tag_code')
                ->orderBy('id', 'desc')
                ->whereDate('order_date','>=',$formattedMonthly)
                ->sum('order_sum_total');
                
                
                #daily revenue
                $totalRevenueDaily=orders_Tables::whereHas('userorder', function ($query) use ($location) {
                    $query->where('lga', $location);
                  })
                ->where(['order_status'=>'completed'])
                ->groupBy('order_tag_code')
                ->orderBy('id', 'desc')
                ->whereDate('order_date','>=',$formattedDaily)
                ->sum('order_sum_total');
                
            


                return response([
                    'totalrecievedOrders'=>$totalrecievedOrders,
                    'totalunconfirmedOrders'=>$totalunconfirmedOrders,
                    'totalPendingOrders' => $totalPendingOrders,
                    'totalCompletedOrders' => $totalCompletedOrders,
                    'totalRevenueMonthly' => $totalRevenueMonthly,
                    'totalDailyRevenueDaily'=>$totalRevenueDaily,
                ]);
           }
           

        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }

    }

    #get all orders needed will be filtered on the adminpage
    public function ReceivedOrders(){
        try{

            if(Auth::user()->role==="super"){
                 
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function($query){
                        return $query->select('id','first_name','last_name','email','profile_photo');
                    }])
                    #->where('order_status', 'received')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                       return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            else{
                    $location=Auth::user()->Manage_Lga;
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function ($query) use ($location) {
                        $query->where('lga', $location)->select('id','first_name','last_name','email','profile_photo');
                    }])
                    #->where('order_status', 'received')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                    return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }

    }

    #get all pending orders (not needed anymore)
    public function PendingOrders(){
        try{

            if(Auth::user()->role==="super"){
                 
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function($query){
                        return $query->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_status', 'pending')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                       return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            else{
                    $location=Auth::user()->Manage_Lga;
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function ($query) use ($location) {
                        $query->where('lga', $location)->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_status', 'pending')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                    return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }


    }

    #Unconfirmed Orders (not needed anymore)
    public function UnconfirmedOrders(){
        try{

            if(Auth::user()->role==="super"){
                 
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function($query){
                        return $query->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_status', 'unconfirmed')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                      return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            else{
                    $location=Auth::user()->Manage_Lga;
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function ($query) use ($location) {
                        $query->where('lga', $location)->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_status', 'unconfirmed')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                      return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }

    }

    #Completed Order (not needed anymore)
    public function CompletedOrders(){
        try{

            if(Auth::user()->role==="super"){
                 
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function($query){
                        return $query->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_status', 'completed')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                       return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
              }
               else{
                    $location=Auth::user()->Manage_Lga;
                    $receivedOrders=orders_Tables::select(
                        'id','user_id','order_category','service_id','order_type','order_quantity','order_price','order_tag_code','order_status'
                    )->with(['user'=>function ($query) use ($location) {
                        $query->where('lga', $location)->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_status', 'completed')
                    ->withCount('totalOrders')
                    ->withSum('totalPrice','order_sum_total')
                    ->groupBy('order_tag_code')
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($receivedOrders->count()>0){
                       return response(["code" => 1, "data" =>$receivedOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$receivedOrders]);
                    }
            }
            
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }
    }
    
    
    public function SingleOrderInfoSummary($code){
        try{

            if(Auth::user()->role==="super"){
                 
                    $userOrders=orders_Tables::with(['user'=>function($query){
                        return $query->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_tag_code',$code)
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($userOrders->count()>0){
                    return response(["code" => 200, "data" =>$userOrders]);
                    }
                    else{
                    return response(["code" => 200, "data" =>$userOrders]);
                    }
            }
            else{
                    $location=Auth::user()->Manage_Lga;
                    $userOrders=orders_Tables::with(['user'=>function ($query) use ($location) {
                        $query->where('lga', $location)->select('id','first_name','last_name','email','profile_photo');
                    }])
                    ->where('order_tag_code',$code)
                    ->orderBy('id', 'desc')
                    ->get();
        
                    if($userOrders->count()>0){
                    return response(["code" => 1, "data" =>$userOrders]);
                    }
                    else{
                    return response(["code" => 2, "data" =>$userOrders]);
                    }
            }
            
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }
    }


    #get reviews
    public function AllOrderReviews(){

        try{
            if(Auth::user()->role==="super"){

                 #order reviews 
                 $reviewcount=Reviews::groupBy('order_code')->get()->count();
                 $allReviews=Reviews::groupBy('order_code')->get()->sum('review_rating')/Reviews::groupBy('order_code')->get()->count();
               
                if($allReviews){
                  return response([
                     "review_count"=>$reviewcount,
                     "review_average_rating" =>$allReviews,
                    ]);
                }
                else{
                    #json response
                    return response(["code" => 2, "data" =>$allReviews]);
                }
            }
            else{

                #order reviews 
                $location=Auth::user()->Manage_Lga;
                $reviewcount=Reviews::where('user_lga',$location)->groupBy('order_code')->get();
                if($reviewcount->count()>0){

                    $allReviews=Reviews::where('user_lga',$location)->groupBy('order_code')->get()->sum('review_rating')/Reviews::where('user_lga',$location)->groupBy('order_code')->get()->count();
                    $reviewcounting=Reviews::where('user_lga',$location)->groupBy('order_code')->get()->count();
                    if($allReviews){
                      return response([
                         "review_count"=>$reviewcounting,
                         "review_average_rating" =>$allReviews,
                        ]);
                    }
                    else{
                        #json response
                        return response(["code" => 200, "data" =>$allReviews],200);
                    }

                 }
                else{
                    return response(["code" => 200, "data" =>$reviewcount],200);
                }
               
                
            }
             
           }
           catch (\Throwable$th) {
             return response(["code" => 3, "error" => $th->getMessage()]);
          }
     }


     #review all review other by state
     public function getAllUsersReviews(){
        try{
            if(Auth::user()->role==="super"){
                  $allReviews=Reviews::groupBy('user_state')->select('user_state')
                    ->withSum('reviews','review_rating')
                    ->withCount('reviewsCount')
                    ->get();

                    if($allReviews->count()>0){
                      return response(["code" => 200, "data" =>$allReviews],200);
                    }
                    else{
                       return response(["code" => 200, "data" =>$allReviews]);
                    }
               }

            else{
                $location=Auth::user()->Manage_Lga;
                $allReviews=Reviews::where('user_lga',$location)->get();

                    if($allReviews->count()>0){
                      return response(["code" => 200, "data" =>$allReviews],200);
                    }
                    else{
                       return response(["code" => 200, "data" =>$allReviews]);
                 }
             }
            
            
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }

}



}
