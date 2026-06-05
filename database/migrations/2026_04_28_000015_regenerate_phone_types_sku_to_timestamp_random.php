<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('phone_types')->update(['sku' => null]);

        $used = [];
        $ids = DB::table('phone_types')->orderBy('id')->pluck('id');
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

                $exists = DB::table('phone_types')->where('sku', $candidate)->exists();
                if (! $exists) {
                    $used[$candidate] = true;
                    DB::table('phone_types')->where('id', $id)->update(['sku' => $candidate]);
                    break;
                }

                if ($attempt % 200 === 0) {
                    usleep(20000);
                }
            }
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE phone_types SET sku = CONCAT('ET-', LPAD(id, 6, '0'))");
    }
};

