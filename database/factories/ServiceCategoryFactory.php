<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ServiceCategory;

class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition()
    {
        return [
            'service_type'=>'wash and iron',
            'services_catergory'=>'Men',
            'services_price'=>$this->faker->randomNumber(4, true)
        ];
    }
}
