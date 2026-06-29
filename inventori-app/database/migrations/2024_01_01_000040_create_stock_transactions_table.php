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
            $table->char('id', 36)->primary();
            $table->char('product_id', 36)->nullable(false);
            $table->char('created_by', 36)->nullable(false);
            $table->string('transaction_number', 50)->unique();
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index('product_id', 'idx_txn_product');
            $table->index('type', 'idx_txn_type');
            $table->index('transaction_number', 'idx_txn_number');
            $table->index('created_at', 'idx_txn_created_at');
            $table->index('deleted_at', 'idx_txn_deleted');
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
