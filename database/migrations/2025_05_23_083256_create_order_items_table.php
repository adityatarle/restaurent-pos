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
        Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
    $table->integer('quantity');
    $table->decimal('price_at_order', 8, 2); // Price when item was added
    $table->enum('status', ['pending', 'sent_to_kitchen', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
    $table->text('item_notes')->nullable(); // e.g., "No onions"
    $table->boolean('printed_to_kitchen')->default(false); // To track if this specific item/batch was printed
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
