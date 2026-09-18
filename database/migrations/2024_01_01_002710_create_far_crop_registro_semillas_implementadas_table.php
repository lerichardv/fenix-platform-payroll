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

        Schema::create('far_crop_registro_semillas_implementadas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_bin';

            $table->integer('cod_registro_semilla_implementada', true, false);
            $table->integer('cod_semilla_bloque');
            $table->integer('cod_bloque_implementado');
            $table->integer('cod_inventario');
            $table->integer('cod_unificacion');
            $table->integer('cod_plantacion');
            $table->integer('cod_trasplante');
            $table->integer('cod_bloque');
            $table->integer('cod_field');
            $table->integer('cod_farm');
            $table->integer('cod_temporada')->default('1');
            $table->decimal('acres_usados', 3, 2)->nullable()->default(null);
            $table->decimal('porcentaje_acre_usado_en_semilla', 5, 2)->nullable()->default(null);
            $table->timestamp('fecha_plantacion')->nullable()->useCurrent();
            $table->integer('user_insert_semilla_asociada');
            $table->string('usuario_registro_semilla', 45)->collation('latin1_bin')->nullable()->default(null);
            $table->timestamp('date_insert_semilla_asociada')->nullable()->default(null);
            $table->decimal('ini_acres', 3, 2)->nullable()->default(null);
            $table->decimal('use_acres', 3, 2)->nullable()->default(null);
            $table->decimal('acres_disponibles', 3, 2)->nullable()->default(null);
            $table->decimal('porcentaje_acre_usado', 5, 2)->nullable()->default(null);
            $table->integer('block_creator_user')->nullable()->default(null);
            $table->timestamp('block_creation_date')->nullable()->default(null);
            $table->string('nombre_semilla', 100)->collation('latin1_bin')->nullable()->default(null);
            $table->string('abreviatura_semilla', 10)->collation('latin1_bin')->nullable()->default(null);
            $table->string('codigo_semilla', 45)->collation('latin1_bin')->nullable()->default(null);
            $table->string('numero_orden_plantacion', 68)->collation('latin1_bin')->nullable()->default(null);
            $table->integer('edad_semilla_en_plantacion')->nullable()->default(null);
            $table->timestamp('fecha_inicial_plantacion')->nullable()->default(null);
            $table->timestamp('fecha_final_plantacion')->nullable()->default(null);
            $table->timestamp('fecha_de_entrega_plantacion')->nullable()->default(null);
            $table->string('numero_ticket', 100)->collation('latin1_bin')->nullable()->default(null);
            $table->timestamp('fecha_entrega_trasplante')->nullable()->default(null);
            $table->timestamp('fecha_recibo_trasplante')->nullable()->default(null);
            $table->string('nombre_bloque_usado', 145)->collation('latin1_bin')->nullable()->default(null);
            $table->integer('user_insert')->default('1');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_registro_semilla_implementada', 'cod_registro_semilla_implementada_UNIQUE');

            $table->index('cod_semilla_bloque', 'fk_far_crop_registro_semillas_implementadas_far_crop_semill_idx');
            $table->index('cod_bloque_implementado', 'fk_far_c_regis_semi_impleme_far_crop_bloques_implementados_idx');
            $table->index('cod_inventario', 'fk_far_c_regis_semi_impleme_bw_inventario_semilla_idx');
            $table->index('cod_unificacion', 'fk_far_c_regis_semi_impleme_far_semillas_en_bloques_idx');
            $table->index('cod_plantacion', 'fk_far_c_regis_semi_impleme_bw_inventario_plantaciones_idx');
            $table->index('cod_trasplante', 'fk_far_c_regis_semi_impleme_bw_inventario_trasplantes_idx');
            $table->index('cod_bloque', 'fk_far_c_regis_semi_impleme_far_bloques_idx');
            $table->index('cod_field', 'fk_far_c_regis_semi_impleme_far_fields_idx');
            $table->index('cod_farm', 'fk_far_c_regis_semi_impleme_far_farms_idx');
            $table->index('cod_temporada', 'fk_far_c_regis_semi_impleme_far_temporadas_idx');
            $table->index('user_insert_semilla_asociada', 'fk_far_c_regis_semi_impleme_usu_usuarios_idx');
            $table->index('user_insert', 'fk_far_c_regis_semi_impleme_usu_usuarios_insert_idx');

            $table->foreign('cod_plantacion', 'fk_far_c_regis_semi_impleme_bw_inventario_plantaciones')->references('cod_plantacion')->on('bw_inventario_plantaciones');
            $table->foreign('cod_inventario', 'fk_far_c_regis_semi_impleme_bw_inventario_semilla')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('cod_trasplante', 'fk_far_c_regis_semi_impleme_bw_inventario_trasplantes')->references('cod_trasplante')->on('bw_inventario_trasplantes');
            $table->foreign('cod_bloque', 'fk_far_c_regis_semi_impleme_far_bloques')->references('cod_bloque')->on('far_bloques');
            $table->foreign('cod_bloque_implementado', 'fk_far_c_regis_semi_impleme_far_crop_bloques_implementados')->references('cod_bloque_implementado')->on('far_crop_bloques_implementados');
            $table->foreign('cod_farm', 'fk_far_c_regis_semi_impleme_far_farms')->references('cod_farms')->on('far_farms');
            $table->foreign('cod_field', 'fk_far_c_regis_semi_impleme_far_fields')->references('cod_field')->on('far_fields');
            $table->foreign('cod_unificacion', 'fk_far_c_regis_semi_impleme_far_semillas_en_bloques')->references('cod_unificacion')->on('far_semillas_en_bloques');
            $table->foreign('cod_temporada', 'fk_far_c_regis_semi_impleme_far_temporadas')->references('cod_temporada')->on('far_temporadas');
            $table->foreign('user_insert_semilla_asociada', 'fk_far_c_regis_semi_impleme_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_insert', 'fk_far_c_regis_semi_impleme_usu_usuarios_insert')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_semilla_bloque', 'fk_far_crop_registro_semillas_implementadas_far_crop_semill')->references('cod_semilla_bloque')->on('far_crop_semillas_bloques');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_crop_registro_semillas_implementadas');
        Schema::enableForeignKeyConstraints();
    }
};
