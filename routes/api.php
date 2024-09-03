<?php

use App\Http\Controllers\Admin\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuth;
use App\Http\Controllers\UserProfile;
use App\Http\Controllers\UserHomeView;
use App\Http\Controllers\UserOrders;
use App\Http\Controllers\Usernotification;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//----------USER AUTHENTCATION AND MANAGMENT-------------------//
Route::post('/register',[UserAuth::class,'register']);
Route::post('/login', [UserAuth::class,'login']);
Route::post('/forgotpassword', [UserAuth::class,'ForgotPassword']);
Route::post('/resetcode', [UserAuth::class,'RestCode']);
Route::post('/newpassword', [UserAuth::class,'Newpassword']);

#google authentication
Route::post('/googleauth',[UserAuth::class,'googleAuth']);

#24 delete notification
Route::get('delete_notification',[UserProfile::class,'deleteNotification']);


#waitlist
Route::post('/waitlist', [UserAuth::class,'Addwaitlist']);
Route::get('/getwaitlist', [UserAuth::class,'Getwaitlist']);


Route::group(['middleware' => ['auth:sanctum']], function() {

    #basic information for user
    Route::get('userprofileInfo',[UserProfile::class,'BasicProfileInfo']);
    Route::post('userprofileUpdate',[UserProfile::class,'UpdateBasicInfo']);
    Route::post('userpassword',[UserProfile::class,'UpdatePasswordinfo']);
    Route::post('profilephoto',[UserProfile::class,'ProfilePhoto']);
    
    #notification managment
    Route::get('get_notification',[UserProfile::class,'getNotification']);
    

    #address management
    Route::get('get_addresses',[UserProfile::class,'getAddress']);
    Route::post('add_address',[UserProfile::class,'AddAddress']);
    Route::delete('delete_address/{id}',[UserProfile::class,'DeleteAddress']);

    #get referals
    Route::get('get_referal',[UserProfile::class,'getReferals']);
    Route::get('referal_balance',[UserProfile::class,'getReferalBalance']);

    #payment
    Route::post('fund_account',[UserProfile::class,'fundUserWallet']);

    #getting all services
    Route::get('all_services',[UserHomeView::class,'get_services']);
    Route::get('get_all_category/{id}',[UserHomeView::class,'get_services_categories']);

    #checkout 
    Route::post('checkout',[UserOrders::class,'checkout_order']);

    #orders management
    Route::get('order_history',[UserOrders::class,'orderHistroy']);
    Route::get('order_details/{id}',[UserOrders::class,'OrderDetails']);

    #reviews
    Route::post('reviews',[UserOrders::class,'OrderReview']);

    #completed status from paystack when making order
    Route::post('payment_completed',[UserOrders::class,'CompletedPayment']);

    #notification api
     #Route::get('notification',[Usernotification::class,'Notification']);

    Route::post('/push-notification/update-token', [UserProfile::class,'UpdatePushToken']);

    #logout
    Route::get('logout',[UserAuth::class,'logout']);

    #delete account
    Route::get('delete_account',[UserAuth::class,'DeleteAccount']);

    
});


#admin apis
require __DIR__ . '/admin.php';


Route::fallback(function () {
    return response()->json([
        'code' => 404,
        'message' => 'Route Not Found',
    ], 404);
});

