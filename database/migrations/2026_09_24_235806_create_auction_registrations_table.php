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
        Schema::create('auction_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, released, captured
            $table->string('payment_id')->nullable(); // MercadoPago payment ID
            $table->decimal('hold_amount', 12, 2)->default(0); // The amount retained
            $table->decimal('max_bid', 12, 2)->nullable(); // For Proxy Bidding
            $table->timestamps();
            
            $table->unique(['auction_id', 'user_id']); // One registration per user per auction
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_registrations');
    }
};
