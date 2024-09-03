<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\servicesAdmin_Tables;

class AdminServiceTableFactory extends Factory
{
    protected $model = servicesAdmin_Tables::class;

    public function definition()
    {
        return [
            'service_type'=>'wash and fold',
            'services_image'=>$this->faker->imageUrl(640,480),
        ];
    }
}
