<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 32)->nullable()->after('id');
        });

        $used = DB::table('phone_types')
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->pluck('sku')
            ->flip()
            ->all();

        $ids = DB::table('products')->orderBy('id')->pluck('id');
        foreach ($ids as $id) {
            $attempt = 0;
            while (true) {
                $attempt++;
                $candidate = (string) now()->timestamp.random_int(100, 999);
                if (isset($used[$candidate])) {
                    if ($attempt % 200 === 0) {
                        usleep(20000);
                    }

                    continue;
                }

                $exists = DB::table('products')->where('sku', $candidate)->exists();
                if (! $exists) {
                    $used[$candidate] = true;
                    DB::table('products')->where('id', $id)->update(['sku' => $candidate]);
                    break;
                }

                if ($attempt % 200 === 0) {
                    usleep(20000);
                }
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('sku');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};

