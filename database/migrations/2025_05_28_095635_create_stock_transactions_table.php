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
       Schema::create('stock_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
    $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
    $table->enum('type', ['purchase', 'wastage', 'sale_deduction', 'manual_adjustment_in', 'manual_adjustment_out', 'opening_stock']);
    $table->decimal('quantity', 10, 3); // Positive for IN, can be negative for OUT or specific logic
    $table->decimal('cost_price_at_transaction', 10, 2)->nullable(); // For purchases
    $table->text('notes')->nullable();
    $table->timestamp('transaction_date')->useCurrent();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User who performed it
    $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('set null'); // For sale_deduction
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
