<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('MEMB_INFO', function (Blueprint $table) {
            $table->rememberToken()->after('mail_chek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schedule::table('MEMB_INFO', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
