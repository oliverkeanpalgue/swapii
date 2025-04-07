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
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', [
                'trade_req',
                'trade_reject',
                'trade_success',
                'trade_cancel',
                'trade_confirm',
                'item_unlist',
                'user_report',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', [
                'trade_req',
                'trade_reject',
                'trade_success',
                'trade_cancel',
                'trade_confirm',
                'item_unlist',
            ])->change();
        });
    }
};
