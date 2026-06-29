<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'min_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('min_stock')->default(5)->after('stock');
            });
        }

        if (Schema::hasTable('product_variants') && !Schema::hasColumn('product_variants', 'min_stock')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->integer('min_stock')->default(5)->after('stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'min_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }

        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'min_stock')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }
    }
};
