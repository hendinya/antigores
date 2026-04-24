<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lcd_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('lcd_group_product_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lcd_group_id')->constrained('lcd_groups')->cascadeOnDelete();
            $table->foreignId('product_master_id')->constrained('product_masters')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lcd_group_id', 'product_master_id'], 'lcd_group_master_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lcd_group_product_master');
        Schema::dropIfExists('lcd_groups');
    }
};
