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
        Schema::create('inventory_items', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->text('description')->nullable();
    $table->string('category')->nullable(); // Or foreignId to an inventory_categories table
    $table->string('unit_of_measure'); // e.g., kg, pcs, ltr
    $table->decimal('current_stock', 10, 3)->default(0);
    $table->decimal('reorder_level', 10, 3)->nullable();
    $table->decimal('average_cost_price', 10, 2)->nullable(); // For COGS
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
