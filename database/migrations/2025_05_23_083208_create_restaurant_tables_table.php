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
       Schema::create('restaurant_tables', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // e.g., "Table 1", "A5", "Patio 2"
    $table->integer('capacity');
    $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
    $table->string('visual_coordinates')->nullable(); // For floor plan (e.g., "x:10,y:20") - Optional
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
