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

        Schema::create('usu_tokens_sesion', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_token', true, false);
            $table->string('token_sesion', 255);
            $table->integer('cod_usuario');
            $table->dateTime('fecha_expiracion');
            $table->timestamp('date_insert')->useCurrent();
            $table->timestamp('date_update')->nullable()->useCurrent()->useCurrentOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_tokens_sesion');
        Schema::enableForeignKeyConstraints();
    }
};
