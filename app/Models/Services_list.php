<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Services_list extends Model
{
    use HasFactory,SoftDeletes;


    protected $guarded = [];

     #get a services and price under a category
     public function services(){

        return $this->hasMany(ServiceCategory::class,'service_id')->select('id','service_id','service_name','services_catergory','services_price');
    }
}
