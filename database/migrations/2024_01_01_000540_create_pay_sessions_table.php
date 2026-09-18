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

        Schema::create('pay_sessions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('id', 255)->collation('utf8mb4_unicode_ci')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->default(null);
            $table->string('ip_address', 45)->collation('utf8mb4_unicode_ci')->nullable()->default(null);
            $table->text('user_agent')->collation('utf8mb4_unicode_ci')->nullable();
            $table->longText('payload')->collation('utf8mb4_unicode_ci');
            $table->integer('last_activity');

            $table->index('user_id', 'sessions_user_id_index');
            $table->index('last_activity', 'sessions_last_activity_index');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_sessions');
        Schema::enableForeignKeyConstraints();
    }
};
