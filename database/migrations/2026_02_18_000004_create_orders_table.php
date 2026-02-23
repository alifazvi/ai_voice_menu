<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('food_name')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('size')->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->string('table_number')->nullable();
            $table->unsignedInteger('guest_count')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('instructions')->nullable();
            $table->json('items')->nullable();
            $table->dateTime('booked_at')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('restaurant_id')
                ->references('id')->on('restaurants')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('set null');

            $table->foreign('menu_id')
                ->references('id')->on('menus')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
