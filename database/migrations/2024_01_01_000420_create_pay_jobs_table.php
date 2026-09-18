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

        Schema::create('pay_jobs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id('id');
            $table->string('queue', 255)->collation('utf8mb4_unicode_ci');
            $table->longText('payload')->collation('utf8mb4_unicode_ci');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable()->default(null);
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');

            $table->index('queue', 'jobs_queue_index');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_jobs');
        Schema::enableForeignKeyConstraints();
    }
};
