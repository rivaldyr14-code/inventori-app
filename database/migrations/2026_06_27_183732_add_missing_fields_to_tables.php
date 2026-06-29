<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('quantity');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('preferences')->nullable()->after('is_active');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('guard_name');
            $table->json('settings')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferences');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'settings']);
        });
    }
};
