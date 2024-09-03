<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users__tables', function (Blueprint $table) {
            $table->id();
            //basic user information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('state');
            $table->string('lga');
            $table->text('address');
            $table->string('auth_code');
            $table->string('account_balance');
            $table->string('pay_api_code');
            $table->string('referal_balance');
            $table->string('referal_code');
            $table->string('profile_photo');
            $table->string('pin_transaction');
            $table->string('account_status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users__tables');
    }
}
