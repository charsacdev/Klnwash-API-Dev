<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\users_Tables;
use App\Models\servicesAdmin_Tables;
use App\Models\ServiceCategory;
use App\Models\Notification_Table;

class DatabaseSeeder extends Seeder
{
   
    public function run()
    {
        #users_Tables::factory(100)->create();
        #servicesAdmin_Tables::factory(6)->create();
        #ServiceCategory::factory(15)->create();
        Notification_Table::factory(20)->create();
    }
}
