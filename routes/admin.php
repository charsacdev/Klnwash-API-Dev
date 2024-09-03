<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthentication;
use App\Http\Controllers\Admin\Services;
use App\Http\Controllers\Admin\UserManager;
use App\Http\Controllers\Admin\OrdersManager;
use App\Http\Controllers\Admin\PushNotification;

#middleware group
Route::prefix('admin')->group(function () {

     #admin authentication
     Route::post('/login',[AdminAuthentication::class,'AdminLogin']);
     Route::post('/resetpassword',[AdminAuthentication::class,'ForgotPassword']);
     Route::post('/newpassword',[AdminAuthentication::class,'AdminewPassword'])->middleware('checkUrl');
     
     

});

// Route::post('/register',[AdminAuthentication::class,'Adminregister']);
#authenticated middleware group
Route::group([ 'middleware' => 'auth:sanctum', 'prefix' => 'admin'], function () {

     #super admin functions
     Route::post('/register',[AdminAuthentication::class,'Adminregister'])->middleware('Adminrole');
     Route::post('/addservice',[Services::class,'AddServices'])->middleware('Adminrole');
     Route::post('/addservicecategory',[Services::class,'AddServicesCategory'])->middleware('Adminrole');
     Route::post('/serviceimage',[Services::class,'ImageAddingCategory'])->middleware('Adminrole');
     Route::delete('/deleteserviceimage/{id}',[Services::class,'DeleteImage'])->middleware('Adminrole');
     Route::delete('/deleteservicecategory/{id}',[Services::class,'DeleteServiceSubCategory'])->middleware('Adminrole');
     Route::get('/getservicecategory/{id}',[Services::class,'getServicesCategoryId'])->middleware('Adminrole');
     Route::post('/addservicessubcategory',[Services::class,'AddServicesCategoryPrice'])->middleware('Adminrole');
     Route::get('/allservice',[Services::class,'getServices'])->middleware('Adminrole');
     Route::delete('/deleteservice/{id}',[Services::class,'DeleteService'])->middleware('Adminrole');
     Route::get('/serviceid/{id}',[Services::class,'getServicesId'])->middleware('Adminrole');
     Route::post('/editservices',[Services::class,'EditServices'])->middleware('Adminrole');
     

     #administrative function
     Route::get('/allsubadmins',[UserManager::class,'SubAdmin'])->middleware('Adminrole');
     Route::get('/subadminblock/{id}',[UserManager::class,'SubAdminBlock'])->middleware('Adminrole');
     Route::get('/subadminactivate/{id}',[UserManager::class,'SubAdminActive'])->middleware('Adminrole');
     Route::get('/accessaccount/{id}',[UserManager::class,'AccessSubAdminAccount'])->middleware('Adminrole');
     Route::post('/editsubadmin',[UserManager::class,'SubAdminEditProfile'])->middleware('Adminrole');

     #user management same for sub admins
     Route::get('/alluser',[UserManager::class,'getAllUsers']);
     Route::get('/usersummary/{id}',[UserManager::class,'GetUserInfoSummary']);
     Route::get('/userblock/{id}',[UserManager::class,'BlockUser']);
     Route::get('/useractivate/{id}',[UserManager::class,'UnBlockuser']);
     Route::post('/useredit/{id}',[UserManager::class,'EditUser']);
     Route::post('/orderstatus',[UserManager::class,'EditOrderStatus']);

     #order management same for sub admins
     Route::get('/orderstatistics',[OrdersManager::class,'getAllOrders']);
     Route::get('/ordersreceived',[OrdersManager::class,'ReceivedOrders']);
     Route::get('/orderspending',[OrdersManager::class,'PendingOrders']);
     Route::get('/ordersunconfirmed',[OrdersManager::class,'UnconfirmedOrders']);
     Route::get('/orderscompleted',[OrdersManager::class,'CompletedOrders']);
     Route::get('/singlesummary/{code}',[OrdersManager::class,'SingleOrderInfoSummary']);

     #order reviews
     Route::get('/orderreviewscards',[OrdersManager::class,'AllOrderReviews']);
     Route::get('/allorderreviews',[OrdersManager::class,'getAllUsersReviews']);
     

     #admin notification
     Route::post('/push-notification/broadcast', [PushNotification::class,'sendBroadcast'])->middleware('Adminrole');


     #admin profile for super admin and sub admin
     Route::get('/profile',[AdminAuthentication::class,'AdminProfile']);
     Route::post('/updateprofile',[AdminAuthentication::class,'AdminUpdateProfile']);
     Route::post('/updatepassword',[AdminAuthentication::class,'UpdatePassword']);
     Route::get('/logout',[AdminAuthentication::class,'logout']);
     

 
});
?>