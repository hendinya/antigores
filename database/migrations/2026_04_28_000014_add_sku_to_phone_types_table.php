<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->string('sku', 32)->nullable()->after('id');
        });

        DB::statement("UPDATE phone_types SET sku = CONCAT('ET-', LPAD(id, 6, '0')) WHERE sku IS NULL OR sku = ''");

        Schema::table('phone_types', function (Blueprint $table) {
            $table->unique('sku');
        });
    }

    public function down(): void
    {
        Schema::table('phone_types', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};

