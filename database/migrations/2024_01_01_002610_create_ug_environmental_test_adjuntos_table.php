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

        Schema::create('ug_environmental_test_adjuntos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_adjunto', true, false);
            $table->integer('cod_test');
            $table->text('nombre_adjunto');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_test', 'fk_ug_env_test_adj_ug_env_test_idx');
            $table->index('user_insert', 'fk_ug_env_test_adj_usu_usuarios_idx');

            $table->foreign('cod_test', 'fk_ug_env_test_adj_ug_env_test')->references('cod_test')->on('ug_environmental_test');
            $table->foreign('user_insert', 'fk_ug_env_test_adj_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_environmental_test_adjuntos');
        Schema::enableForeignKeyConstraints();
    }
};
