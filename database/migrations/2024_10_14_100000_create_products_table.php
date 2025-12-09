<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('added_by')->default('admin');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('unit_price', 20, 2)->default(0);
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_link')->nullable();
            $table->string('videos')->nullable();
            $table->text('colors')->nullable();
            $table->text('choice_options')->nullable();
            $table->text('variations')->nullable();
            $table->text('photos')->nullable();
            $table->string('thumbnail_img')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->tinyInteger('published')->default(0);
            $table->tinyInteger('approved')->default(1);
            $table->tinyInteger('featured')->default(0);
            $table->tinyInteger('todays_deal')->default(0);
            $table->tinyInteger('digital')->default(0);
            $table->tinyInteger('auction_product')->default(0);
            $table->tinyInteger('cash_on_delivery')->default(1);
            $table->tinyInteger('est_shipping_days')->nullable();
            $table->tinyInteger('is_quantity_multiplied')->default(0);
            $table->string('pdf')->nullable();
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->integer('num_of_sale')->default(0);
            $table->decimal('rating', 8, 2)->default(0);
            $table->string('barcode')->nullable();
            $table->tinyInteger('refundable')->default(1);
            $table->string('sku')->nullable();
            $table->text('tags')->nullable();
            $table->string('external_link')->nullable();
            $table->string('external_link_btn')->nullable();
            $table->decimal('wholesale_price', 20, 2)->default(0);
            $table->integer('min_qty')->default(1);
            $table->decimal('discount', 20, 2)->default(0);
            $table->string('discount_type')->nullable();
            $table->date('discount_start_date')->nullable();
            $table->date('discount_end_date')->nullable();
            $table->integer('current_stock')->default(0);
            $table->string('stock_visibility_state')->default('quantity');
            $table->tinyInteger('low_stock_quantity')->default(1);
            $table->decimal('tax', 20, 2)->default(0);
            $table->string('tax_type')->default('percent');
            $table->decimal('shipping_fee', 8, 2)->default(0);
            $table->tinyInteger('condition_original')->default(0);
            $table->tinyInteger('condition_reprint')->default(0);
            $table->timestamps();

            // Explicit short index names to avoid MySQL 64-character identifier limit
            $table->index(
                ['published', 'approved', 'todays_deal', 'auction_product', 'added_by'],
                'prod_pub_app_deal_auc_added_idx'
            );
            $table->index(
                ['user_id', 'category_id', 'brand_id'],
                'prod_user_cat_brand_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
