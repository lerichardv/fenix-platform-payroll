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

        Schema::create('far_crop_bloques_implementados', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_bin';

            $table->integer('cod_bloque_implementado', true, false);
            $table->integer('cod_bloque')->default('0');
            $table->integer('cod_field');
            $table->integer('cod_farm');
            $table->tinyInteger('libre')->default('1');
            $table->tinyInteger('reseteado')->default('0');
            $table->tinyInteger('multiple_semillas')->default('0');
            $table->decimal('ini_acres', 3, 2)->default('0.00');
            $table->decimal('use_acres', 3, 2)->default('0.00');
            $table->decimal('acres_disponibles', 3, 2)->default('0.00');
            $table->decimal('porcentaje_acre_usado', 5, 2)->default('0.00');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_bloque_implementado', 'cod_bloque_implementado_UNIQUE');
            $table->unique('cod_bloque', 'cod_bloque_UNIQUE');

            $table->index('cod_bloque', 'fk_far_crop_bloques_implementados_far_crop_bloques_idx');
            $table->index('cod_field', 'fk_far_crop_bloques_implementados_far_fields_idx');
            $table->index('cod_farm', 'fk_far_crop_bloques_implementados_far_farms_idx');
            $table->index('user_insert', 'fk_far_crop_bloques_implementados_usu_usuarios_idx');
            $table->index('cod_farm', 'idx_cod_farm');
            $table->index('cod_bloque_implementado', 'idx_far_crop_bloques_implementados_cod_bloque_implementado');
            $table->index('cod_bloque', 'idx_far_crop_bloques_implementados_cod_bloque');

            $table->foreign('cod_bloque', 'fk_far_crop_bloques_implementados_far_crop_bloques')->references('cod_bloque')->on('far_bloques');
            $table->foreign('cod_farm', 'fk_far_crop_bloques_implementados_far_farms')->references('cod_farms')->on('far_farms');
            $table->foreign('cod_field', 'fk_far_crop_bloques_implementados_far_fields')->references('cod_field')->on('far_fields');
            $table->foreign('user_insert', 'fk_far_crop_bloques_implementados_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_crop_bloques_implementados');
        Schema::enableForeignKeyConstraints();
    }
};
