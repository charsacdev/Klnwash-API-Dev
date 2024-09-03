<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;

    protected $guarded = [];

    #counting all order
    public function reviews(){
        return $this->hasMany(Reviews::class,'user_state','user_state')->select('review_rating');
    }

    public function reviewsCount(){
        return $this->hasMany(Reviews::class,'user_state','user_state')->select('review_rating');
    }
}
