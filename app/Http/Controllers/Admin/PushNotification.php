<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\ExpoPushNotification\CloudMessagingService;
use App\Models\users_Tables;
use App\Models\Notification_Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PushNotification extends Controller
{
    #send notification
    public function sendBroadcast(CloudMessagingService $cloudMessage, Request $request){
             $rules = [
                   'title' => 'required',
                   'body'=>'required',
               ];
   
               $messages = [
                   'title.required' => 'Please enter a message title',
                   'body.required'=>'Please enter a body message',
   
               ];
   
               # Validate the request
               $validator = Validator::make($request->all(), $rules,$messages);
               if ($validator->fails()) {
                   return response()->json(['errors' => $validator->errors()], 422);
               }
               else{
                    $title = $request->title;
                    $body = $request->body;
            
                    $cloudMessage->setTitle($title);
                    $cloudMessage->setBody($body);
            
                    // get to
                    $allUsers = users_Tables::where("pn_token","!=","")->get();
                    
                    $to = [];
            
                    for ($i=0; $i < count($allUsers); $i++) { 
                        if(strlen($allUsers[$i]->pn_token) > 1){
                            $to[] = $allUsers[$i]->pn_token;
                            
                            #insert all the messaged mapped to a user
                            Notification_Table::create([
                                'notify_id'=>$allUsers[$i]->id,
                                'notify_type'=>'Broadcast Message',
                                'notify_title'=>$title,
                                'notify_body'=>$body,
                            ]);
                            
                        }
                    }
            
                    $cloudMessage->setTo($to);
                    $cloudMessage->send();
            
                    return response()->json([
                        'code' => '1',
                        'data' => $cloudMessage->getResponse()
                    ]);
              }
        }

    
}
