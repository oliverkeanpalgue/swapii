<?php

use App\Models\TradeRequest;
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
        Schema::create('trade_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TradeRequest::class)->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_cancellations');
    }
};
