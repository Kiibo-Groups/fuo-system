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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->onDelete('cascade');
            $table->decimal('start_price', 12, 2);
            $table->decimal('current_price', 12, 2);
            $table->decimal('min_increment', 12, 2)->default(100);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('payment_deadline')->nullable();
            $table->enum('status', ['pending', 'active', 'finished', 'cancelled'])->default('pending');
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
