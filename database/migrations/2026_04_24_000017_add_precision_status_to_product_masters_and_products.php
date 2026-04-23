<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_BELUM_DITES = 'belum_dites';

    public function up(): void
    {
        Schema::table('product_masters', function (Blueprint $table) {
            $table->string('precision_status', 32)
                ->default(self::STATUS_BELUM_DITES)
                ->after('is_visible_for_affiliator');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('precision_status', 32)
                ->default(self::STATUS_BELUM_DITES)
                ->after('is_visible_for_affiliator');
        });

        DB::table('products')
            ->whereNotNull('product_master_id')
            ->update(['precision_status' => self::STATUS_BELUM_DITES]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('precision_status');
        });

        Schema::table('product_masters', function (Blueprint $table) {
            $table->dropColumn('precision_status');
        });
    }
};
