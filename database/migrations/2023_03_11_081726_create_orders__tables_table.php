<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders__tables', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('order_category');
            $table->string('service_id');
            $table->string('order_type');
            $table->string('order_quantity');
            $table->string('order_price');
            $table->string('order_tag_code');
            $table->string('order_status');
            $table->string('pickup_date');
            $table->string('delivery_date');
            $table->string('order_date');
            $table->string('checkout_address');
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
        Schema::dropIfExists('orders__tables');
    }
}
