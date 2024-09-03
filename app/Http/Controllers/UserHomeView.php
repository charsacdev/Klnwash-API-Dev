<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\servicesAdmin_Tables;
use App\Models\ServiceCategory;
use App\Models\Services_list;

class UserHomeView extends Controller
{
    #get all services
    public function get_services(){

        try{
            $allservices=servicesAdmin_Tables::all();
            if($allservices){
                return response()->json([
                    'code'=>'1',
                    'data' => $allservices,
                ], 200);
            }
            else{
                return response()->json([
                    'code'=>'3',
                    'message' => 'no avaliable services we will get back shortly',
                ], 422);
            }
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
        }
        
    }


    #get services and categories
    public function get_services_categories($id){

        try{
            $allcategory=Services_list::with('services')
                                ->where(['service_id'=>$id])
                                #->orderBy('services_catergory','asc')
                                ->get();
                                #->groupBy('services_catergory');
                                
            if($allcategory){
                return response()->json([
                    'code'=>'1',
                    'data' => $allcategory,
                ], 200);
            }
            else{
                return response()->json([
                    'code'=>'3',
                    'message' => 'no avaliable services we will get back shortly',
                ], 200);
            }
        }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
        }
        
    }
}
