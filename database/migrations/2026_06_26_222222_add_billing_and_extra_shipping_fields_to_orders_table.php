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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('alternate_phone')->nullable()->after('phone');
            $table->string('address_type')->default('work')->after('alternate_phone'); // office, work, other
            $table->string('landmark')->nullable()->after('address_type');
            
            // Billing fields
            $table->boolean('shipping_and_billing_same')->default(true)->after('landmark');
            $table->string('billing_first_name')->nullable()->after('shipping_and_billing_same');
            $table->string('billing_last_name')->nullable()->after('billing_first_name');
            $table->string('billing_address')->nullable()->after('billing_last_name');
            $table->string('billing_city')->nullable()->after('billing_address');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_zip')->nullable()->after('billing_state');
            $table->string('billing_country')->nullable()->after('billing_zip');
            $table->string('billing_phone')->nullable()->after('billing_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'alternate_phone',
                'address_type',
                'landmark',
                'shipping_and_billing_same',
                'billing_first_name',
                'billing_last_name',
                'billing_address',
                'billing_city',
                'billing_state',
                'billing_zip',
                'billing_country',
                'billing_phone'
            ]);
        });
    }
};
