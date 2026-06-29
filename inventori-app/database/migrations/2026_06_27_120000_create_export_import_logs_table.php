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
        Schema::create('export_import_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('user_id', 36);
            $table->string('entity', 100);
            $table->enum('job_type', ['export', 'import']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('selected_fields')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->integer('total_rows')->nullable();
            $table->integer('success_rows')->nullable();
            $table->integer('failed_rows')->nullable();
            $table->text('error_log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->index('user_id', 'idx_eil_user');
            $table->index('status', 'idx_eil_status');
            $table->index('entity', 'idx_eil_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_import_logs');
    }
};
