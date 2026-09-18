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

        Schema::create('pay_failed_jobs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id('id');
            $table->string('uuid', 255)->collation('utf8mb4_unicode_ci');
            $table->text('connection')->collation('utf8mb4_unicode_ci');
            $table->text('queue')->collation('utf8mb4_unicode_ci');
            $table->longText('payload')->collation('utf8mb4_unicode_ci');
            $table->longText('exception')->collation('utf8mb4_unicode_ci');
            $table->timestamp('failed_at')->useCurrent();

            $table->unique('uuid', 'failed_jobs_uuid_unique');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_failed_jobs');
        Schema::enableForeignKeyConstraints();
    }
};
