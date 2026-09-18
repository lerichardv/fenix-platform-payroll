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

        Schema::create('ug_estados_ordenes_compra', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_estado_orden_compra', true, false);
            $table->string('estado', 45);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_ug_es_or_com_usu_usuarios_idx');

            $table->foreign('user_insert', 'fk_ug_es_or_com_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_estados_ordenes_compra');
        Schema::enableForeignKeyConstraints();
    }
};
