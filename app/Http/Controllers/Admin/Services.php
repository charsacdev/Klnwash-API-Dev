<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\servicesAdmin_Tables;
use App\Models\Services_list;
use App\Models\ServiceCategory;

class Services extends Controller
{
    #=========================Manager services===============================#

    #Add services
    public function AddServices(Request $request){
        try{
                $rules = [
                    'service_type'=>'required',
                    'services_image'=>'required|file|mimes:jpeg,png,jpg|max:1024',

                ];

                $messages = [
                    'service_type.required'=>'please enter a service name',
                    'services_image.required' => 'A service picture is required',
                    'services_image.mimes' => 'Only jpeg,png,jpg images are supported',
                    'services_image.max' => 'file size should not be more than 1MB',
                ];

                #Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{

                    #handle business document
                    $Service = $request->file('services_image');
                    $Serviceextension = $Service->getClientOriginalExtension();
                    $newServiceName= time().uniqid() . '.' . $Serviceextension;
                    $storagePathService = public_path('KlinwashUploads');
                    $Service->move($storagePathService, $newServiceName);
                    

                        $userinfo=$request->user();
                        $updatePhoto=servicesAdmin_Tables::create([
                            'service_type'=>$request->service_type,
                            'services_image'=>"KlinwashUploads/".$newServiceName,
                            
                        ]);
                        if($updatePhoto){

                            #json reponse
                            return response()->json([
                                'code'=>'200',
                                'reason' => 'Service Added successfully',
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
                return response(["code" => 3, "error" => $th->getMessage()]);
                #return response(["code" => 3, "error" => "an error occured"]);
            }

    }
    
    
    #adding images
    public function ImageAddingCategory(Request $request){
        try{
              $rules = [
                    'service_id'=>'required',
                    'category_image'=>'required|file|mimes:jpeg,png,jpg|max:1024',
                ];

                $messages = [
                    'service_id.required'=>'Please enter a service ID its required',
                    'category_name.required'=>'please enter a categorie name',
                    'category_image.required' => 'A categorie picture is required',
                    'category_image.mimes' => 'Only jpeg,png,jpg images are supported',
                    'category_image.max' => 'file size should not be more than 1MB',
                ];

                #Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{
                        #handle business document
                        $Service = $request->file('category_image');
                        $Serviceextension = $Service->getClientOriginalExtension();
                        $newServiceName= time().uniqid() . '.' . $Serviceextension;
                        $storagePathService = public_path('KlinwashUploads');
                        $Service->move($storagePathService, $newServiceName);
                        
    
                        #handled the adding of cartegory and prices
                        $countCategory=$request->category_details;
                        
                         #insertin into services list
                            $userinfo=$request->user();
                            $updateList=Services_list::create([
                                'service_id'=>$request->service_id,
                                'category_name'=>'',
                                'category_image'=>"KlinwashUploads/".$newServiceName,
                                
                           ]);
                        if($updateList){

                            #json reponse
                            return response()->json([
                                'code'=>'200',
                                'row_id'=>$updateList->id,
                                'reason' => 'image added successfully',
                            ], 200);
                        }
                    }
                
                }
            catch (\Throwable$th) {
                return response(["code" => 200, "error" => $th->getMessage()]);
                #return response(["code" => 3, "error" => "an error occured"]);
            }
        
    }
    
    
    #Deleting Image
    public function DeleteImage($id){
        try{
            $deleteImage=Services_list::where('id',$id)->delete();
            return response()->json([
               'code'=>'200',
               'data'=>"Image deleted"
           ], 200);
        }
        catch (\Throwable$th) {
           #return response(["code" => 3, "error" => $th->getMessage()]);
           return response(["code" => 3, "error" => "an error occured"]);
       }
   }
   
   

    #add services categories
    public function AddServicesCategory(Request $request){
        try{
                $rules = [
                    'id'=>'required',
                    'service_id'=>'required',
                    'category_name'=>'required',
                    'category_details'=>[],

                ];

                $messages = [
                    'service_id.required'=>'please enter a services id',
                    'category_name.required'=>'please enter a categorie name',
                ];

                #Validate the request
                $validator = Validator::make($request->all(), $rules,$messages);
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                else{


                    #handled the adding of cartegory and prices
                    $countCategory=$request->category_details;
                    
                     #insertin into services list
                        $userinfo=$request->user();
                        $updateList=Services_list::where('id',$request->id)->update([
                            'category_name'=>$request->category_name,
                        ]);

                   if($updateList){
                       
                           foreach($countCategory as $categories){
                            $Addcategory=ServiceCategory::create([
                                'service_id'=>$request->id,
                                'service_name'=>$categories["category_name"],
                                'services_catergory'=>$request->category_name,
                                'services_price'=>$categories["category_price"]
                                
                            ]);
                         }

                            #json reponse
                            return response()->json([
                                'code'=>'200',
                                'reason' => 'Service Category Added successfully',
                            ], 200);
                        }
                        else{
                            return response()->json([
                                'reason' => 'an error occured',
                                'code'=>'422',
                            ], 422);
                        }
                    }
              }
            catch (\Throwable$th) {
                return response(["code" => 3, "error" => $th->getMessage()]);
                #return response(["code" => 3, "error" => "an error occured"]);
            }

    }

    #Get Services
    public function getServices(){
         try{
             $getAllSerice=servicesAdmin_Tables::with('Category')->get();
             
             return response()->json([
                'code'=>'200',
                'data'=>$getAllSerice
            ], 200);
         }
         catch (\Throwable$th) {
            #return response(["code" => 3, "error" => $th->getMessage()]);
            return response(["code" => 3, "error" => "an error occured"]);
        }
    }

    #Get Services Category by id
    public function getServicesCategoryId($id){
        try{
            $getServicesCategoryId=Services_list::with('services')
                ->select('id','service_id','category_name','category_image')
                ->where('service_id',$id)
                ->get();
            return response()->json([
               'code'=>'200',
               'data'=>$getServicesCategoryId
           ],200);
        }
        catch (\Throwable$th) {
           #return response(["code" => 3, "error" => $th->getMessage()]);
           return response(["code" => 3, "error" => "an error occured"]);
       }
   }


   public function AddServicesCategoryPrice(Request $request){
      try{
            $rules = [
                'service_id'=>'required',
                'service_name'=>'required',
                'category_details'=>[],
                

            ];
            #Validate the request
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{

                    $countCategory=$request->category_details;

                    foreach($countCategory as $categories){
                        $Addcategory=ServiceCategory::create([
                            'service_id'=>$request->service_id,
                            'service_name'=>$categories["category_name"],
                            'services_catergory'=>$request->service_name,
                            'services_price'=>$categories["category_price"]
                            
                        ]);
                    }
            
                    #json reponse
                    return response()->json([
                        'code'=>'200',
                        'reason' => 'Sub Category and prices added successfully',
                    ], 200);
                   
                }
          }
        catch (\Throwable$th) {
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }

}


    #Delete Services
    public function DeleteServiceSubCategory($id){
        try{

            $deleteServices= ServiceCategory::find($id)->delete();
  
            if($deleteServices){
                return response()->json([
                      'code'=>200,
                      'reason'=>'service deleted successfully',
                  ], 200);
             }else{
                  return response()->json([
                      'code'=>200,
                      'reason'=>'could not perform this operation',
                  ], 200);
             }
         }
          catch (\Throwable$th) {
              #return response(["code" => 3, "error" => $th->getMessage()]);
              return response(["code" => 400, "error" => "an error occured"]);
          }
    }


    #Delete Services
    public function DeleteService($id){
        try{

            $deleteServices= servicesAdmin_Tables::find($id)->delete();
  
            if($deleteServices){
                return response()->json([
                      'code'=>200,
                      'reason'=>'service deleted successfully',
                  ], 200);
             }else{
                  return response()->json([
                      'code'=>200,
                      'reason'=>'could not perform this operation',
                  ], 200);
             }
         }
          catch (\Throwable$th) {
              #return response(["code" => 3, "error" => $th->getMessage()]);
              return response(["code" => 200, "error" => "an error occured"]);
          }
    }
   

    #Get Services by id
    public function getServicesId($id){
        try{
            $getAllSericeId=servicesAdmin_Tables::where('id',$id)->get();
            return response()->json([
               'code'=>'201',
               'data'=>$getAllSericeId
           ], 200);
        }
        catch (\Throwable$th) {
           #return response(["code" => 3, "error" => $th->getMessage()]);
           return response(["code" => 3, "error" => "an error occured"]);
       }
   }

    #Edit Services
    public function EditServices(Request $request){
        try{
            $rules = [
                'service_id'=>'required',
                'service_type'=>'required',
                'services_image'=>'required|file|mimes:jpeg,png,jpg|max:1024',

            ];

            $messages = [
                'service.required'=>'service id is required',
                'service_type.required'=>'please enter a service name',
                'services_image.required' => 'A service picture is required',
                'services_image.mimes' => 'Only jpeg,png,jpg images are supported',
                'services_image.max' => 'file size should not be more than 1MB',
            ];

            #Validate the request
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{

                #handle business document
                $Service = $request->file('services_image');
                $Serviceextension = $Service->getClientOriginalExtension();
                $newServiceName= time().uniqid() . '.' . $Serviceextension;
                $storagePathService = public_path('KlinwashUploads');
                $Service->move($storagePathService, $newServiceName);

                $userinfo=$request->user();

                 #check if photo exsit
                 if($userinfo->profile_photo!==''){
                    #unlink the old photo
                    unlink(public_path($userinfo->profile_photo));
                   }

                    #update services
                    $updatePhoto=servicesAdmin_Tables::where(['id'=>$request->service_id])->update([
                        'service_type'=>$request->service_type,
                        'services_image'=>"KlinwashUploads/".$newServiceName,
                        
                    ]);
                    if($updatePhoto){

                        #json reponse
                        return response()->json([
                            'code'=>'200',
                            'reason' => 'Service updated successfully',
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
            return response(["code" => 3, "error" => $th->getMessage()]);
            #return response(["code" => 3, "error" => "an error occured"]);
        }
    }

}
