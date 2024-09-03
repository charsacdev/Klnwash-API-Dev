<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function services(){

       return $this->hasMany(ServiceCategory::class,'id')->select('id','service_id','service_name','services_catergory','services_price');
    }

    public function Category(){
        return $this->hasMany(Services_list::class,'service_id')->select('id','service_id','category_name','category_image')->with('services'); 
    }

    #hanldes factories
    protected static function newFactory(){
        return \Database\Factories\ServiceCategoryFactory::new();
    }
}
