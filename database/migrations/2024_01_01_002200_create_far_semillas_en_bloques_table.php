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

        Schema::create('far_semillas_en_bloques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_bin';

            $table->integer('cod_unificacion', true, false);
            $table->integer('cod_semilla')->default('0');
            $table->integer('cod_bloque')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->tinyInteger('completado')->default('0');

            $table->index('cod_semilla', 'fk_far_semillas_en_bloques_bw_inventario_semilla_idx');
            $table->index('cod_bloque', 'fk_far_semillas_en_bloques_far_bloque_idx');

            $table->foreign('cod_semilla', 'fk_far_semillas_en_bloques_bw_inventario_semilla')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('cod_bloque', 'fk_far_semillas_en_bloques_far_bloque')->references('cod_bloque')->on('far_bloques');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_semillas_en_bloques');
        Schema::enableForeignKeyConstraints();
    }
};
