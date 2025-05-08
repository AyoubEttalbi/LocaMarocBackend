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
            $table->string('category');
            $table->string('brand');
            $table->string('model');
            $table->integer('seats');
            $table->string('gearType');
            $table->integer('mileage');
            $table->float('pricePerDay');
            $table->boolean('availability');
            
            $table->string('fuelType');
            $table->string('color');
            $table->year('year');
            $table->string('image')->nullable();
            $table->date('insuranceExpiryDate')->nullable();
            $table->date('serviceDueDate')->nullable();
            
            $table->json('features')->nullable();
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