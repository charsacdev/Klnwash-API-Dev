<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatisticsInfo extends Controller
{
    #================Super Admin Statistics======================#

    #user and orders statistics
    public function AllOrders(){

    }

    public function CompletedOrdersTotal(){

    }

    public function RecievedOrdersTotal(){

    }

    public function PendingOrdersTotal(){

    }

    public function UnconfirmedOrdersTotal(){

    }
    
    public function GetAllUser(){

    }

    public function GetAllTransactionTotal(){

    }

    #income statistics
    public function getRevenueCompletedSum(){

    }

    public function getRevenuePendingSum(){
        
    }

    public function getRevenueUnconfiredSum(){
        
    }




     #================Sub Admin Statistics======================#

    #user and orders statistics
    public function SubAdmin_AllOrders(){

    }

    public function SubAdmin_CompletedOrdersTotal(){

    }

    public function SubAdmin_RecievedOrdersTotal(){

    }

    public function SubAdmin_PendingOrdersTotal(){

    }

    public function SubAdmin_UnconfirmedOrdersTotal(){

    }
    
    public function SubAdmin_GetAllUser(){

    }

    public function SubAdmin_GetAllTransactionTotal(){

    }

    #income statistics
    public function SubAdmin_getRevenueCompletedSum(){

    }

    public function SubAdmin_getRevenuePendingSum(){
        
    }

    public function SubAdmin_getRevenueUnconfiredSum(){
        
    }
}
