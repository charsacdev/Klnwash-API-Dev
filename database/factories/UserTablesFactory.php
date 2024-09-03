<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\users_Tables;

class UserTablesFactory extends Factory
{
    protected $model = users_Tables::class;

    public function definition()
    {
        return [
            'first_name'=>$this->faker->firstName(),
            'last_name'=>$this->faker->lastName(),
            'email'=>$this->faker->email(),
            'password'=>bcrypt('12345'),
            'phone'=>$this->faker->phoneNumber(),
            'state'=>$this->faker->state(),
            'lga'=>$this->faker->state(),
            'address'=>$this->faker->address(),
            'auth_code'=>$this->faker->randomNumber(4, true),
            'account_balance'=>$this->faker->randomNumber(5, true),
            'pay_api_code'=>$this->faker->randomNumber(6, true),
            'referal_balance'=>$this->faker->randomNumber(4, true),
            'referal_code'=>'KLN'.$this->faker->randomNumber(6, true),
            'profile_photo'=>$this->faker->imageUrl(640,480),
            'pin_transaction'=>$this->faker->randomNumber(4, true),
            'account_status'=>'active',
        ];
    }
}
