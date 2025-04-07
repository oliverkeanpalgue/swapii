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
        Schema::dropIfExists('verif_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('verif_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->string('id_image');
            $table->string('school');
            $table->timestamps();
        });
    }
};
