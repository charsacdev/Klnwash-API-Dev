<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class orders_Tables extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];
    
    #all order type
    public function order_type(){
         return $this->hasMany(orders_Tables::class,'order_tag_code','order_tag_code');
    }
    
    
    #all order quantity
    public function order_quantity(){
        return $this->hasMany(orders_Tables::class,'order_tag_code','order_tag_code');
    }
    
    #all order quantity
    public function order_price(){
        return $this->hasMany(orders_Tables::class,'order_tag_code','order_tag_code')->select('order_price');
    }


    #counting all order
    public function orders(){

        return $this->belongsTo(orders_Tables::class);
    }

    #total amount spent
    public function spent(){
        
        return $this->belongsTo(orders_Tables::class)->select('order_price');
    }

    #getting user names
    public function user(){
        
        return $this->belongsTo(users_Tables::class,'user_id');
    }
    
    
    #total prices
    public function totalPrice(){
       return $this->belongsTo(orders_Tables::class,'order_tag_code','order_tag_code')->select('order_price');
    }
    
    public function totalOrders(){
        return $this->hasMany(orders_Tables::class,'order_tag_code','order_tag_code');
    }
    
    

    public function userorder(){
        
        return $this->belongsTo(users_Tables::class,'user_id');
    }
}
