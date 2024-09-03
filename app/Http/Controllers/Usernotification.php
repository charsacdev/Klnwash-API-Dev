<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification_Table;

class Usernotification extends Controller
{
    #get all user notification
    public function Notification(){
      try{

          $getNotification=Notification_Table::where(['id'=>auth()->user()->id])->latest()->get();
          if($getNotification){
            return response()->json([
                'code'=>'200',
                'data' =>$getNotification,
                ],200);
           }

      }catch(\Throwable$e){
        return response(["code" => 3, "error" => $e->getMessage()]);
      }
    }
}
