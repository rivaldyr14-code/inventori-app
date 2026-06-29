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
        Schema::create('products', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('category_id', 36)->nullable(false);
            $table->string('sku', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('current_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('extra_data')->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories');

            $table->index('category_id', 'idx_products_category');
            $table->index('sku', 'idx_products_sku');
            $table->index('name', 'idx_products_name');
            $table->index('is_active', 'idx_products_active');
            $table->index('deleted_at', 'idx_products_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
