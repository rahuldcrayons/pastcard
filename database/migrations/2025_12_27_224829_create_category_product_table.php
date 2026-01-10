<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');  // matches products.id (bigint unsigned)
            $table->unsignedInteger('category_id');     // matches categories.id (int)
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Indexes for fast lookups
            $table->index('product_id');
            $table->index('category_id');
            $table->unique(['product_id', 'category_id']);
        });

        // Add foreign keys separately without strict constraint
        // This allows for flexibility with existing data
        Schema::table('category_product', function (Blueprint $table) {
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_product');
    }
}
