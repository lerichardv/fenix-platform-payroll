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

        Schema::create('pay_personal_access_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id('id');
            $table->string('tokenable_type', 255)->collation('utf8mb4_unicode_ci');
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name', 255)->collation('utf8mb4_unicode_ci');
            $table->string('token', 64)->collation('utf8mb4_unicode_ci');
            $table->text('abilities')->collation('utf8mb4_unicode_ci')->nullable();
            $table->timestamp('last_used_at')->nullable()->default(null);
            $table->timestamp('expires_at')->nullable()->default(null);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);

            $table->unique('token', 'personal_access_tokens_token_unique');

            $table->index(['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_personal_access_tokens');
        Schema::enableForeignKeyConstraints();
    }
};
