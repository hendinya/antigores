<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateMasterIds = DB::table('lcd_group_product_master')
            ->select('product_master_id')
            ->groupBy('product_master_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('product_master_id');

        foreach ($duplicateMasterIds as $masterId) {
            $keptRowId = DB::table('lcd_group_product_master')
                ->where('product_master_id', $masterId)
                ->orderBy('id')
                ->value('id');

            DB::table('lcd_group_product_master')
                ->where('product_master_id', $masterId)
                ->where('id', '!=', $keptRowId)
                ->delete();
        }

        Schema::table('lcd_group_product_master', function (Blueprint $table) {
            $table->unique('product_master_id', 'lcd_group_product_master_single_group_unique');
        });
    }

    public function down(): void
    {
        Schema::table('lcd_group_product_master', function (Blueprint $table) {
            $table->dropUnique('lcd_group_product_master_single_group_unique');
        });
    }
};
