<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->string('camera_shape', 100)->nullable()->after('antigores_size');
        });
    }

    public function down(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->dropColumn('camera_shape');
        });
    }
};
