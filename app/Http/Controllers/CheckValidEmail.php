<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use NeverBounce\Auth;
// use NeverBounce\Single;
// use NeverBounce\Account;
#use NeverBounce\API\NeverBounceAPI;


class CheckValidEmail extends Controller
{
    #check neverbounce email verification
    public function EmailVerification(){
        $api_key="private_7362d1749890cc4b210992230aab4b91";
       
        \NeverBounce\Auth::setApiKey($api_key);
        $info = \NeverBounce\Account::info();
        #dd($info);
        #var_dump($info);
        $verification = \NeverBounce\Single::check('michealbenard077@gmail.com',true,true);
        dd($verification);
        // return response()->json([
        //       'message'=>$verification->is('valid'),
        //       'message2'=>$verification->is('deliverable')
        // ]);

    }
    
  

    // public function EmailVerification() {
    //     $api_key = "private_7362d1749890cc4b210992230aab4b91";
    //     Auth::setApiKey($api_key);
    
    //     try {
    //         $verification = Single::check('michealbenard077@gmail.com');
    
    //         return response()->json([
    //             'message' => $verification->is('valid'),
    //             'message2' => $verification->is('deliverable'),
    //             'account'=>Account::info()
    //         ]);
    //     } 
    //     catch (\Throwable $e) {
    
    //         return response()->json([
    //             'error' => 'An error occurred during email verification.',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

}
