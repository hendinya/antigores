<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->string('shopping_link', 500)->nullable()->after('camera_shape');
            $table->text('masteran')->nullable()->after('shopping_link');
        });
    }

    public function down(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->dropColumn(['shopping_link', 'masteran']);
        });
    }
};
