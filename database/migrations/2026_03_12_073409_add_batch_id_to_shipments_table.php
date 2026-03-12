<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('shipment_batch_id')
                  ->nullable()
                  ->after('generator_id')
                  ->constrained('shipment_batches')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['shipment_batch_id']);
            $table->dropColumn('shipment_batch_id');
        });
    }
};
