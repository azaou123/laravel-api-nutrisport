<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_view_orders')->default(false)->after('type');
            $table->boolean('can_create_product')->default(false)->after('can_view_orders');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_view_orders', 'can_create_product']);
        });
    }
};