<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notification_Table;

class Notification_TableFactory extends Factory
{
    
    public function definition()
    {
        return [
            'notify_id'=>$this->faker->randomDigitNot(2,true),
            'notify_type'=>'',
            'notify_title'=>$this->faker->words(5,true),
            'notify_body'=>$this->faker->paragraph(),
        ];
    }
}
