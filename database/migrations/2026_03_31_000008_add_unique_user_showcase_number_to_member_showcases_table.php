<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_showcases', function (Blueprint $table) {
            $table->unique(['user_id', 'showcase_number'], 'member_showcases_user_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('member_showcases', function (Blueprint $table) {
            $table->dropUnique('member_showcases_user_number_unique');
        });
    }
};
