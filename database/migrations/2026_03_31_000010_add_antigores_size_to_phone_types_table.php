<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->string('antigores_size', 100)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->dropColumn('antigores_size');
        });
    }
};
