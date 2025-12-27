<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add composite index for common product queries
        Schema::table('products', function (Blueprint $table) {
            // Check if indexes already exist before adding
            $existingIndexes = collect(DB::select("SHOW INDEX FROM products"))->pluck('Key_name')->unique();

            // Index for published products with price (homepage queries)
            if (!$existingIndexes->contains('products_published_price_idx')) {
                $table->index(['published', 'unit_price'], 'products_published_price_idx');
            }

            // Index for category filtering
            if (!$existingIndexes->contains('products_category_published_idx')) {
                $table->index(['category_id', 'published'], 'products_category_published_idx');
            }

            // Index for today's deal
            if (!$existingIndexes->contains('products_todays_deal_idx')) {
                $table->index(['todays_deal', 'published'], 'products_todays_deal_idx');
            }
        });

        // Add index on categories for hierarchy queries
        Schema::table('categories', function (Blueprint $table) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM categories"))->pluck('Key_name')->unique();

            if (!$existingIndexes->contains('categories_parent_level_idx')) {
                $table->index(['parent_id', 'level'], 'categories_parent_level_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_published_price_idx');
            $table->dropIndex('products_category_published_idx');
            $table->dropIndex('products_todays_deal_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_level_idx');
        });
    }
}
