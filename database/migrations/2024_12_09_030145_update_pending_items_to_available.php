<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('items')
            ->where('status', 'pending')
            ->update(['status' => 'available']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('items')
            ->where('status', 'available')
            ->update(['status' => 'pending']);
    }
};
