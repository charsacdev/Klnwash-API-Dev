<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referal_Table extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ReferalDetails()
    {
        return $this->belongsTo(users_Tables::class, 'refered_id')->select('id','first_name','last_name');
    }
}
