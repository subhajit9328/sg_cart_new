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
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'session_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('session_id')->nullable()->index()->after('customer_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'session_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('session_id');
            });
        }
    }
};
