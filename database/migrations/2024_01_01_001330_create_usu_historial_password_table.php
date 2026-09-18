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

        Schema::create('usu_historial_password', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_historial_password', true, false);
            $table->integer('cod_usuario');
            $table->string('password_anterior', 45);
            $table->string('password_nuevo', 45);
            $table->timestamp('date_cambio')->useCurrent()->useCurrentOnUpdate();

            $table->index('cod_usuario', 'fk_hist_password_idx');

            $table->foreign('cod_usuario', 'fk_hist_password')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_historial_password');
        Schema::enableForeignKeyConstraints();
    }
};
