<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('name')->constrained()->cascadeOnUpdate()->restrictOnDelete();
        });

        $defaultCategoryId = DB::table('categories')->orderBy('id')->value('id');
        if ($defaultCategoryId) {
            DB::table('brands')->whereNull('category_id')->update(['category_id' => $defaultCategoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
