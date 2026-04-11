<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_name_category_unique');
            $table->unique(['name', 'category_id', 'brand_id'], 'products_name_category_brand_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_name_category_brand_unique');
            $table->unique(['name', 'category_id'], 'products_name_category_unique');
        });
    }
};
