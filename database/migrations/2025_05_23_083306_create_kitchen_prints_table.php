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
        Schema::create('kitchen_prints', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    // $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('cascade'); // Optional: if printing individual items
    $table->enum('type', ['new_order', 'add_item', 'modify_item', 'cancel_item']);
    $table->text('print_content'); // Store JSON of items printed
    $table->timestamp('printed_at')->useCurrent();
    $table->foreignId('user_id')->comment('User who triggered print')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_prints');
    }
};
