<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('service_charge_amount', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('tip_amount', 10, 2)->default(0)->after('service_charge_amount');
            $table->decimal('final_total', 10, 2)->nullable()->after('tip_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount','tax_amount','service_charge_amount','tip_amount','final_total']);
        });
    }
};