<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('course')->nullable()->after('quantity');
            $table->json('modifiers')->nullable()->after('item_notes');
            $table->timestamp('fired_at')->nullable()->after('printed_to_kitchen');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['course', 'modifiers', 'fired_at']);
        });
    }
};