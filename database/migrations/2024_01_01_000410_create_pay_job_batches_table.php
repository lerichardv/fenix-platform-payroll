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
        Schema::disableForeignKeyConstraints();

        Schema::create('pay_job_batches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('id', 255)->collation('utf8mb4_unicode_ci')->primary();
            $table->string('name', 255)->collation('utf8mb4_unicode_ci');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids')->collation('utf8mb4_unicode_ci');
            $table->mediumText('options')->collation('utf8mb4_unicode_ci')->nullable();
            $table->integer('cancelled_at')->nullable()->default(null);
            $table->integer('created_at');
            $table->integer('finished_at')->nullable()->default(null);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_job_batches');
        Schema::enableForeignKeyConstraints();
    }
};
