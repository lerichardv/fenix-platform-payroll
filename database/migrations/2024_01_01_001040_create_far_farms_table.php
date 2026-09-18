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

        Schema::create('far_farms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_farms')->autoIncrement();
            $table->integer('cod_estado')->default('0');
            $table->string('farm', 100);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent()->comment('cod_state viene de la tabla de estados para convertirse en llave compuesta/');

            $table->primary(['cod_farms', 'cod_estado']);

            $table->index('cod_estado', 'fk_far_farms_bw_inventario_estados_plantaciones1_idx');
            $table->index('user_insert', 'fk_far_farms_usu_usuarios_idx');
            $table->index('cod_farms', 'cod_farms');

            $table->foreign('cod_estado', 'fk_far_farms_bw_inventario_estados_plantaciones1')->references('cod_estado')->on('bw_inventario_estados_plantaciones');
            $table->foreign('user_insert', 'fk_far_farms_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_farms');
        Schema::enableForeignKeyConstraints();
    }
};
