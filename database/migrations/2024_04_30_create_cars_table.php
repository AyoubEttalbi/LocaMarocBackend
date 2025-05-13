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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->string('brand', 50);
            $table->string('model', 50);
            $table->integer('seats');
            $table->string('gearType', 20);
            $table->integer('mileage');
            $table->float('pricePerDay');
            $table->boolean('availability');
            
            $table->string('fuelType', 20);
            $table->string('color', 20);
            $table->year('year');
            $table->string('image', 255)->nullable();
            $table->date('insuranceExpiryDate')->nullable();
            $table->date('serviceDueDate')->nullable();
            
            $table->text('features')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
}; 