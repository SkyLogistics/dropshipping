<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->char('vendor', 20)->nullable();
            $table->char('vendorCode', 20)->nullable();
            $table->json('options')->nullable();
            $table->text('imageUrl')->nullable();
            $table->integer('quantityInStock')->default(0);
            $table->text('keywords')->nullable();
            $table->text('keywords_ua')->nullable();
            $table->tinyInteger('active')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_fields');
    }
};
