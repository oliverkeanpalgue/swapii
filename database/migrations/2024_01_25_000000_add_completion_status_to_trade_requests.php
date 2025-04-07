<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trade_requests')) {
            return;
        }

        Schema::table('trade_requests', function (Blueprint $table) {
            $table->boolean('requestor_completed')->default(false);
            $table->boolean('owner_completed')->default(false);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trade_requests')) {
            return;
        }

        Schema::table('trade_requests', function (Blueprint $table) {
            $table->dropColumn(['requestor_completed', 'owner_completed']);
        });
    }
};
