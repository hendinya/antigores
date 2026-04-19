<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('brand_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->text('product_note')->nullable();
            $table->boolean('is_visible_for_affiliator')->default(false);
            $table->timestamps();
            $table->index(['name', 'brand_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_master_id')->nullable()->after('id');
            $table->index('product_master_id', 'products_product_master_id_index');
        });

        $masterIdByKey = [];
        $products = DB::table('products')
            ->select('id', 'name', 'brand_id', 'product_note', 'is_visible_for_affiliator')
            ->orderBy('id')
            ->get();

        foreach ($products as $product) {
            $name = trim((string) $product->name);
            $productNote = trim((string) ($product->product_note ?? ''));
            $key = mb_strtolower($name).'|'.$product->brand_id.'|'.mb_strtolower($productNote);

            if (! isset($masterIdByKey[$key])) {
                $masterId = DB::table('product_masters')->insertGetId([
                    'name' => $name,
                    'brand_id' => $product->brand_id,
                    'product_note' => $productNote !== '' ? $productNote : null,
                    'is_visible_for_affiliator' => (bool) $product->is_visible_for_affiliator,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $masterIdByKey[$key] = $masterId;
            } else {
                $masterId = $masterIdByKey[$key];
                if ((bool) $product->is_visible_for_affiliator) {
                    DB::table('product_masters')
                        ->where('id', $masterId)
                        ->update(['is_visible_for_affiliator' => true, 'updated_at' => now()]);
                }
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update(['product_master_id' => $masterId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['product_master_id', 'category_id'], 'products_master_category_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_master_category_unique');
            $table->dropIndex('products_product_master_id_index');
            $table->dropColumn('product_master_id');
        });

        Schema::dropIfExists('product_masters');
    }
};
