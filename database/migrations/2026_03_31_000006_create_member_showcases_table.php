<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_showcases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('showcase_number');
            $table->timestamps();
            $table->unique(['user_id', 'brand_id'], 'member_showcases_user_brand_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_showcases');
    }
};
