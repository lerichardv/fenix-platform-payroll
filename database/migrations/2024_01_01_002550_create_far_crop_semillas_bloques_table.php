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

        Schema::create('far_crop_semillas_bloques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_bin';

            $table->integer('cod_semilla_bloque', true, false);
            $table->integer('cod_bloque_implementado');
            $table->integer('cod_semilla');
            $table->integer('cod_unificacion');
            $table->tinyInteger('completada')->default('0');
            $table->integer('cod_plantacion');
            $table->integer('cod_trasplante');
            $table->integer('cod_temporada')->default('1');
            $table->decimal('acres_usados', 3, 2)->default('0.00');
            $table->decimal('porcentaje_acre_usado', 5, 2)->default('0.00');
            $table->timestamp('fecha_plantacion')->useCurrent();
            $table->tinyInteger('completada_en_app')->default('0');
            $table->tinyInteger('en_proceso')->default('0');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_semilla_bloque', 'cod_semilla_bloque_UNIQUE');

            $table->index('cod_bloque_implementado', 'far_crop_semillas_bloques_far_crop_bloques_implementados_idx');
            $table->index('cod_semilla', 'far_crop_semillas_bloques_bw_inventario_semilla_idx');
            $table->index('cod_unificacion', 'far_crop_semillas_bloques_far_semillas_en_bloques_idx');
            $table->index('cod_semilla', 'idx_cod_semilla');
            $table->index('cod_plantacion', 'idx_cod_plantacion');
            $table->index('cod_semilla_bloque', 'idx_far_crop_semillas_bloques_cod_semilla_bloque');
            $table->index('cod_semilla', 'idx_far_crop_semillas_bloques_cod_semilla');
            $table->index('cod_bloque_implementado', 'idx_far_crop_semillas_bloques_cod_bloque_implementado');

            $table->foreign('cod_semilla', 'far_crop_semillas_bloques_bw_inventario_semilla')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('cod_bloque_implementado', 'far_crop_semillas_bloques_far_crop_bloques_implementados')->references('cod_bloque_implementado')->on('far_crop_bloques_implementados');
            $table->foreign('cod_unificacion', 'far_crop_semillas_bloques_far_semillas_en_bloques')->references('cod_unificacion')->on('far_semillas_en_bloques');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_crop_semillas_bloques');
        Schema::enableForeignKeyConstraints();
    }
};
