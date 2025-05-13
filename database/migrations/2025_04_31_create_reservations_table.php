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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->date('startDate');
            $table->date('endDate');
            $table->float('totalPrice');
            $table->string('status', 20);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('statusUpdatedBy')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vehicle_id')->constrained('cars')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->boolean('selectDriver')->default(false);
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
}; 