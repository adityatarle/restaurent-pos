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
        Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('restaurant_table_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->comment('Waiter who took the order')->constrained('users')->onDelete('cascade');
    $table->integer('customer_count');
    $table->enum('status', ['pending', 'preparing', 'served', 'paid', 'cancelled'])->default('pending');
    $table->decimal('total_amount', 10, 2)->default(0.00);
    $table->text('notes')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
