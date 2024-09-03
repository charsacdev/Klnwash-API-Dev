<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class users_Tables extends Authenticatable
{
    use HasFactory,HasApiTokens,SoftDeletes;

    protected $guarded = [];

    #counting all order
    public function order_type(){
        return $this->belongsTo(orders_Tables::class,'order_tag_code','order_tag_code');
    }
    
    #order type
    public function order_quantity(){
       return $this->belongsTo(orders_Tables::class,'order_tag_code','order_tag_code')->select('order_tag_code');
    }
    
    #order price
    public function order_price(){
       return $this->belongsTo(orders_Tables::class,'order_tag_code','order_tag_code')->select('order_price');
    }
    
  
               
                   
                    
    public function orders(){

        return $this->hasMany(orders_Tables::class,'user_id')->select(
            'id',
            'user_id',
            'order_category',
            'service_id',
            'order_tag_code',
            'order_status',
            'pickup_date',
            'delivery_date',
            'pickup_time',
            'delivery_time',
            'express_delivery',
            'created_at'
            );
            #->with('order_type','order_quantity','order_price');
    }

    #total amount spent
    public function spent(){
        
        return $this->hasMany(orders_Tables::class,'user_id')->select('order_price');
    }

    

    

    protected static function newFactory(){
    return \Database\Factories\UserTablesFactory::new();
   }
}
