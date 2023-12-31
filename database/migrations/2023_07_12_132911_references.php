<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('option_for_product', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('option_id');

            $table->foreign('product_id')
                ->references('id')
                ->on('origami_product')
                ->onDelete('cascade');
            $table->foreign('option_id')
                ->references('id')
                ->on('option_for_product')
                ->onDelete('cascade');

            $table->char('value',100)->nullable();
            //$table->primary(['product_id', 'option_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
